<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Expense;
use App\Models\Vehicle;
use App\Core\Auth;

final class ExpenseController extends Controller
{
    public function index(): void
    {
        $this->view('expenses/index', [
            'expenses' => (new Expense())->listWithDetails(),
            'totals_by_category' => (new Expense())->totalByCategory(),
            'vehicles' => (new Vehicle())->all('plate_number ASC'),
        ]);
    }

    public function store(): void
    {
        $category = trim((string) $this->input('category'));
        $amount = (float) $this->input('amount');
        $description = trim((string) $this->input('description'));
        $vehicleId = (int) $this->input('vehicle_id') ?: null;
        $expenseDate = (string) $this->input('expense_date', date('Y-m-d'));

        if ($category === '' || $amount <= 0 || $description === '') {
            Flash::set('error', 'Catégorie, montant et description sont obligatoires.');
            $this->redirect('/expenses');
        }

        $user = Auth::user();

        try {
            (new Expense())->createExpense([
                'category' => $category,
                'amount' => $amount,
                'description' => $description,
                'vehicle_id' => $vehicleId,
                'user_id' => $user['id'] ?? null,
                'expense_date' => $expenseDate
            ]);
            Flash::set('success', 'Dépense enregistrée.');
        } catch (\Throwable $e) {
            Flash::set('error', 'Erreur : ' . $e->getMessage());
        }

        $this->redirect('/expenses');
    }
}
