
-- ============ CATEGORIES ============
CREATE TABLE public.product_categories (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  company_id UUID NOT NULL REFERENCES public.companies(id) ON DELETE CASCADE,
  name TEXT NOT NULL,
  description TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  UNIQUE(company_id, name)
);

-- ============ PRODUCTS ============
CREATE TABLE public.products (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  company_id UUID NOT NULL REFERENCES public.companies(id) ON DELETE CASCADE,
  category_id UUID REFERENCES public.product_categories(id) ON DELETE SET NULL,
  sku TEXT NOT NULL,
  name TEXT NOT NULL,
  description TEXT,
  unit TEXT NOT NULL DEFAULT 'pièce',
  purchase_price NUMERIC(12,2) NOT NULL DEFAULT 0,
  sale_price NUMERIC(12,2) NOT NULL DEFAULT 0,
  alert_threshold INTEGER NOT NULL DEFAULT 0,
  barcode TEXT,
  image_url TEXT,
  is_active BOOLEAN NOT NULL DEFAULT true,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  UNIQUE(company_id, sku)
);

CREATE INDEX idx_products_company ON public.products(company_id);
CREATE INDEX idx_products_category ON public.products(category_id);

-- ============ WAREHOUSES ============
CREATE TABLE public.warehouses (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  company_id UUID NOT NULL REFERENCES public.companies(id) ON DELETE CASCADE,
  name TEXT NOT NULL,
  code TEXT NOT NULL,
  address TEXT,
  city TEXT,
  country TEXT,
  manager_id UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
  is_active BOOLEAN NOT NULL DEFAULT true,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  UNIQUE(company_id, code)
);

CREATE INDEX idx_warehouses_company ON public.warehouses(company_id);

-- ============ STOCK LEVELS ============
CREATE TABLE public.stock_levels (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  company_id UUID NOT NULL REFERENCES public.companies(id) ON DELETE CASCADE,
  product_id UUID NOT NULL REFERENCES public.products(id) ON DELETE CASCADE,
  warehouse_id UUID NOT NULL REFERENCES public.warehouses(id) ON DELETE CASCADE,
  quantity NUMERIC(12,3) NOT NULL DEFAULT 0,
  reserved_quantity NUMERIC(12,3) NOT NULL DEFAULT 0,
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  UNIQUE(product_id, warehouse_id)
);

CREATE INDEX idx_stock_levels_company ON public.stock_levels(company_id);
CREATE INDEX idx_stock_levels_product ON public.stock_levels(product_id);
CREATE INDEX idx_stock_levels_warehouse ON public.stock_levels(warehouse_id);

-- ============ STOCK MOVEMENTS ============
CREATE TYPE public.stock_movement_type AS ENUM ('entree', 'sortie', 'transfert', 'ajustement');

CREATE TABLE public.stock_movements (
  id UUID NOT NULL DEFAULT gen_random_uuid() PRIMARY KEY,
  company_id UUID NOT NULL REFERENCES public.companies(id) ON DELETE CASCADE,
  product_id UUID NOT NULL REFERENCES public.products(id) ON DELETE RESTRICT,
  warehouse_id UUID NOT NULL REFERENCES public.warehouses(id) ON DELETE RESTRICT,
  destination_warehouse_id UUID REFERENCES public.warehouses(id) ON DELETE RESTRICT,
  movement_type public.stock_movement_type NOT NULL,
  quantity NUMERIC(12,3) NOT NULL,
  reason TEXT,
  reference TEXT,
  performed_by UUID REFERENCES public.profiles(id) ON DELETE SET NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_stock_movements_company ON public.stock_movements(company_id);
CREATE INDEX idx_stock_movements_product ON public.stock_movements(product_id);
CREATE INDEX idx_stock_movements_warehouse ON public.stock_movements(warehouse_id);
CREATE INDEX idx_stock_movements_created ON public.stock_movements(created_at DESC);

-- ============ TRIGGERS updated_at ============
CREATE TRIGGER trg_product_categories_updated BEFORE UPDATE ON public.product_categories
  FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();
CREATE TRIGGER trg_products_updated BEFORE UPDATE ON public.products
  FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();
CREATE TRIGGER trg_warehouses_updated BEFORE UPDATE ON public.warehouses
  FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();
CREATE TRIGGER trg_stock_levels_updated BEFORE UPDATE ON public.stock_levels
  FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

-- ============ TRIGGER : appliquer les mouvements de stock ============
CREATE OR REPLACE FUNCTION public.apply_stock_movement()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
  -- Source warehouse
  IF NEW.movement_type IN ('sortie', 'transfert') THEN
    INSERT INTO public.stock_levels (company_id, product_id, warehouse_id, quantity)
    VALUES (NEW.company_id, NEW.product_id, NEW.warehouse_id, -NEW.quantity)
    ON CONFLICT (product_id, warehouse_id)
    DO UPDATE SET quantity = stock_levels.quantity - NEW.quantity, updated_at = now();
  ELSIF NEW.movement_type = 'entree' THEN
    INSERT INTO public.stock_levels (company_id, product_id, warehouse_id, quantity)
    VALUES (NEW.company_id, NEW.product_id, NEW.warehouse_id, NEW.quantity)
    ON CONFLICT (product_id, warehouse_id)
    DO UPDATE SET quantity = stock_levels.quantity + NEW.quantity, updated_at = now();
  ELSIF NEW.movement_type = 'ajustement' THEN
    INSERT INTO public.stock_levels (company_id, product_id, warehouse_id, quantity)
    VALUES (NEW.company_id, NEW.product_id, NEW.warehouse_id, NEW.quantity)
    ON CONFLICT (product_id, warehouse_id)
    DO UPDATE SET quantity = NEW.quantity, updated_at = now();
  END IF;

  -- Destination for transfer
  IF NEW.movement_type = 'transfert' AND NEW.destination_warehouse_id IS NOT NULL THEN
    INSERT INTO public.stock_levels (company_id, product_id, warehouse_id, quantity)
    VALUES (NEW.company_id, NEW.product_id, NEW.destination_warehouse_id, NEW.quantity)
    ON CONFLICT (product_id, warehouse_id)
    DO UPDATE SET quantity = stock_levels.quantity + NEW.quantity, updated_at = now();
  END IF;

  RETURN NEW;
END;
$$;

CREATE TRIGGER trg_apply_stock_movement
AFTER INSERT ON public.stock_movements
FOR EACH ROW EXECUTE FUNCTION public.apply_stock_movement();

-- ============ ENABLE RLS ============
ALTER TABLE public.product_categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.products ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.warehouses ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.stock_levels ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.stock_movements ENABLE ROW LEVEL SECURITY;

-- ============ HELPER : peut gérer la logistique ============
CREATE OR REPLACE FUNCTION public.can_manage_logistics(_user_id uuid)
RETURNS boolean
LANGUAGE sql STABLE SECURITY DEFINER SET search_path = public
AS $$
  SELECT
    public.has_role(_user_id, 'admin_saas') OR
    public.has_role(_user_id, 'admin_entreprise') OR
    public.has_role(_user_id, 'directeur_general') OR
    public.has_role(_user_id, 'responsable_logistique')
$$;

-- ============ POLICIES product_categories ============
CREATE POLICY "Voir catégories de son entreprise" ON public.product_categories
  FOR SELECT TO authenticated
  USING (company_id = get_user_company(auth.uid()) OR has_role(auth.uid(), 'admin_saas'));

CREATE POLICY "Gérer catégories de son entreprise" ON public.product_categories
  FOR ALL TO authenticated
  USING (can_manage_logistics(auth.uid()) AND (company_id = get_user_company(auth.uid()) OR has_role(auth.uid(), 'admin_saas')))
  WITH CHECK (can_manage_logistics(auth.uid()) AND (company_id = get_user_company(auth.uid()) OR has_role(auth.uid(), 'admin_saas')));

-- ============ POLICIES products ============
CREATE POLICY "Voir produits de son entreprise" ON public.products
  FOR SELECT TO authenticated
  USING (company_id = get_user_company(auth.uid()) OR has_role(auth.uid(), 'admin_saas'));

CREATE POLICY "Gérer produits de son entreprise" ON public.products
  FOR ALL TO authenticated
  USING (can_manage_logistics(auth.uid()) AND (company_id = get_user_company(auth.uid()) OR has_role(auth.uid(), 'admin_saas')))
  WITH CHECK (can_manage_logistics(auth.uid()) AND (company_id = get_user_company(auth.uid()) OR has_role(auth.uid(), 'admin_saas')));

-- ============ POLICIES warehouses ============
CREATE POLICY "Voir entrepôts de son entreprise" ON public.warehouses
  FOR SELECT TO authenticated
  USING (company_id = get_user_company(auth.uid()) OR has_role(auth.uid(), 'admin_saas'));

CREATE POLICY "Gérer entrepôts de son entreprise" ON public.warehouses
  FOR ALL TO authenticated
  USING (can_manage_logistics(auth.uid()) AND (company_id = get_user_company(auth.uid()) OR has_role(auth.uid(), 'admin_saas')))
  WITH CHECK (can_manage_logistics(auth.uid()) AND (company_id = get_user_company(auth.uid()) OR has_role(auth.uid(), 'admin_saas')));

-- ============ POLICIES stock_levels ============
CREATE POLICY "Voir stocks de son entreprise" ON public.stock_levels
  FOR SELECT TO authenticated
  USING (company_id = get_user_company(auth.uid()) OR has_role(auth.uid(), 'admin_saas'));

CREATE POLICY "Gérer stocks de son entreprise" ON public.stock_levels
  FOR ALL TO authenticated
  USING (can_manage_logistics(auth.uid()) AND (company_id = get_user_company(auth.uid()) OR has_role(auth.uid(), 'admin_saas')))
  WITH CHECK (can_manage_logistics(auth.uid()) AND (company_id = get_user_company(auth.uid()) OR has_role(auth.uid(), 'admin_saas')));

-- ============ POLICIES stock_movements ============
CREATE POLICY "Voir mouvements de son entreprise" ON public.stock_movements
  FOR SELECT TO authenticated
  USING (company_id = get_user_company(auth.uid()) OR has_role(auth.uid(), 'admin_saas'));

CREATE POLICY "Créer mouvements (logistique + magasinier)" ON public.stock_movements
  FOR INSERT TO authenticated
  WITH CHECK (
    (company_id = get_user_company(auth.uid()) OR has_role(auth.uid(), 'admin_saas'))
    AND (can_manage_logistics(auth.uid()) OR has_role(auth.uid(), 'magasinier'))
  );
