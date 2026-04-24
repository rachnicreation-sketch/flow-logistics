import { useEffect, useState } from "react";
import { Plus, Pencil, Trash2 } from "lucide-react";
import { PageHeader } from "@/components/PageHeader";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import {
  Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from "@/components/ui/table";
import {
  Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle,
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { supabase } from "@/integrations/supabase/client";
import { useAuth } from "@/hooks/useAuth";
import { toast } from "sonner";

type Warehouse = {
  id: string;
  code: string;
  name: string;
  address: string | null;
  city: string | null;
  country: string | null;
  is_active: boolean;
};

const empty = { code: "", name: "", address: "", city: "", country: "" };

export default function Entrepots() {
  const { profile } = useAuth();
  const [items, setItems] = useState<Warehouse[]>([]);
  const [loading, setLoading] = useState(true);
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Warehouse | null>(null);
  const [form, setForm] = useState(empty);
  const [saving, setSaving] = useState(false);

  const load = async () => {
    setLoading(true);
    const { data } = await supabase.from("warehouses").select("*").order("name");
    setItems((data as Warehouse[]) ?? []);
    setLoading(false);
  };

  useEffect(() => { load(); }, []);

  const openNew = () => { setEditing(null); setForm(empty); setOpen(true); };
  const openEdit = (w: Warehouse) => {
    setEditing(w);
    setForm({
      code: w.code, name: w.name,
      address: w.address ?? "", city: w.city ?? "", country: w.country ?? "",
    });
    setOpen(true);
  };

  const save = async () => {
    if (!profile?.company_id) return toast.error("Aucune entreprise rattachée.");
    if (!form.code.trim() || !form.name.trim()) return toast.error("Code et nom obligatoires.");
    setSaving(true);
    const payload = {
      company_id: profile.company_id,
      code: form.code.trim(),
      name: form.name.trim(),
      address: form.address || null,
      city: form.city || null,
      country: form.country || null,
    };
    const { error } = editing
      ? await supabase.from("warehouses").update(payload).eq("id", editing.id)
      : await supabase.from("warehouses").insert(payload);
    setSaving(false);
    if (error) return toast.error(error.message);
    toast.success(editing ? "Entrepôt mis à jour" : "Entrepôt créé");
    setOpen(false); load();
  };

  const remove = async (w: Warehouse) => {
    if (!confirm(`Supprimer l'entrepôt "${w.name}" ?`)) return;
    const { error } = await supabase.from("warehouses").delete().eq("id", w.id);
    if (error) return toast.error(error.message);
    toast.success("Entrepôt supprimé"); load();
  };

  return (
    <div>
      <PageHeader
        title="Entrepôts"
        description="Sites, zones et emplacements"
        action={{ label: "Nouvel entrepôt", onClick: openNew }}
      />
      <Card className="border-border/60">
        <CardContent className="p-4">
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Code</TableHead>
                  <TableHead>Nom</TableHead>
                  <TableHead>Ville</TableHead>
                  <TableHead>Pays</TableHead>
                  <TableHead>Statut</TableHead>
                  <TableHead className="w-24"></TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {loading ? (
                  <TableRow><TableCell colSpan={6} className="text-center text-muted-foreground py-8">Chargement…</TableCell></TableRow>
                ) : items.length === 0 ? (
                  <TableRow><TableCell colSpan={6} className="text-center text-muted-foreground py-8">Aucun entrepôt.</TableCell></TableRow>
                ) : items.map((w) => (
                  <TableRow key={w.id}>
                    <TableCell className="font-mono text-xs">{w.code}</TableCell>
                    <TableCell className="font-medium">{w.name}</TableCell>
                    <TableCell>{w.city ?? "—"}</TableCell>
                    <TableCell>{w.country ?? "—"}</TableCell>
                    <TableCell>
                      <Badge variant={w.is_active ? "default" : "outline"}>
                        {w.is_active ? "Actif" : "Inactif"}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div className="flex justify-end gap-1">
                        <Button variant="ghost" size="icon" onClick={() => openEdit(w)}><Pencil className="h-4 w-4" /></Button>
                        <Button variant="ghost" size="icon" onClick={() => remove(w)}><Trash2 className="h-4 w-4" /></Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <Dialog open={open} onOpenChange={setOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{editing ? "Modifier l'entrepôt" : "Nouvel entrepôt"}</DialogTitle>
          </DialogHeader>
          <div className="grid gap-3 py-2">
            <div className="grid grid-cols-2 gap-3">
              <div>
                <Label htmlFor="code">Code *</Label>
                <Input id="code" value={form.code} onChange={(e) => setForm({ ...form, code: e.target.value })} />
              </div>
              <div>
                <Label htmlFor="name">Nom *</Label>
                <Input id="name" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
              </div>
            </div>
            <div>
              <Label htmlFor="address">Adresse</Label>
              <Input id="address" value={form.address} onChange={(e) => setForm({ ...form, address: e.target.value })} />
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div>
                <Label htmlFor="city">Ville</Label>
                <Input id="city" value={form.city} onChange={(e) => setForm({ ...form, city: e.target.value })} />
              </div>
              <div>
                <Label htmlFor="country">Pays</Label>
                <Input id="country" value={form.country} onChange={(e) => setForm({ ...form, country: e.target.value })} />
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
