<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin: 0;">Douanes — Import / Export</h2>
    <button class="btn btn-primary" onclick="document.getElementById('modal-customs').style.display='block'">
        <i class="fa-solid fa-stamp"></i> Nouvelle déclaration
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Déclaration</th>
                        <th>Type</th>
                        <th>Bureau douanier</th>
                        <th>Commande / Achat lié</th>
                        <th>Taxes (€)</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($declarations as $d): ?>
                        <tr>
                            <td><strong><?= e($d['declaration_number']) ?></strong></td>
                            <td>
                                <?php if ($d['type'] === 'import'): ?>
                                    <span class="badge badge-primary">Import</span>
                                <?php elseif ($d['type'] === 'export'): ?>
                                    <span class="badge badge-warning">Export</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Transit</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($d['customs_office']) ?></td>
                            <td>
                                <?= $d['order_reference'] ? '🛒 ' . e($d['order_reference']) : '' ?>
                                <?= $d['purchase_reference'] ? '📦 ' . e($d['purchase_reference']) : '' ?>
                                <?= (!$d['order_reference'] && !$d['purchase_reference']) ? '—' : '' ?>
                            </td>
                            <td><strong><?= number_format((float)$d['taxes_amount'], 2, ',', ' ') ?> €</strong></td>
                            <td><?= e(date('d/m/Y', strtotime($d['created_at']))) ?></td>
                            <td>
                                <?php
                                $statuses = [
                                    'draft' => ['Brouillon', 'badge-secondary'],
                                    'submitted' => ['Soumise', 'badge-primary'],
                                    'cleared' => ['Dédouanée ✓', 'badge-success'],
                                    'rejected' => ['Rejetée', 'badge-danger']
                                ];
                                $st = $statuses[$d['status']] ?? [$d['status'], 'badge-secondary'];
                                ?>
                                <span class="badge <?= $st[1] ?>"><?= $st[0] ?></span>
                            </td>
                            <td style="text-align: right;">
                                <form method="post" action="<?= e(url('/customs/' . $d['id'] . '/status')) ?>" style="display: inline-block;">
                                    <?= csrf_field() ?>
                                    <select name="status" class="form-control" style="width: auto; display: inline-block;" onchange="this.form.submit()">
                                        <?php foreach ($statuses as $val => [$label, $cls]): ?>
                                            <option value="<?= e($val) ?>" <?= $d['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($declarations)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 30px;">
                                <i class="fa-solid fa-globe" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                Aucune déclaration douanière.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Customs -->
<div id="modal-customs" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: #fff; width: 580px; margin: 100px auto; border-radius: 8px; padding: 25px;">
        <h3 style="margin-top: 0;">Nouvelle déclaration douanière</h3>
        <form method="post" action="<?= e(url('/customs')) ?>">
            <?= csrf_field() ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label>Type de déclaration</label>
                    <select name="type" class="form-control" required>
                        <option value="import">Import</option>
                        <option value="export">Export</option>
                        <option value="transit">Transit</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Bureau douanier</label>
                    <input type="text" name="customs_office" class="form-control" required placeholder="Ex: Douane de Roissy">
                </div>
                <div class="form-group">
                    <label>Montant des taxes (€)</label>
                    <input type="number" step="0.01" name="taxes_amount" class="form-control" value="0.00">
                </div>
                <div class="form-group">
                    <label>N° Déclaration (auto si vide)</label>
                    <input type="text" name="declaration_number" class="form-control" placeholder="DCL-20260527-XXXX">
                </div>
                <div class="form-group">
                    <label>Commande liée (optionnel)</label>
                    <select name="order_id" class="form-control">
                        <option value="">Aucune</option>
                        <?php foreach ($orders as $o): ?>
                            <option value="<?= e((string)$o['id']) ?>"><?= e($o['reference']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Achat/Import lié (optionnel)</label>
                    <select name="purchase_id" class="form-control">
                        <option value="">Aucun</option>
                        <?php foreach ($purchases as $p): ?>
                            <option value="<?= e((string)$p['id']) ?>"><?= e($p['reference']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-customs').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
