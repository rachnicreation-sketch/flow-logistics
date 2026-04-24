import { Outlet } from "react-router-dom";
import { Bell, LogOut, Search } from "lucide-react";
import { SidebarProvider, SidebarTrigger } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/AppSidebar";
import { ThemeToggle } from "@/components/ThemeToggle";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { useAuth } from "@/hooks/useAuth";

const roleLabels: Record<string, string> = {
  admin_saas: "Admin SaaS",
  admin_entreprise: "Admin entreprise",
  directeur_general: "Directeur général",
  responsable_logistique: "Responsable logistique",
  magasinier: "Magasinier",
  chauffeur: "Chauffeur",
};

function getInitials(name: string | null | undefined, email: string | null | undefined) {
  const source = name?.trim() || email || "U";
  const parts = source.split(/\s+/).filter(Boolean);
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return source.slice(0, 2).toUpperCase();
}

export default function AppLayout() {
  const { profile, user, roles, signOut } = useAuth();
  const displayName = profile?.full_name || user?.email || "Utilisateur";
  const initials = getInitials(profile?.full_name, user?.email);
  const primaryRole = roles[0] ? roleLabels[roles[0]] : "Aucun rôle";

  return (
    <SidebarProvider>
      <div className="flex min-h-screen w-full bg-background">
        <AppSidebar />
        <div className="flex flex-1 flex-col">
          <header className="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-border bg-card/80 px-4 backdrop-blur">
            <SidebarTrigger />
            <div className="relative hidden md:block w-72">
              <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input placeholder="Rechercher produit, commande…" className="pl-8 h-9" />
            </div>
            <div className="ml-auto flex items-center gap-1">
              <ThemeToggle />
              <Button variant="ghost" size="icon" aria-label="Notifications">
                <Bell className="h-4 w-4" />
              </Button>
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="ghost" className="ml-1 h-9 px-1.5 gap-2">
                    <Avatar className="h-7 w-7">
                      <AvatarFallback className="text-xs bg-primary text-primary-foreground">
                        {initials}
                      </AvatarFallback>
                    </Avatar>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-56">
                  <DropdownMenuLabel className="flex flex-col">
                    <span className="text-sm font-medium truncate">{displayName}</span>
                    <span className="text-xs text-muted-foreground">{primaryRole}</span>
                  </DropdownMenuLabel>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem onClick={() => signOut()} className="text-destructive focus:text-destructive">
                    <LogOut className="h-4 w-4" />
                    Se déconnecter
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </header>
          <main className="flex-1 p-4 md:p-6">
            <Outlet />
          </main>
        </div>
      </div>
    </SidebarProvider>
  );
}
