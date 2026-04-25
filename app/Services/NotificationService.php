<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Config;
use App\Models\Notification;

final class NotificationService
{
    private Notification $notifications;

    public function __construct()
    {
        $this->notifications = new Notification();
    }

    public function inApp(?int $userId, string $type, string $title, string $message): void
    {
        $this->notifications->createForUser($userId, $type, $title, $message);
    }

    public function email(string $to, string $subject, string $body): bool
    {
        $mailConfig = Config::get('mail');
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/plain; charset=UTF-8',
            'From: ' . $mailConfig['from_name'] . ' <' . $mailConfig['from_email'] . '>',
        ];

        if (config('app.env') === 'local') {
            // Dans un environnement local WAMP, on ne tente pas d'envoyer de vrai mail
            // On loggue simplement le contenu dans un fichier pour le debug
            $logMsg = sprintf("[%s] EMAIL TO: %s | SUBJECT: %s | BODY: %s\n", date('Y-m-d H:i:s'), $to, $subject, $body);
            file_put_contents(base_path('logs/mail.log'), $logMsg, FILE_APPEND);
            return true;
        }

        return @mail($to, $subject, $body, implode("\r\n", $headers));
    }

    public function lowStockAlert(string $productName, float $qty, float $min): void
    {
        $title = 'Alerte de stock faible';
        $message = sprintf('Le produit %s est à %.2f (minimum: %.2f).', $productName, $qty, $min);
        $this->inApp(null, 'stock', $title, $message);
    }

    public function orderConfirmation(string $customerEmail, string $orderReference): void
    {
        $subject = 'Confirmation de commande ' . $orderReference;
        $body = "Votre commande {$orderReference} a bien été enregistrée.";
        $this->email($customerEmail, $subject, $body);
    }
}

