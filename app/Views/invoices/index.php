<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 style="margin: 0; font-size: 1.25rem;">Factures & Paiements</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Facture</th>
                        <th>Client</th>
                        <th>Réf. Commande</th>
                        <th>Date</th>
                        <th>Echéance</th>
                        <th>Montant TTC</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td><strong><?= e($inv['invoice_number']) ?></strong></td>
                            <td><?= e($inv['customer_name']) ?></td>
                            <td><?= e($inv['order_reference']) ?></td>
                            <td><?= e(date('d/m/Y', strtotime($inv['created_at']))) ?></td>
                            <td><?= e(date('d/m/Y', strtotime($inv['due_date']))) ?></td>
                            <td><strong><?= number_format((float) $inv['total_incl_tax'], 2, ',', ' ') ?> €</strong></td>
                            <td>
                                <?php if ($inv['status'] === 'paid'): ?>
                                    <span class="badge badge-success">Payée</span>
                                <?php elseif ($inv['status'] === 'partially_paid'): ?>
                                    <span class="badge badge-warning">Partielle</span>
                                <?php elseif ($inv['status'] === 'unpaid'): ?>
                                    <span class="badge badge-danger">Impayée</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><?= e($inv['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?= e(url('/invoices/' . $inv['id'])) ?>" class="btn btn-sm btn-outline">Détails / Payer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b;">Aucune facture trouvée.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
