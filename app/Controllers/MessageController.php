<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use App\Services\AuditService;

final class MessageController extends Controller
{
    public function index(): void
    {
        $messageModel = new Message();
        $users = array_values(array_filter(
            (new User())->listUsers(),
            static fn (array $u): bool => (int) ($u['is_active'] ?? 0) === 1
        ));

        $this->view('messages/index', [
            'inbox' => $messageModel->inbox(),
            'sent' => $messageModel->sent(),
            'users' => $users,
            'unreadCount' => $messageModel->unreadCount(),
            'currentUserId' => (int) (Auth::id() ?? 0),
            'canSend' => $this->roleSlug() !== 'driver',
        ]);
    }

    public function store(): void
    {
        if ($this->roleSlug() === 'driver') {
            Flash::set('error', 'Le chauffeur ne peut pas envoyer de message.');
            $this->redirect('/messages');
        }

        $recipientId = (int) $this->input('recipient_id');
        $subject = trim((string) $this->input('subject'));
        $body = trim((string) $this->input('body'));

        if ($recipientId <= 0 || $subject === '' || $body === '') {
            Flash::set('error', 'Destinataire, sujet et message sont obligatoires.');
            $this->redirect('/messages');
        }

        try {
            $id = (new Message())->send($recipientId, $subject, $body);
            (new Notification())->createForUser(
                $recipientId,
                'message',
                'Nouveau message',
                $subject
            );
            (new AuditService())->log('SEND', 'messages', $id, ['recipient_id' => $recipientId]);
            Flash::set('success', 'Message envoye.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur envoi message: ' . $e->getMessage());
        }

        $this->redirect('/messages');
    }

    public function markRead(int $id): void
    {
        try {
            (new Message())->markAsRead($id);
            (new AuditService())->log('READ', 'messages', $id);
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur de lecture message: ' . $e->getMessage());
        }

        $this->redirect('/messages');
    }
}
