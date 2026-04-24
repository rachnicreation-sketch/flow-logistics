-- Enum des rôles
CREATE TYPE public.app_role AS ENUM (
  'admin_saas',
  'admin_entreprise',
  'directeur_general',
  'responsable_logistique',
  'magasinier',
  'chauffeur'
);

-- Table companies
CREATE TABLE public.companies (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  name text NOT NULL,
  slug text UNIQUE NOT NULL,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

-- Table profiles
CREATE TABLE public.profiles (
  id uuid PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
  company_id uuid REFERENCES public.companies(id) ON DELETE SET NULL,
  full_name text,
  avatar_url text,
  email text,
  created_at timestamptz NOT NULL DEFAULT now(),
  updated_at timestamptz NOT NULL DEFAULT now()
);

-- Table user_roles
CREATE TABLE public.user_roles (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id uuid NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
  role app_role NOT NULL,
  company_id uuid REFERENCES public.companies(id) ON DELETE CASCADE,
  created_at timestamptz NOT NULL DEFAULT now(),
  UNIQUE (user_id, role, company_id)
);

-- Activer RLS
ALTER TABLE public.companies ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.user_roles ENABLE ROW LEVEL SECURITY;

-- Security definer: vérifier un rôle
CREATE OR REPLACE FUNCTION public.has_role(_user_id uuid, _role app_role)
RETURNS boolean
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
  SELECT EXISTS (
    SELECT 1 FROM public.user_roles
    WHERE user_id = _user_id AND role = _role
  )
$$;

-- Security definer: récupérer la company_id d'un user
CREATE OR REPLACE FUNCTION public.get_user_company(_user_id uuid)
RETURNS uuid
LANGUAGE sql
STABLE
SECURITY DEFINER
SET search_path = public
AS $$
  SELECT company_id FROM public.profiles WHERE id = _user_id
$$;

-- Trigger: timestamps
CREATE OR REPLACE FUNCTION public.update_updated_at_column()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$;

CREATE TRIGGER companies_updated_at
  BEFORE UPDATE ON public.companies
  FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

CREATE TRIGGER profiles_updated_at
  BEFORE UPDATE ON public.profiles
  FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

-- Trigger: création auto du profil et rôle par défaut
CREATE OR REPLACE FUNCTION public.handle_new_user()
RETURNS trigger
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
BEGIN
  INSERT INTO public.profiles (id, email, full_name)
  VALUES (
    NEW.id,
    NEW.email,
    COALESCE(NEW.raw_user_meta_data->>'full_name', NEW.email)
  );

  INSERT INTO public.user_roles (user_id, role)
  VALUES (NEW.id, 'magasinier');

  RETURN NEW;
END;
$$;

CREATE TRIGGER on_auth_user_created
  AFTER INSERT ON auth.users
  FOR EACH ROW EXECUTE FUNCTION public.handle_new_user();

-- Policies companies
CREATE POLICY "Admins SaaS voient toutes les entreprises"
  ON public.companies FOR SELECT
  TO authenticated
  USING (public.has_role(auth.uid(), 'admin_saas'));

CREATE POLICY "Utilisateurs voient leur entreprise"
  ON public.companies FOR SELECT
  TO authenticated
  USING (id = public.get_user_company(auth.uid()));

CREATE POLICY "Admins SaaS gèrent les entreprises"
  ON public.companies FOR ALL
  TO authenticated
  USING (public.has_role(auth.uid(), 'admin_saas'))
  WITH CHECK (public.has_role(auth.uid(), 'admin_saas'));

-- Policies profiles
CREATE POLICY "Utilisateurs voient leur propre profil"
  ON public.profiles FOR SELECT
  TO authenticated
  USING (id = auth.uid());

CREATE POLICY "Utilisateurs voient les profils de leur entreprise"
  ON public.profiles FOR SELECT
  TO authenticated
  USING (company_id = public.get_user_company(auth.uid()) AND company_id IS NOT NULL);

CREATE POLICY "Admins SaaS voient tous les profils"
  ON public.profiles FOR SELECT
  TO authenticated
  USING (public.has_role(auth.uid(), 'admin_saas'));

CREATE POLICY "Utilisateurs modifient leur propre profil"
  ON public.profiles FOR UPDATE
  TO authenticated
  USING (id = auth.uid())
  WITH CHECK (id = auth.uid());

CREATE POLICY "Admins entreprise modifient les profils de leur entreprise"
  ON public.profiles FOR UPDATE
  TO authenticated
  USING (public.has_role(auth.uid(), 'admin_entreprise') AND company_id = public.get_user_company(auth.uid()));

-- Policies user_roles
CREATE POLICY "Utilisateurs voient leurs propres rôles"
  ON public.user_roles FOR SELECT
  TO authenticated
  USING (user_id = auth.uid());

CREATE POLICY "Admins entreprise voient les rôles de leur entreprise"
  ON public.user_roles FOR SELECT
  TO authenticated
  USING (
    public.has_role(auth.uid(), 'admin_entreprise')
    AND company_id = public.get_user_company(auth.uid())
  );

CREATE POLICY "Admins SaaS voient tous les rôles"
  ON public.user_roles FOR SELECT
  TO authenticated
  USING (public.has_role(auth.uid(), 'admin_saas'));

CREATE POLICY "Admins entreprise gèrent les rôles de leur entreprise"
  ON public.user_roles FOR ALL
  TO authenticated
  USING (
    public.has_role(auth.uid(), 'admin_entreprise')
    AND company_id = public.get_user_company(auth.uid())
  )
  WITH CHECK (
    public.has_role(auth.uid(), 'admin_entreprise')
    AND company_id = public.get_user_company(auth.uid())
  );

CREATE POLICY "Admins SaaS gèrent tous les rôles"
  ON public.user_roles FOR ALL
  TO authenticated
  USING (public.has_role(auth.uid(), 'admin_saas'))
  WITH CHECK (public.has_role(auth.uid(), 'admin_saas'));