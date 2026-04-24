import { useEffect, useState } from "react";
import { Plus, Pencil, Trash2, Search } from "lucide-react";
import { PageHeader } from "@/components/PageHeader";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/hooks/useAuth";
import { toast } from "sonner";

type Product = {
  id: string;
  sku: string;
  name: string;
  description: string | null;
  unit: string;
  purchase_price: number;
  sale_price: number;
  alert_threshold: number;
  is_active: boolean;
  category_id: string | null;
};

type Category = { id: string; name: string };

const empty = {
  sku: "",
  name: "",
  description: "",
  unit: "pièce",
  purchase_price: "0",
  sale_price: "0",
  alert_threshold: "0",
  category_id: "",
};

export default function Produits() {
  const { profile } = useAuth();
  const [items, setItems] = useState<Product[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Product | null>(null);
  const [form, setForm] = useState(empty);
  const [saving, setSaving] = useState(false);

  const load = async () => {
    setLoading(true);
    const [{ data: prods }, { data: cats }] = await Promise.all([
      supabase.from("products").select("*").order("created_at", { ascending: false }),
      supabase.from("product_categories").select("id, name").order("name"),
    ]);
    setItems((prods as Product[]) ?? []);
    setCategories((cats as Category[]) ?? []);
    setLoading(false);
  };

  useEffect(() => {
    load();
  }, []);

  const openNew = () => {
    setEditing(null);
    setForm(empty);
    setOpen(true);
  };

  const openEdit = (p: Product) => {
    setEditing(p);
    setForm({
      sku: p.sku,
      name: p.name,
      description: p.description ?? "",
      unit: p.unit,
      purchase_price: String(p.purchase_price),
      sale_price: String(p.sale_price),
      alert_threshold: String(p.alert_threshold),
      category_id: p.category_id ?? "",
    });
    setOpen(true);
  };

  const save = async () => {
    if (!profile?.company_id) {
      toast.error("Aucune entreprise rattachée à votre compte.");
      return;
    }
    if (!form.sku.trim() || !form.name.trim()) {
      toast.error("SKU et nom sont obligatoires.");
      return;
    }
    setSaving(true);
    const payload = {
      company_id: profile.company_id,
      sku: form.sku.trim(),
      name: form.name.trim(),
      description: form.description || null,
      unit: form.unit,
      purchase_price: Number(form.purchase_price) || 0,
      sale_price: Number(form.sale_price) || 0,
      alert_threshold: Number(form.alert_threshold) || 0,
      category_id: form.category_id || null,
    };
    const { error } = editing
      ? await supabase.from("products").update(payload).eq("id", editing.id)
      : await supabase.from("products").insert(payload);
    setSaving(false);
    if (error) {
      toast.error(error.message);
      return;
    }
    toast.success(editing ? "Produit mis à jour" : "Produit créé");
    setOpen(false);
    load();
  };

  const remove = async (p: Product) => {
    if (!confirm(`Supprimer le produit "${p.name}" ?`)) return;
    const { error } = await supabase.from("products").delete().eq("id", p.id);
    if (error) return toast.error(error.message);
    toast.success("Produit supprimé");
    load();
  };

  const filtered = items.filter(
    (p) =>
      p.name.toLowerCase().includes(search.toLowerCase()) ||
      p.sku.toLowerCase().includes(search.toLowerCase()),
  );

  return (
    <div>
      <PageHeader
        title="Produits"
        description="Catalogue, SKU et tarification"
        action={{ label: "Nouveau produit", onClick: openNew }}
      />

      <Card className="border-border/60">
        <CardContent className="p-4">
          <div className="flex items-center gap-2 mb-4">
            <div className="relative flex-1 max-w-sm">
              <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Rechercher par nom ou SKU…"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="pl-8"
              />
            </div>
          </div>

          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>SKU</TableHead>
                  <TableHead>Nom</TableHead>
                  <TableHead>Unité</TableHead>
                  <TableHead className="text-right">Achat</TableHead>
                  <TableHead className="text-right">Vente</TableHead>
                  <TableHead className="text-right">Seuil</TableHead>
                  <TableHead>Statut</TableHead>
                  <TableHead className="w-24"></TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {loading ? (
                  <TableRow>
                    <TableCell colSpan={8} className="text-center text-muted-foreground py-8">
                      Chargement…
                    </TableCell>
                  </TableRow>
                ) : filtered.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={8} className="text-center text-muted-foreground py-8">
                      Aucun produit. Cliquez sur « Nouveau produit » pour commencer.
                    </TableCell>
                  </TableRow>
                ) : (
                  filtered.map((p) => (
                    <TableRow key={p.id}>
                      <TableCell className="font-mono text-xs">{p.sku}</TableCell>
                      <TableCell className="font-medium">{p.name}</TableCell>
                      <TableCell>{p.unit}</TableCell>
                      <TableCell className="text-right">{Number(p.purchase_price).toFixed(2)}</TableCell>
                      <TableCell className="text-right">{Number(p.sale_price).toFixed(2)}</TableCell>
                      <TableCell className="text-right">{p.alert_threshold}</TableCell>
                      <TableCell>
                        <Badge variant={p.is_active ? "default" : "outline"}>
                          {p.is_active ? "Actif" : "Inactif"}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <div className="flex justify-end gap-1">
                          <Button variant="ghost" size="icon" onClick={() => openEdit(p)}>
                            <Pencil className="h-4 w-4" />
                          </Button>
                          <Button variant="ghost" size="icon" onClick={() => remove(p)}>
                            <Trash2 className="h-4 w-4" />
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <Dialog open={open} onOpenChange={setOpen}>
        <DialogContent className="max-w-lg">
          <DialogHeader>
            <DialogTitle>{editing ? "Modifier le produit" : "Nouveau produit"}</DialogTitle>
            <DialogDescription>
              Renseignez les informations du produit.
            </DialogDescription>
          </DialogHeader>
          <div className="grid gap-3 py-2">
            <div className="grid grid-cols-2 gap-3">
              <div>
                <Label htmlFor="sku">SKU *</Label>
                <Input id="sku" value={form.sku} onChange={(e) => setForm({ ...form, sku: e.target.value })} />
              </div>
              <div>
                <Label htmlFor="unit">Unité</Label>
                <Input id="unit" value={form.unit} onChange={(e) => setForm({ ...form, unit: e.target.value })} />
              </div>
            </div>
            <div>
              <Label htmlFor="name">Nom *</Label>
              <Input id="name" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
            </div>
            <div>
              <Label htmlFor="desc">Description</Label>
              <Textarea id="desc" rows={2} value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} />
            </div>
            <div>
              <Label htmlFor="cat">Catégorie</Label>
              <Select value={form.category_id || "none"} onValueChange={(v) => setForm({ ...form, category_id: v === "none" ? "" : v })}>
                <SelectTrigger><SelectValue placeholder="Aucune" /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="none">Aucune</SelectItem>
                  {categories.map((c) => (
                    <SelectItem key={c.id} value={c.id}>{c.name}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="grid grid-cols-3 gap-3">
              <div>
                <Label htmlFor="pp">Prix achat</Label>
                <Input id="pp" type="number" step="0.01" value={form.purchase_price} onChange={(e) => setForm({ ...form, purchase_price: e.target.value })} />
              </div>
              <div>
                <Label htmlFor="sp">Prix vente</Label>
                <Input id="sp" type="number" step="0.01" value={form.sale_price} onChange={(e) => setForm({ ...form, sale_price: e.target.value })} />
              </div>
              <div>
                <Label htmlFor="at">Seuil alerte</Label>
                <Input id="at" type="number" value={form.alert_threshold} onChange={(e) => setForm({ ...form, alert_threshold: e.target.value })} />
              </div>
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setOpen(false)}>Annuler</Button>
            <Button onClick={save} disabled={saving}>{saving ? "Enregistrement…" : "Enregistrer"}</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
