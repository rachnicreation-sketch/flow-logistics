import { Boxes, AlertTriangle, ShoppingCart, Truck } from "lucide-react";
import {
  Area,
  AreaChart,
  Bar,
  BarChart,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";
import { PageHeader } from "@/components/PageHeader";
import { StatCard } from "@/components/StatCard";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

const flowData = [
  { mois: "Jan", entrees: 420, sorties: 380 },
  { mois: "Fév", entrees: 510, sorties: 460 },
  { mois: "Mar", entrees: 480, sorties: 510 },
  { mois: "Avr", entrees: 620, sorties: 570 },
  { mois: "Mai", entrees: 690, sorties: 640 },
  { mois: "Jun", entrees: 740, sorties: 720 },
];

const deliveriesByDay = [
  { jour: "Lun", livrées: 32 },
  { jour: "Mar", livrées: 41 },
  { jour: "Mer", livrées: 28 },
  { jour: "Jeu", livrées: 45 },
  { jour: "Ven", livrées: 52 },
  { jour: "Sam", livrées: 18 },
  { jour: "Dim", livrées: 9 },
];

const recentOrders = [
  { id: "CMD-1042", client: "Acme Corp", montant: "1 240 €", statut: "Préparation" },
  { id: "CMD-1041", client: "Globex SARL", montant: "860 €", statut: "Expédié" },
  { id: "CMD-1040", client: "Initech", montant: "3 120 €", statut: "Livré" },
  { id: "CMD-1039", client: "Umbrella", montant: "540 €", statut: "En attente" },
];

const statutTone: Record<string, "default" | "secondary" | "outline"> = {
  Livré: "default",
  Expédié: "secondary",
  Préparation: "outline",
  "En attente": "outline",
};

export default function Dashboard() {
  return (
    <div>
      <PageHeader
        title="Tableau de bord"
        description="Vue d'ensemble de votre chaîne d'approvisionnement"
      />

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard label="Stock total" value="14 832" delta={8} icon={Boxes} />
        <StatCard label="Ruptures" value="23" delta={-12} icon={AlertTriangle} tone="warning" />
        <StatCard label="Commandes en cours" value="186" delta={5} icon={ShoppingCart} tone="success" />
        <StatCard label="Livraisons du jour" value="42" delta={14} icon={Truck} tone="default" />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-6">
        <Card className="lg:col-span-2 border-border/60">
          <CardHeader>
            <CardTitle className="text-base">Flux de stock — 6 derniers mois</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-72 w-full">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={flowData}>
                  <defs>
                    <linearGradient id="entrees" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="hsl(var(--primary))" stopOpacity={0.4} />
                      <stop offset="95%" stopColor="hsl(var(--primary))" stopOpacity={0} />
                    </linearGradient>
                    <linearGradient id="sorties" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="hsl(var(--accent))" stopOpacity={0.4} />
                      <stop offset="95%" stopColor="hsl(var(--accent))" stopOpacity={0} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                  <XAxis dataKey="mois" stroke="hsl(var(--muted-foreground))" fontSize={12} />
                  <YAxis stroke="hsl(var(--muted-foreground))" fontSize={12} />
                  <Tooltip
                    contentStyle={{
                      background: "hsl(var(--card))",
                      border: "1px solid hsl(var(--border))",
                      borderRadius: 8,
                      fontSize: 12,
                    }}
                  />
                  <Area type="monotone" dataKey="entrees" stroke="hsl(var(--primary))" fill="url(#entrees)" strokeWidth={2} />
                  <Area type="monotone" dataKey="sorties" stroke="hsl(var(--accent))" fill="url(#sorties)" strokeWidth={2} />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>

        <Card className="border-border/60">
          <CardHeader>
            <CardTitle className="text-base">Livraisons / jour</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-72 w-full">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={deliveriesByDay}>
                  <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                  <XAxis dataKey="jour" stroke="hsl(var(--muted-foreground))" fontSize={12} />
                  <YAxis stroke="hsl(var(--muted-foreground))" fontSize={12} />
                  <Tooltip
                    contentStyle={{
                      background: "hsl(var(--card))",
                      border: "1px solid hsl(var(--border))",
                      borderRadius: 8,
                      fontSize: 12,
                    }}
                  />
                  <Bar dataKey="livrées" fill="hsl(var(--primary))" radius={[6, 6, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>
      </div>

      <Card className="mt-6 border-border/60">
        <CardHeader>
          <CardTitle className="text-base">Commandes récentes</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="text-left text-muted-foreground border-b border-border">
                  <th className="font-medium py-2 px-2">Référence</th>
                  <th className="font-medium py-2 px-2">Client</th>
                  <th className="font-medium py-2 px-2">Montant</th>
                  <th className="font-medium py-2 px-2">Statut</th>
                </tr>
              </thead>
              <tbody>
                {recentOrders.map((o) => (
                  <tr key={o.id} className="border-b border-border/60 last:border-0 hover:bg-muted/30">
                    <td className="py-3 px-2 font-mono text-xs">{o.id}</td>
                    <td className="py-3 px-2">{o.client}</td>
                    <td className="py-3 px-2 font-medium">{o.montant}</td>
                    <td className="py-3 px-2">
                      <Badge variant={statutTone[o.statut] ?? "outline"}>{o.statut}</Badge>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
