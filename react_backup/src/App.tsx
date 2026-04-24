import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { Toaster } from "@/components/ui/toaster";
import { TooltipProvider } from "@/components/ui/tooltip";
import { ThemeProvider } from "@/components/theme-provider";
import { AuthProvider } from "@/hooks/useAuth";
import { ProtectedRoute } from "@/components/ProtectedRoute";
import AppLayout from "./layouts/AppLayout";
import Dashboard from "./pages/Dashboard";
import ModulePage from "./pages/ModulePage";
import Produits from "./pages/Produits";
import Entrepots from "./pages/Entrepots";
import Stocks from "./pages/Stocks";
import Auth from "./pages/Auth";
import NotFound from "./pages/NotFound";

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <ThemeProvider>
      <TooltipProvider>
        <Toaster />
        <Sonner />
        <BrowserRouter>
          <AuthProvider>
            <Routes>
              <Route path="/auth" element={<Auth />} />
              <Route
                element={
                  <ProtectedRoute>
                    <AppLayout />
                  </ProtectedRoute>
                }
              >
                <Route path="/" element={<Dashboard />} />
                <Route path="/fournisseurs" element={<ModulePage title="Fournisseurs" description="Gérez vos partenaires d'approvisionnement" actionLabel="Nouveau fournisseur" />} />
                <Route path="/achats" element={<ModulePage title="Achats" description="Bons de commande et réceptions" actionLabel="Nouveau bon de commande" />} />
                <Route path="/produits" element={<Produits />} />
                <Route path="/entrepots" element={<Entrepots />} />
                <Route path="/stocks" element={<Stocks />} />
                <Route path="/clients" element={<ModulePage title="Clients" description="Base clients et historique" actionLabel="Nouveau client" />} />
                <Route path="/commandes" element={<ModulePage title="Commandes" description="Commandes clients et facturation" actionLabel="Nouvelle commande" />} />
                <Route path="/livraisons" element={<ModulePage title="Livraisons" description="Planification et suivi temps réel" actionLabel="Planifier une livraison" />} />
                <Route path="/vehicules" element={<ModulePage title="Véhicules" description="Flotte et chauffeurs" actionLabel="Nouveau véhicule" />} />
                <Route path="/utilisateurs" element={<ModulePage title="Utilisateurs" description="Comptes et rôles" actionLabel="Inviter un utilisateur" />} />
                <Route path="/parametres" element={<ModulePage title="Paramètres" description="Configuration de l'entreprise" actionLabel="Sauvegarder" />} />
              </Route>
              <Route path="*" element={<NotFound />} />
            </Routes>
          </AuthProvider>
        </BrowserRouter>
      </TooltipProvider>
    </ThemeProvider>
  </QueryClientProvider>
);

export default App;
