<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <!-- Facture Détails -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between;">
            <h2 style="margin: 0;">Facture <?= e($invoice['invoice_number']) ?></h2>
            <a href="<?= e(url('/invoices')) ?>" class="btn btn-outline btn-sm">Retour</a>
        </div>
        <div class="card-body">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div>
                    <h4 style="margin-bottom: 5px; color: #64748b;">Client</h4>
                    <p style="margin: 0; font-weight: bold;"><?= e($customer['name'] ?? '-') ?></p>
                    <p style="margin: 0;"><?= e($customer['address'] ?? '') ?></p>
                </div>
                <div style="text-align: right;">
                    <h4 style="margin-bottom: 5px; color: #64748b;">Détails</h4>
                    <p style="margin: 0;">Date: <?= e(date('d/m/Y', strtotime($invoice['created_at']))) ?></p>
                    <p style="margin: 0; font-weight: bold; color: #dc2626;">Échéance: <?= e(date('d/m/Y', strtotime($invoice['due_date']))) ?></p>
                </div>
            </div>

            <table class="table" style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th style="text-align: center;">Qté</th>
                        <th style="text-align: right;">P.U (HT)</th>
                        <th style="text-align: right;">Total (HT)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= e($item['product_name']) ?></td>
                            <td style="text-align: center;"><?= e((string)(float) $item['quantity']) ?></td>
                            <td style="text-align: right;"><?= number_format((float) $item['unit_price'], 2, ',', ' ') ?> €</td>
                            <td style="text-align: right;"><?= number_format((float) $item['total_price'], 2, ',', ' ') ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="width: 300px; margin-left: auto;">
                <table class="table" style="border: none;">
                    <tr>
                        <td style="text-align: right; color: #64748b;">Total HT :</td>
                        <td style="text-align: right;"><strong><?= number_format((float) $invoice['total_excl_tax'], 2, ',', ' ') ?> €</strong></td>
                    </tr>
                    <tr>
                        <td style="text-align: right; color: #64748b;">TVA (20%) :</td>
                        <td style="text-align: right;"><strong><?= number_format((float) $invoice['tax_amount'], 2, ',', ' ') ?> €</strong></td>
                    </tr>
                    <tr>
                        <td style="text-align: right; font-size: 1.2rem;">Total TTC :</td>
                        <td style="text-align: right; font-size: 1.2rem; color: #0f766e;"><strong><?= number_format((float) $invoice['total_incl_tax'], 2, ',', ' ') ?> €</strong></td>
                    </tr>
                </table>
            </div>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="<?= e(url('/orders/' . $invoice['order_id'] . '/invoice')) ?>" target="_blank" class="btn btn-outline"><i class="fa-solid fa-print"></i> Imprimer le PDF simple</a>
            </div>
        </div>
    </div>

    <!-- Paiements -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Formulaire de paiement -->
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0; font-size: 1.1rem;">Enregistrer un paiement</h3>
            </div>
            <div class="card-body">
                <?php
                $totalPaid = array_sum(array_column($payments, 'amount'));
                $remaining = max(0, (float) $invoice['total_incl_tax'] - $totalPaid);
                ?>
                <div style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Déjà payé:</span>
                        <strong style="color: #10b981;"><?= number_format($totalPaid, 2, ',', ' ') ?> €</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Reste à payer:</span>
                        <strong style="color: #dc2626;"><?= number_format($remaining, 2, ',', ' ') ?> €</strong>
                    </div>
                </div>

                <?php if ($invoice['status'] !== 'paid'): ?>
                <form method="post" action="<?= e(url('/invoices/' . $invoice['id'] . '/pay')) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Montant reçu (€)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="<?= e((string) $remaining) ?>" max="<?= e((string) $remaining) ?>" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Moyen de paiement</label>
                        <select name="payment_method" class="form-control">
                            <option value="bank_transfer">Virement bancaire</option>
                            <option value="credit_card">Carte de crédit</option>
                            <option value="check">Chèque</option>
                            <option value="cash">Espèces</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Référence (facultatif)</label>
                        <input type="text" name="reference" class="form-control" placeholder="N° transaction...">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Valider le paiement</button>
                </form>
                <?php else: ?>
                    <div class="alert alert-success" style="text-align: center;">
                        <i class="fa-solid fa-check-circle"></i> Cette facture est totalement soldée.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Historique des paiements -->
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0; font-size: 1.1rem;">Historique</h3>
            </div>
            <div class="card-body">
                <?php if (empty($payments)): ?>
                    <p style="color: #64748b; font-size: 0.9rem;">Aucun paiement enregistré.</p>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php foreach ($payments as $p): ?>
                            <li style="border-bottom: 1px solid #e2e8f0; padding: 10px 0;">
                                <div style="display: flex; justify-content: space-between;">
                                    <strong><?= number_format((float) $p['amount'], 2, ',', ' ') ?> €</strong>
                                    <span style="font-size: 0.85rem; color: #64748b;"><?= e(date('d/m/Y', strtotime($p['payment_date']))) ?></span>
                                </div>
                                <div style="font-size: 0.85rem; color: #64748b;">
                                    <?= e(ucfirst(str_replace('_', ' ', $p['payment_method']))) ?>
                                    <?= !empty($p['reference']) ? ' - Réf: ' . e($p['reference']) : '' ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
