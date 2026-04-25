<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Models\Company;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AuditService;

final class TicketController extends Controller
{
    public function index(): void
    {
        if ($this->roleSlug() === 'driver') {
            Flash::set('error', 'Le module ticketing n\'est pas disponible pour le chauffeur.');
            $this->redirect('/driver/deliveries');
        }

        $ticketModel = new Ticket();
        $tickets = $ticketModel->listTickets();
        $commentsByTicket = [];
        foreach ($tickets as $ticket) {
            $ticketId = (int) $ticket['id'];
            $commentsByTicket[$ticketId] = $ticketModel->commentsByTicket($ticketId);
        }

        $users = array_values(array_filter(
            (new User())->listUsers(),
            static fn (array $u): bool => (int) ($u['is_active'] ?? 0) === 1
        ));

        $isSuper = (Auth::user()['role_slug'] ?? '') === 'super_admin';
        $companies = $isSuper ? (new Company())->all('id ASC') : [];

        $this->view('tickets/index', [
            'tickets' => $tickets,
            'commentsByTicket' => $commentsByTicket,
            'users' => $users,
            'stats' => $ticketModel->stats(),
            'isSuper' => $isSuper,
            'companies' => $companies,
        ]);
    }

    public function store(): void
    {
        if ($this->roleSlug() === 'driver') {
            Flash::set('error', 'Action non autorisée.');
            $this->redirect('/driver/deliveries');
        }

        $title = trim((string) $this->input('title'));
        $description = trim((string) $this->input('description'));
        if ($title === '' || $description === '') {
            Flash::set('error', 'Titre et description sont obligatoires.');
            $this->redirect('/tickets');
        }

        try {
            $assignédTo = $this->nullableInt($this->input('assignéd_to'));
            $ticketId = (new Ticket())->createTicket([
                'company_id' => $this->nullableInt($this->input('company_id')),
                'ticket_number' => $this->generateTicketNumber(),
                'title' => $title,
                'description' => $description,
                'module_name' => $this->input('module_name'),
                'priority' => (string) $this->input('priority', 'medium'),
                'reporter_id' => Auth::id(),
                'assignéd_to' => $assignédTo,
                'due_at' => $this->normalizeDateTime((string) $this->input('due_at', '')),
            ]);

            if ($assignédTo !== null && $assignédTo !== Auth::id()) {
                (new Notification())->createForUser(
                    $assignédTo,
                    'ticket_assignéd',
                    'Nouveau ticket assigné',
                    'Vous avez ete assigné au ticket #' . $ticketId . '.'
                );
            }

            (new AuditService())->log('CREATE', 'tickets', $ticketId, ['title' => $title]);
            Flash::set('success', 'Ticket créé avec succes.');
            $this->redirect('/tickets#ticket-' . $ticketId);
        } catch (\Throwable $e) {
            Flash::set('error', 'Impossible de créer le ticket: ' . $e->getMessage());
            $this->redirect('/tickets');
        }
    }

    public function assign(int $id): void
    {
        if ($this->roleSlug() === 'driver') {
            Flash::set('error', 'Action non autorisée.');
            $this->redirect('/driver/deliveries');
        }

        $ticketModel = new Ticket();
        $ticket = $ticketModel->find($id);
        if (!$ticket) {
            Flash::set('error', 'Ticket introuvable.');
            $this->redirect('/tickets');
        }

        try {
            $assignédTo = $this->nullableInt($this->input('assignéd_to'));
            $ticketModel->assignTo($id, $assignédTo);

            if ($assignédTo !== null && $assignédTo !== Auth::id()) {
                (new Notification())->createForUser(
                    $assignédTo,
                    'ticket_assignéd',
                    'Ticket assigné',
                    'Le ticket ' . $ticket['ticket_number'] . ' vous a ete assigné.'
                );
            }

            (new AuditService())->log('ASSIGN', 'tickets', $id, ['assignéd_to' => $assignédTo]);
            Flash::set('success', 'Assignation mise à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur d\'assignation: ' . $e->getMessage());
        }

        $this->redirect('/tickets#ticket-' . $id);
    }

    public function assignSelf(int $id): void
    {
        if ($this->roleSlug() === 'driver') {
            Flash::set('error', 'Action non autorisée.');
            $this->redirect('/driver/deliveries');
        }

        try {
            (new Ticket())->assignTo($id, Auth::id());
            (new AuditService())->log('ASSIGN_SELF', 'tickets', $id, ['assignéd_to' => Auth::id()]);
            Flash::set('success', 'Ticket assigné a votre utilisateur.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur d\'auto-assignation: ' . $e->getMessage());
        }

        $this->redirect('/tickets#ticket-' . $id);
    }

    public function updateStatus(int $id): void
    {
        if ($this->roleSlug() === 'driver') {
            Flash::set('error', 'Action non autorisée.');
            $this->redirect('/driver/deliveries');
        }

        $status = (string) $this->input('status');
        $this->setStatus($id, $status);
    }

    public function close(int $id): void
    {
        $this->setStatus($id, 'closed');
    }

    public function reopen(int $id): void
    {
        $this->setStatus($id, 'open');
    }

    public function updatePriority(int $id): void
    {
        if ($this->roleSlug() === 'driver') {
            Flash::set('error', 'Action non autorisée.');
            $this->redirect('/driver/deliveries');
        }

        $priority = (string) $this->input('priority');
        try {
            (new Ticket())->updateTicketPriority($id, $priority);
            (new AuditService())->log('UPDATE_PRIORITY', 'tickets', $id, ['priority' => $priority]);
            Flash::set('success', 'Priorité du ticket mise à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur priorité ticket: ' . $e->getMessage());
        }

        $this->redirect('/tickets#ticket-' . $id);
    }

    public function addComment(int $id): void
    {
        if ($this->roleSlug() === 'driver') {
            Flash::set('error', 'Action non autorisée.');
            $this->redirect('/driver/deliveries');
        }

        $comment = trim((string) $this->input('comment'));
        if ($comment === '') {
            Flash::set('error', 'Le commentaire ne peut pas etre vide.');
            $this->redirect('/tickets#ticket-' . $id);
        }

        $ticketModel = new Ticket();
        $ticket = $ticketModel->find($id);
        if (!$ticket) {
            Flash::set('error', 'Ticket introuvable.');
            $this->redirect('/tickets');
        }

        try {
            $ticketModel->addComment($id, $comment);

            $targets = array_unique(array_filter([
                (int) ($ticket['assignéd_to'] ?? 0),
                (int) ($ticket['reporter_id'] ?? 0),
            ]));

            foreach ($targets as $targetId) {
                if ($targetId <= 0 || $targetId === Auth::id()) {
                    continue;
                }

                (new Notification())->createForUser(
                    $targetId,
                    'ticket_comment',
                    'Nouveau commentaire ticket',
                    'Un commentaire a ete ajoute sur le ticket ' . $ticket['ticket_number'] . '.'
                );
            }

            (new AuditService())->log('COMMENT', 'tickets', $id);
            Flash::set('success', 'Commentaire ajoute.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur commentaire ticket: ' . $e->getMessage());
        }

        $this->redirect('/tickets#ticket-' . $id);
    }

    private function setStatus(int $id, string $status): void
    {
        $ticketModel = new Ticket();
        $ticket = $ticketModel->find($id);
        if (!$ticket) {
            Flash::set('error', 'Ticket introuvable.');
            $this->redirect('/tickets');
        }

        try {
            $ticketModel->updateTicketStatus($id, $status);
            (new AuditService())->log('UPDATE_STATUS', 'tickets', $id, ['status' => $status]);
            Flash::set('success', 'Statut du ticket mis à jour.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur statut ticket: ' . $e->getMessage());
        }

        $this->redirect('/tickets#ticket-' . $id);
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        $int = (int) $value;
        return $int > 0 ? $int : null;
    }

    private function normalizeDateTime(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $timestamp = strtotime($raw);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function generateTicketNumber(): string
    {
        return 'TCK-' . date('Ymd-His') . '-' . random_int(100, 999);
    }
}
