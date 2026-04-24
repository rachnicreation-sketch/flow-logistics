import { useEffect, useState } from "react";
import { Plus, AlertTriangle } from "lucide-react";
import { PageHeader } from "@/components/PageHeader";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from "@/components/ui/table";
import {
  Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle,
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import {
  Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from "@/components/ui/select";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/hooks/useAuth";
import { toast } from "sonner";

type StockRow = {
  id: string;
  product_id: string;
  warehouse_id: string;
  quantity: number;
  reserved_quantity: number;
  products: { sku: string; name: string; unit: string; alert_threshold: number } | null;
  warehouses: { name: string; code: string } | null;
};

type Movement = {
  id: string;
  movement_type: "entree" | "sortie" | "transfert" | "ajustement";
  quantity: number;
  reason: string | null;
  reference: string | null;
  created_at: string;
  products: { sku: string; name: string } | null;
  warehouses: { name: string } | null;
};

const movementForm = {
  product_id: "",
  warehouse_id: "",
  destination_warehouse_id: "",
  movement_type: "entree" as "entree" | "sortie" | "transfert" | "ajustement",
  quantity: "",
  reason: "",
  reference: "",
};

export default function Stocks() {
  const { profile, user } = useAuth();
  const [stocks, setStocks] = useState<StockRow[]>([]);
  const [movements, setMovements] = useState<Movement[]>([]);
  const [products, setProducts] = useState<{ id: string; sku: string; name: string }[]>([]);
  const [warehouses, setWarehouses] = useState<{ id: string; name: string; code: string }[]>([]);
  const [loading, setLoading] = useState(true);
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState(movementForm);
  const [saving, setSaving] = useState(false);

  const load = async () => {
    setLoading(true);
    const [{ data: s }, { data: m }, { data: p }, { data: w }] = await Promise.all([
      supabase
        .from("stock_levels")
        .select("id, product_id, warehouse_id, quantity, reserved_quantity, products(sku, name, unit, alert_threshold), warehouses(name, code)")
        .order("updated_at", { ascending: false }),
      supabase
        .from("stock_movements")
        .select("id, movement_type, quantity, reason, reference, created_at, products(sku, name), warehouses!stock_movements_warehouse_id_fkey(name)")
        .order("created_at", { ascending: false })
        .limit(50),
      supabase.from("products").select("id, sku, name").eq("is_active", true).order("name"),
      supabase.from("warehouses").select("id, name, code").eq("is_active", true).order("name"),
    ]);
    setStocks((s as unknown as StockRow[]) ?? []);
    setMovements((m as unknown as Movement[]) ?? []);
    setProducts((p as { id: string; sku: string; name: string }[]) ?? []);
    setWarehouses((w as { id: string; name: string; code: string }[]) ?? []);
    setLoading(false);
  };

  useEffect(() => { load(); }, []);

  const openNew = () => { setForm(movementForm); setOpen(true); };

  const save = async () => {
    if (!profile?.company_id || !user) return toast.error("Compte invalide.");
    if (!form.product_id || !form.warehouse_id || !form.quantity) {
      return toast.error("Produit, entrepôt et quantité obligatoires.");
    }
    if (form.movement_type === "transfert" && !form.destination_warehouse_id) {
      return toast.error("Entrepôt de destination obligatoire pour un transfert.");
    }
    setSaving(true);
    const { error } = await supabase.from("stock_movements").insert({
      company_id: profile.company_id,
      product_id: form.product_id,
      warehouse_id: form.warehouse_id,
      destination_warehouse_id: form.movement_type === "transfert" ? form.destination_warehouse_id : null,
      movement_type: form.movement_type,
      quantity: Number(form.quantity),
      reason: form.reason || null,
      reference: form.reference || null,
      performed_by: user.id,
    });
    setSaving(false);
    if (error) return toast.error(error.message);
    toast.success("Mouvement enregistré");
    setOpen(false); load();
  };

  const lowStock = stocks.filter((s) => s.products && s.quantity <= s.products.alert_threshold);

  const typeLabels: Record<string, string> = {
    entree: "Entrée", sortie: "Sortie", transfert: "Transfert", ajustement: "Ajustement",
  };
  const typeVariants: Record<string, "default" | "secondary" | "outline" | "destructive"> = {
    entree: "default", sortie: "destructive", transfert: "secondary", ajustement: "outline",
  };

  return (
    <div>
      <PageHeader
        title="Stocks"
        description="Niveaux, mouvements et alertes"
        action={{ label: "Nouveau mouvement", onClick: openNew }}
      />

      {lowStock.length > 0 && (
        <Card className="border-warning/40 bg-warning/5 mb-4">
          <CardHeader className="pb-2">
            <CardTitle className="text-sm flex items-center gap-2 text-warning">
              <AlertTriangle className="h-4 w-4" />
              {lowStock.length} alerte{lowStock.length > 1 ? "s" : ""} de seuil
            </CardTitle>
          </CardHeader>
          <CardContent className="text-sm text-muted-foreground">
            {lowStock.slice(0, 3).map((s) => (
              <div key={s.id}>
                {s.products?.name} ({s.warehouses?.code}) — {Number(s.quantity)} ≤ seuil {s.products?.alert_threshold}
              </div>
            ))}
            {lowStock.length > 3 && <div className="mt-1">…et {lowStock.length - 3} de plus.</div>}
          </CardContent>
        </Card>
      )}

      <Tabs defaultValue="levels">
        <TabsList>
          <TabsTrigger value="levels">Niveaux</TabsTrigger>
          <TabsTrigger value="movements">Mouvements récents</TabsTrigger>
        </TabsList>

        <TabsContent value="levels">
          <Card className="border-border/60">
            <CardContent className="p-4">
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>SKU</TableHead>
                      <TableHead>Produit</TableHead>
                      <TableHead>Entrepôt</TableHead>
                      <TableHead className="text-right">Quantité</TableHead>
                      <TableHead className="text-right">Réservée</TableHead>
                      <TableHead>Statut</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {loading ? (
                      <TableRow><TableCell colSpan={6} className="text-center text-muted-foreground py-8">Chargement…</TableCell></TableRow>
                    ) : stocks.length === 0 ? (
                      <TableRow><TableCell colSpan={6} className="text-center text-muted-foreground py-8">Aucun stock. Créez un mouvement d'entrée pour démarrer.</TableCell></TableRow>
                    ) : stocks.map((s) => {
                      const low = s.products && s.quantity <= s.products.alert_threshold;
                      return (
                        <TableRow key={s.id}>
                          <TableCell className="font-mono text-xs">{s.products?.sku}</TableCell>
                          <TableCell className="font-medium">{s.products?.name}</TableCell>
                          <TableCell>{s.warehouses?.name} <span className="text-muted-foreground">({s.warehouses?.code})</span></TableCell>
                          <TableCell className="text-right font-medium">{Number(s.quantity)} {s.products?.unit}</TableCell>
                          <TableCell className="text-right text-muted-foreground">{Number(s.reserved_quantity)}</TableCell>
                          <TableCell>
                            {low ? (
                              <Badge variant="outline" className="border-warning text-warning">Seuil bas</Badge>
                            ) : (
                              <Badge variant="default">OK</Badge>
                            )}
                          </TableCell>
                        </TableRow>
                      );
                    })}
                  </TableBody>
                </Table>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="movements">
          <Card className="border-border/60">
            <CardContent className="p-4">
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Date</TableHead>
                      <TableHead>Type</TableHead>
                      <TableHead>Produit</TableHead>
                      <TableHead>Entrepôt</TableHead>
                      <TableHead className="text-right">Quantité</TableHead>
                      <TableHead>Référence</TableHead>
                      <TableHead>Motif</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {loading ? (
                      <TableRow><TableCell colSpan={7} className="text-center text-muted-foreground py-8">Chargement…</TableCell></TableRow>
                    ) : movements.length === 0 ? (
                      <TableRow><TableCell colSpan={7} className="text-center text-muted-foreground py-8">Aucun mouvement.</TableCell></TableRow>
                    ) : movements.map((m) => (
                      <TableRow key={m.id}>
                        <TableCell className="text-xs text-muted-foreground">
                          {new Date(m.created_at).toLocaleString("fr-FR")}
                        </TableCell>
                        <TableCell>
                          <Badge variant={typeVariants[m.movement_type]}>{typeLabels[m.movement_type]}</Badge>
                        </TableCell>
                        <TableCell>{m.products?.name}</TableCell>
                        <TableCell>{m.warehouses?.name}</TableCell>
                        <TableCell className="text-right font-medium">{Number(m.quantity)}</TableCell>
                        <TableCell className="font-mono text-xs">{m.reference ?? "—"}</TableCell>
                        <TableCell className="text-muted-foreground text-sm">{m.reason ?? "—"}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      <Dialog open={open} onOpenChange={setOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Nouveau mouvement de stock</DialogTitle>
          </DialogHeader>
          <div className="grid gap-3 py-2">
            <div>
              <Label>Type</Label>
              <Select value={form.movement_type} onValueChange={(v) => setForm({ ...form, movement_type: v as typeof form.movement_type })}>
                <SelectTrigger><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="entree">Entrée (réception)</SelectItem>
                  <SelectItem value="sortie">Sortie (expédition)</SelectItem>
                  <SelectItem value="transfert">Transfert entre entrepôts</SelectItem>
                  <SelectItem value="ajustement">Ajustement (inventaire)</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div>
              <Label>Produit *</Label>
              <Select value={form.product_id} onValueChange={(v) => setForm({ ...form, product_id: v })}>
                <SelectTrigger><SelectValue placeholder="Sélectionner…" /></SelectTrigger>
                <SelectContent>
                  {products.map((p) => (
                    <SelectItem key={p.id} value={p.id}>{p.sku} — {p.name}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div>
                <Label>Entrepôt {form.movement_type === "transfert" ? "source" : ""} *</Label>
                <Select value={form.warehouse_id} onValueChange={(v) => setForm({ ...form, warehouse_id: v })}>
                  <SelectTrigger><SelectValue placeholder="Sélectionner…" /></SelectTrigger>
                  <SelectContent>
                    {warehouses.map((w) => (
                      <SelectItem key={w.id} value={w.id}>{w.code} — {w.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              {form.movement_type === "transfert" && (
                <div>
                  <Label>Destination *</Label>
                  <Select value={form.destination_warehouse_id} onValueChange={(v) => setForm({ ...form, destination_warehouse_id: v })}>
                    <SelectTrigger><SelectValue placeholder="Sélectionner…" /></SelectTrigger>
                    <SelectContent>
                      {warehouses.filter((w) => w.id !== form.warehouse_id).map((w) => (
                        <SelectItem key={w.id} value={w.id}>{w.code} — {w.name}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              )}
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div>
                <Label htmlFor="qty">Quantité *</Label>
                <Input id="qty" type="number" step="0.001" value={form.quantity} onChange={(e) => setForm({ ...form, quantity: e.target.value })} />
              </div>
              <div>
                <Label htmlFor="ref">Référence</Label>
                <Input id="ref" value={form.reference} onChange={(e) => setForm({ ...form, reference: e.target.value })} placeholder="BL-2025-001" />
              </div>
            </div>
            <div>
              <Label htmlFor="reason">Motif</Label>
              <Textarea id="reason" rows={2} value={form.reason} onChange={(e) => setForm({ ...form, reason: e.target.value })} />
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setOpen(false)}>Annuler</Button>
            <Button onClick={save} disabled={saving}>{saving ? "Enregistrement…" : "Valider"}</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
