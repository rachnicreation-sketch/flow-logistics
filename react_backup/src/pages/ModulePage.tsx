import { Card, CardContent } from "@/components/ui/card";
import { PageHeader } from "@/components/PageHeader";
import { Construction } from "lucide-react";

interface ModulePageProps {
  title: string;
  description: string;
  actionLabel: string;
}

export default function ModulePage({ title, description, actionLabel }: ModulePageProps) {
  return (
    <div>
      <PageHeader title={title} description={description} action={{ label: actionLabel }} />
      <Card className="border-border/60 border-dashed">
        <CardContent className="flex flex-col items-center justify-center py-16 text-center">
          <div className="rounded-full bg-primary/10 p-4 mb-4">
            <Construction className="h-8 w-8 text-primary" />
          </div>
          <h3 className="text-lg font-semibold">Module prêt à être branché</h3>
          <p className="text-sm text-muted-foreground mt-2 max-w-md">
            L'interface est en place. Activez Lovable Cloud à l'étape suivante pour brancher la
            base de données, l'authentification et les permissions par rôle.
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
