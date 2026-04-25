<?php

declare(strict_types=1);

final class SimplePdf
{
    private float $pageWidth = 595.0;   // A4 portrait
    private float $pageHeight = 842.0;  // A4 portrait
    private float $margin = 42.0;
    private float $y = 0.0;
    /** @var array<int, string> */
    private array $pages = [];
    /** @var array<int, string> */
    private array $stream = [];

    public function __construct()
    {
        $this->newPage();
    }

    public function newPage(): void
    {
        if (!empty($this->stream)) {
            $this->pages[] = "q\n" . implode("\n", $this->stream) . "\nQ";
        }

        $this->stream = [];
        $this->y = $this->pageHeight - $this->margin;
    }

    public function heading(string $text, int $level = 1): void
    {
        $sizes = [1 => 18.0, 2 => 14.0, 3 => 11.5];
        $size = $sizes[$level] ?? 11.5;
        $this->writeWrapped($text, $size, true, 98, 1.35, 0.0);
        $this->y -= 3.0;
    }

    public function paragraph(string $text, float $indent = 0.0): void
    {
        $this->writeWrapped($text, 10.5, false, 110, 1.45, $indent);
    }

    public function bullet(string $text, float $indent = 0.0): void
    {
        $this->writeWrapped('- ' . $text, 10.5, false, 106, 1.45, $indent + 10.0);
    }

    public function spacer(float $height = 6.0): void
    {
        $this->y -= $height;
        if ($this->y <= $this->margin + 20.0) {
            $this->newPage();
        }
    }

    public function save(string $path): void
    {
        if (!empty($this->stream)) {
            $this->pages[] = "q\n" . implode("\n", $this->stream) . "\nQ";
        }

        $pdf = $this->buildPdf();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, $pdf);
    }

    private function writeWrapped(
        string $text,
        float $fontSize,
        bool $bold,
        int $maxChars,
        float $lineHeightFactor,
        float $indent
    ): void {
        $wrapped = wordwrap(trim($text), $maxChars, "\n", false);
        $lines = explode("\n", $wrapped);
        $lineHeight = $fontSize * $lineHeightFactor;
        $x = $this->margin + $indent;

        foreach ($lines as $line) {
            if ($this->y <= $this->margin + 20.0) {
                $this->newPage();
            }
            $this->stream[] = $this->textCmd($x, $this->y, $fontSize, $line, $bold);
            $this->y -= $lineHeight;
        }
    }

    private function textCmd(float $x, float $y, float $size, string $text, bool $bold): string
    {
        $font = $bold ? 'F2' : 'F1';
        return sprintf(
            'BT /%s %.2f Tf %.2f %.2f Td (%s) Tj ET',
            $font,
            $size,
            $x,
            $y,
            $this->escapePdfText($text)
        );
    }

    private function escapePdfText(string $text): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        if ($encoded === false) {
            $encoded = $text;
        }

        $out = '';
        $len = strlen($encoded);
        for ($i = 0; $i < $len; $i++) {
            $ch = ord($encoded[$i]);
            if ($ch === 40 || $ch === 41 || $ch === 92) {
                $out .= '\\' . chr($ch);
                continue;
            }

            if ($ch < 32 || $ch > 126) {
                $out .= sprintf('\\%03o', $ch);
                continue;
            }

            $out .= chr($ch);
        }

        return $out;
    }

    private function buildPdf(): string
    {
        $objects = [];
        $objects[1] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';

        $kids = [];
        $objNum = 5;
        foreach ($this->pages as $_) {
            $kids[] = $objNum . ' 0 R';
            $objNum += 2;
        }
        $objects[2] = '2 0 obj << /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($this->pages) . ' >> endobj';
        $objects[3] = '3 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >> endobj';
        $objects[4] = '4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >> endobj';

        $objNum = 5;
        foreach ($this->pages as $pageContent) {
            $pageObj = $objNum;
            $contentObj = $objNum + 1;
            $objects[$pageObj] =
                $pageObj . ' 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . $this->pageWidth . ' ' . $this->pageHeight .
                '] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents ' . $contentObj . ' 0 R >> endobj';

            $objects[$contentObj] = $contentObj . ' 0 obj << /Length ' . strlen($pageContent) . " >> stream\n" . $pageContent . "\nendstream endobj";
            $objNum += 2;
        }

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0 => 0];
        foreach ($objects as $n => $obj) {
            $offsets[$n] = strlen($pdf);
            $pdf .= $obj . "\n";
        }

        $xrefStart = strlen($pdf);
        $maxObj = max(array_keys($objects));
        $pdf .= "xref\n0 " . ($maxObj + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $maxObj; $i++) {
            $offset = $offsets[$i] ?? 0;
            $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        $pdf .= 'trailer << /Size ' . ($maxObj + 1) . ' /Root 1 0 R >>' . "\n";
        $pdf .= "startxref\n" . $xrefStart . "\n%%EOF";
        return $pdf;
    }
}

$now = date('d/m/Y H:i');
$pdf = new SimplePdf();

$pdf->heading('Guide Utilisateur Flow Logistics', 1);
$pdf->paragraph('Version generée le ' . $now . '. Ce document explique l utilisation du logiciel profil par profil, avec les ecrans a utiliser et les operations quotidiennes recommandees.');
$pdf->spacer(8);

$pdf->heading('1. Acces Et Connexion', 2);
$pdf->bullet('Ouvrir l application dans le navigateur puis se connecter avec votre compte utilisateur.');
$pdf->bullet('Chaque utilisateur voit uniquement les menus autorisés par son role.');
$pdf->bullet('En cas d erreur 403, demander au DG ou au DM d ajuster les droits dans Utilisateurs.');
$pdf->spacer(6);

$pdf->heading('2. Workflow Global Recommande', 2);
$pdf->bullet('Achats: creer un bon de commande fournisseur.');
$pdf->bullet('Reception: receptionner l achat pour alimenter le stock.');
$pdf->bullet('Vente: creer puis valider la commande client.');
$pdf->bullet('Preparation: preparer la commande pour decrementation de stock.');
$pdf->bullet('Livraison: planifier puis suivre la livraison jusqu au statut delivered.');
$pdf->bullet('Pilotage: consulter rapports, notifications, messages et logs.');
$pdf->spacer(8);

$pdf->heading('3. Utilisation Par Profil', 2);

$pdf->heading('3.1 Directeur General (dg)', 3);
$pdf->paragraph('Objectif: pilotage complet de l entreprise et supervision des equipes.');
$pdf->bullet('Menus principaux: Dashboard, Utilisateurs, Fournisseurs, Produits, Entrepots, Stocks, Achats, Clients, Commandes, Livraisons, Messages, Notifications, Ticketing, Rapports, Logs, Parametres.');
$pdf->bullet('Actions quotidiennes: valider les commandes, suivre les KPI, surveiller les retards de livraison, consulter les tickets critiques.');
$pdf->bullet('Action de gouvernance: creer/mettre a jour les comptes utilisateurs et verifier la repartition des roles.');
$pdf->spacer(5);

$pdf->heading('3.2 Directeur Manager / Admin Entreprise (dm ou company_admin)', 3);
$pdf->paragraph('Objectif: gestion operationnelle transverse avec droits proches du DG.');
$pdf->bullet('Menus principaux: identiques au DG.');
$pdf->bullet('Actions quotidiennes: organiser les equipes, gerer les donnees de base (fournisseurs, produits, entrepots), piloter les flux achats-commandes-livraisons.');
$pdf->bullet('Parametrage: mettre a jour les informations de societe (Parametres) et les utilisateurs.');
$pdf->spacer(5);

$pdf->heading('3.3 Responsable Logistique (logistics_manager)', 3);
$pdf->paragraph('Objectif: execution et performance de la chaine logistique.');
$pdf->bullet('Menus principaux: Dashboard, Fournisseurs, Produits, Entrepots, Stocks, Achats, Clients, Commandes, Livraisons, Messages, Notifications, Ticketing, Rapports, Logs.');
$pdf->bullet('Cycle type: creer achat > receptionner > verifier stock > preparer commandes > planifier livraison.');
$pdf->bullet('Bonnes pratiques: maintenir les SKU propres, verifier les seuils mini, traiter les alertes stock faible chaque jour.');
$pdf->spacer(5);

$pdf->heading('3.4 Magasinier (storekeeper)', 3);
$pdf->paragraph('Objectif: fiabilite du stock et execution physique entrepot.');
$pdf->bullet('Menus principaux: Dashboard, Stocks, Messages, Notifications.');
$pdf->bullet('Actions autorisees importantes: reception d achat, preparation de commande, mouvements de stock.');
$pdf->bullet('Routine conseillee: controle des receptions, verification des ecarts, confirmation des preparations avant expedition.');
$pdf->spacer(5);

$pdf->heading('3.5 Chauffeur (driver)', 3);
$pdf->paragraph('Objectif: suivre et mettre a jour les livraisons en temps reel.');
$pdf->bullet('Menus principaux: Dashboard, Espace chauffeur, Messages, Notifications.');
$pdf->bullet('Depuis Espace chauffeur: ouvrir la livraison, passer le statut de pending a in_transit puis delivered (ou failed), ajouter notes et position GPS si disponible.');
$pdf->bullet('API mobile disponible: POST /api/login, GET /api/driver/deliveries, POST /api/driver/deliveries/{id}/status avec Bearer token.');
$pdf->spacer(6);

$pdf->heading('4. Procedures Critiques', 2);
$pdf->bullet('Validation commande: Commandes > choisir une commande > action Valider.');
$pdf->bullet('Preparation commande: Commandes > action Preparer > choisir l entrepot > confirmer la methode FIFO/LIFO.');
$pdf->bullet('Planification livraison: Livraisons > creer une livraison > associer commande + vehicule + chauffeur + date.');
$pdf->bullet('Facture: Commandes > ouvrir commande > Imprimer facture.');
$pdf->bullet('Audit: Logs pour tracer les actions sensibles (creation, suppression, changement de statut).');
$pdf->spacer(6);

$pdf->heading('5. Comptes De Demo Et Securite', 2);
$pdf->bullet('Comptes seed presents dans le schema: superadmin@flow-logistics.com, admin@flow-logistics.com, dg@flow-logistics.com (mot de passe initial: password).');
$pdf->bullet('Les autres profils (DM, Responsable Logistique, Magasinier, Chauffeur) peuvent etre crees depuis le module Utilisateurs.');
$pdf->bullet('Apres installation, changer tous les mots de passe par defaut et desactiver les comptes inutilises.');

$outputPath = dirname(__DIR__) . '/docs/Guide_Utilisateur_Flow_Logistics.pdf';
$pdf->save($outputPath);

echo 'PDF genere: ' . $outputPath . PHP_EOL;
