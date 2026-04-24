import { Card, CardContent } from "@/components/ui/card";
import { LucideIcon, TrendingUp, TrendingDown } from "lucide-react";
import { cn } from "@/lib/utils";

interface StatCardProps {
  label: string;
  value: string;
  delta?: number;
  icon: LucideIcon;
  tone?: "default" | "success" | "warning" | "destructive";
}

const toneMap: Record<NonNullable<StatCardProps["tone"]>, string> = {
  default: "bg-primary/10 text-primary",
  success: "bg-success/10 text-success",
  warning: "bg-warning/10 text-warning",
  destructive: "bg-destructive/10 text-destructive",
};

export function StatCard({ label, value, delta, icon: Icon, tone = "default" }: StatCardProps) {
  const positive = (delta ?? 0) >= 0;
  return (
    <Card className="overflow-hidden border-border/60 hover:shadow-elegant transition-shadow">
      <CardContent className="p-5">
        <div className="flex items-start justify-between">
          <div>
            <p className="text-xs font-medium uppercase tracking-wider text-muted-foreground">{label}</p>
            <p className="mt-2 text-2xl font-bold tracking-tight">{value}</p>
            {delta !== undefined && (
              <div
                className={cn(
                  "mt-2 inline-flex items-center gap-1 text-xs font-medium",
                  positive ? "text-success" : "text-destructive"
                )}
              >
                {positive ? <TrendingUp className="h-3 w-3" /> : <TrendingDown className="h-3 w-3" />}
                {positive ? "+" : ""}
                {delta}% vs mois dernier
              </div>
            )}
          </div>
          <div className={cn("rounded-lg p-2.5", toneMap[tone])}>
            <Icon className="h-5 w-5" />
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
