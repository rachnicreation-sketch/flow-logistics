<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin: 0;">Dépenses Opérationnelles</h2>
    <button class="btn btn-primary" onclick="document.getElementById('modal-expense').style.display='block'">
        <i class="fa-solid fa-receipt"></i> Saisir une dépense
    </button>
</div>

<!-- KPIs par catégorie -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px;">
    <?php
    $categoryIcons = [
        'fuel' => ['⛽', 'Carburant', '#f59e0b'],
        'toll' => ['🛣️', 'Péages', '#8b5cf6'],
        'maintenance' => ['🔧', 'Maintenance', '#0f766e'],
        'office' => ['🏢', 'Bureau', '#3b82f6'],
        'insurance' => ['🛡️', 'Assurances', '#64748b'],
    ];
    $totalAll = 0;
    foreach ($totals_by_category as $tc): $totalAll += (float)$tc['total']; endforeach;
    ?>
    <?php foreach ($totals_by_category as $tc):
        $cat = $tc['category'];
        $icon = $categoryIcons[$cat][0] ?? '💰';
        $label = $categoryIcons[$cat][1] ?? ucfirst($cat);
        $color = $categoryIcons[$cat][2] ?? '#64748b';
    ?>
    <div class="card" style="border-left: 4px solid <?= $color ?>;">
        <div style="text-align: center; padding: 15px;">
            <div style="font-size: 1.8rem; margin-bottom: 5px;"><?= $icon ?></div>
            <div style="font-size: 0.85rem; color: #64748b;"><?= $label ?></div>
            <div style="font-size: 1.2rem; font-weight: bold; color: <?= $color ?>;">
                <?= number_format((float)$tc['total'], 2, ',', ' ') ?> €
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <div class="card" style="border-left: 4px solid #dc2626;">
        <div style="text-align: center; padding: 15px;">
            <div style="font-size: 1.8rem; margin-bottom: 5px;">📊</div>
            <div style="font-size: 0.85rem; color: #64748b;">Total dépenses</div>
            <div style="font-size: 1.2rem; font-weight: bold; color: #dc2626;">
                <?= number_format($totalAll, 2, ',', ' ') ?> €
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Catégorie</th>
                        <th>Description</th>
                        <th>Véhicule</th>
                        <th>Saisi par</th>
                        <th>Date</th>
                        <th style="text-align: right;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $exp):
                        $cat = $exp['category'];
                        $icon = $categoryIcons[$cat][0] ?? '💰';
                        $label = $categoryIcons[$cat][1] ?? ucfirst($cat);
                    ?>
                        <tr>
                            <td><span style="margin-right: 6px;"><?= $icon ?></span><?= e($label) ?></td>
                            <td><?= e($exp['description']) ?></td>
                            <td><?= e($exp['plate_number'] ?? '—') ?></td>
                            <td><?= e($exp['user_name'] ?? '—') ?></td>
                            <td><?= e(date('d/m/Y', strtotime($exp['expense_date']))) ?></td>
                            <td style="text-align: right; font-weight: bold; color: #dc2626;">
                                <?= number_format((float)$exp['amount'], 2, ',', ' ') ?> €
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($expenses)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">
                                Aucune dépense saisie.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Dépense -->
<div id="modal-expense" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: #fff; width: 520px; margin: 120px auto; border-radius: 8px; padding: 25px;">
        <h3 style="margin-top: 0;">Saisir une dépense</h3>
        <form method="post" action="<?= e(url('/expenses')) ?>">
            <?= csrf_field() ?>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="category" class="form-control" required>
                        <option value="fuel">⛽ Carburant</option>
                        <option value="toll">🛣️ Péages</option>
                        <option value="maintenance">🔧 Maintenance véhicule</option>
                        <option value="office">🏢 Frais de bureau</option>
                        <option value="insurance">🛡️ Assurance</option>
                        <option value="other">💼 Autre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Montant (€)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Description</label>
                    <input type="text" name="description" class="form-control" required placeholder="Plein d'essence A6 - Paris-Lyon">
                </div>
                <div class="form-group">
                    <label>Véhicule lié (optionnel)</label>
                    <select name="vehicle_id" class="form-control">
                        <option value="">Aucun</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= e((string)$v['id']) ?>"><?= e($v['plate_number']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-expense').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
