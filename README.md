# LogiFlow SCM SaaS (PHP MVC + MySQL)

Application web professionnelle de gestion logistique complète avec chaîne SCM:
fournisseurs -> achats -> entrepôts -> stocks -> commandes -> livraisons -> clients.

## Points clés
- Architecture MVC propre (`app/Controllers`, `app/Models`, `app/Views`, `app/Services`)
- Multi-entreprise (isolation `company_id`)
- Authentification sécurisée (`password_hash`, sessions, CSRF, RBAC permissions)
- Modules SCM/WMS/TMS complets
- Tableau de bord KPI + graphiques JS
- API simple chauffeur (token Bearer)
- Logs d'audit utilisateurs
- Paramètres personnalisables par entreprise
- Rapports PDF générés côté serveur (sans dépendance externe)

## Stack
- Frontend: HTML5, CSS3, JavaScript vanilla
- Backend: PHP 8+ (MVC maison)
- DB: MySQL 8+
- Serveur: Apache/WAMP/XAMPP

## Arborescence
```text
app/
  Config/
  Controllers/
  Core/
  Models/
  Services/
  Views/
public/
  assets/css/app.css
  assets/js/app.js
  assets/js/charts.js
  index.php
database/schema.sql
```

## Installation rapide (WAMP/XAMPP)
1. Copier le projet dans votre répertoire web.
2. Configurer Apache pour pointer le `DocumentRoot` vers `public/`.
3. Copier `.env.example` vers `.env` et adapter les variables DB.
4. Créer la base et importer `database/schema.sql`.
5. Ouvrir l'application dans le navigateur.

## Comptes de démonstration (mot de passe: `password`)
- `superadmin@logiflow.com` (Admin SaaS)
- `admin@demo-company.com` (Admin entreprise)
- `dg@demo-company.com` (DG)
- `logistique@demo-company.com` (Responsable logistique)
- `magasinier@demo-company.com` (Magasinier)
- `chauffeur@demo-company.com` (Chauffeur)

## Modules disponibles
- Authentification & rôles
- Multi-entreprise SaaS
- Fournisseurs
- Achats + réception
- Produits + catégories + SKU/barcode
- Entrepôts + zones + emplacements
- Stocks (mouvements IN/OUT/ADJUST, FIFO/LIFO, alertes)
- Commandes clients + validation + préparation
- Facture simple imprimable par commande
- Module clients dédié
- Transport (véhicules, chauffeurs, planification, statuts)
- Interface chauffeur
- Dashboard avec graphiques
- Notifications (in-app + email via `mail()`)
- Rapports PDF (stocks, commandes, livraisons)
- Logs d'audit
- Paramètres entreprise

## API chauffeur (bonus)
### Login API
`POST /api/login`
```json
{
  "email": "chauffeur@demo-company.com",
  "password": "password"
}
```

### Mes livraisons
`GET /api/driver/deliveries`
Header: `Authorization: Bearer <token>`

### Mise à jour statut
`POST /api/driver/deliveries/{id}/status`
Header: `Authorization: Bearer <token>`
```json
{
  "status": "in_transit",
  "lat": 48.8566,
  "lng": 2.3522,
  "notes": "Trajet en cours"
}
```

## Sécurité implémentée
- Requêtes préparées PDO (anti-SQL injection)
- Mots de passe hashés (bcrypt)
- Contrôle d'accès par permission
- Session sécurisée
- Jetons CSRF sur formulaires
- Isolation des données par entreprise

## Limites actuelles (base évolutive)
- Génération PDF simplifiée (texte/tabulaire)
- SMTP avancé possible via intégration future PHPMailer
- Workflow facturation volontairement simple (peut être enrichi)
