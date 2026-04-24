import {
  LayoutDashboard,
  Package,
  Warehouse,
  Boxes,
  Truck,
  Users,
  ShoppingCart,
  ClipboardList,
  Building2,
  UserCog,
  Settings,
  Route as RouteIcon,
} from "lucide-react";
import { NavLink, useLocation } from "react-router-dom";
import {
  Sidebar,
  SidebarContent,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  useSidebar,
} from "@/components/ui/sidebar";

const groups = [
  {
    label: "Pilotage",
    items: [{ title: "Tableau de bord", url: "/", icon: LayoutDashboard }],
  },
  {
    label: "Supply Chain",
    items: [
      { title: "Fournisseurs", url: "/fournisseurs", icon: Building2 },
      { title: "Achats", url: "/achats", icon: ClipboardList },
      { title: "Produits", url: "/produits", icon: Package },
    ],
  },
  {
    label: "Entrepôts (WMS)",
    items: [
      { title: "Entrepôts", url: "/entrepots", icon: Warehouse },
      { title: "Stocks", url: "/stocks", icon: Boxes },
    ],
  },
  {
    label: "Ventes & Transport",
    items: [
      { title: "Clients", url: "/clients", icon: Users },
      { title: "Commandes", url: "/commandes", icon: ShoppingCart },
      { title: "Livraisons", url: "/livraisons", icon: RouteIcon },
      { title: "Véhicules", url: "/vehicules", icon: Truck },
    ],
  },
  {
    label: "Administration",
    items: [
      { title: "Utilisateurs", url: "/utilisateurs", icon: UserCog },
      { title: "Paramètres", url: "/parametres", icon: Settings },
    ],
  },
];

export function AppSidebar() {
  const { state } = useSidebar();
  const collapsed = state === "collapsed";
  const { pathname } = useLocation();

  return (
    <Sidebar collapsible="icon">
      <SidebarHeader className="border-b border-sidebar-border">
        <div className="flex items-center gap-2 px-2 py-3">
          <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-md gradient-primary text-primary-foreground font-bold">
            L
          </div>
          {!collapsed && (
            <div className="flex flex-col leading-tight">
              <span className="text-sm font-semibold">LogiFlow</span>
              <span className="text-xs text-muted-foreground">SCM · WMS · TMS</span>
            </div>
          )}
        </div>
      </SidebarHeader>
      <SidebarContent>
        {groups.map((group) => (
          <SidebarGroup key={group.label}>
            {!collapsed && <SidebarGroupLabel>{group.label}</SidebarGroupLabel>}
            <SidebarGroupContent>
              <SidebarMenu>
                {group.items.map((item) => {
                  const active = pathname === item.url;
                  return (
                    <SidebarMenuItem key={item.url}>
                      <SidebarMenuButton asChild isActive={active}>
                        <NavLink to={item.url} end>
                          <item.icon className="h-4 w-4" />
                          {!collapsed && <span>{item.title}</span>}
                        </NavLink>
                      </SidebarMenuButton>
                    </SidebarMenuItem>
                  );
                })}
              </SidebarMenu>
            </SidebarGroupContent>
          </SidebarGroup>
        ))}
      </SidebarContent>
    </Sidebar>
  );
}
