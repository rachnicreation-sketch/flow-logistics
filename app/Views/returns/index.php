<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin: 0;">Retours Logistiques (Reverse Logistics)</h2>
    <button class="btn btn-primary" onclick="document.getElementById('modal-return').style.display='block'">
        <i class="fa-solid fa-rotate-left"></i> Créer un retour
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Retour</th>
                        <th>Client</th>
                        <th>Commande originale</th>
                        <th>Motif</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Mettre à jour</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($returns as $ret): ?>
                        <tr>
                            <td><strong><?= e($ret['return_number']) ?></strong></td>
                            <td><?= e($ret['customer_name']) ?></td>
                            <td><?= e($ret['order_reference']) ?></td>
                            <td style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= e($ret['reason']) ?></td>
                            <td><?= e(date('d/m/Y', strtotime($ret['created_at']))) ?></td>
                            <td>
                                <?php
                                $retStatuses = [
                                    'requested' => ['label' => 'Demandé', 'class' => 'badge-secondary'],
                                    'approved' => ['label' => 'Approuvé', 'class' => 'badge-primary'],
                                    'received' => ['label' => 'Reçu', 'class' => 'badge-warning'],
                                    'inspected' => ['label' => 'Inspecté', 'class' => 'badge-warning'],
                                    'refunded' => ['label' => 'Remboursé', 'class' => 'badge-success'],
                                    'rejected' => ['label' => 'Rejeté', 'class' => 'badge-danger'],
                                ];
                                $rs = $retStatuses[$ret['status']] ?? ['label' => $ret['status'], 'class' => 'badge-secondary'];
                                ?>
                                <span class="badge <?= $rs['class'] ?>"><?= $rs['label'] ?></span>
                            </td>
                            <td style="text-align: right;">
                                <form method="post" action="<?= e(url('/returns/' . $ret['id'] . '/status')) ?>" style="display: inline-block;">
                                    <?= csrf_field() ?>
                                    <select name="status" class="form-control" style="width: auto; display: inline-block;" onchange="this.form.submit()">
                                        <?php foreach ($retStatuses as $val => $meta): ?>
                                            <option value="<?= e($val) ?>" <?= $ret['status'] === $val ? 'selected' : '' ?>><?= $meta['label'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($returns)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #64748b; padding: 30px;">
                                <i class="fa-solid fa-box-open" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                Aucun retour enregistré.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Créer Retour -->
<div id="modal-return" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: #fff; width: 650px; margin: 80px auto; border-radius: 8px; padding: 25px;">
        <h3 style="margin-top: 0;">Nouveau Retour Client</h3>
        <form method="post" action="<?= e(url('/returns')) ?>" id="return-form">
            <?= csrf_field() ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Commande concernée</label>
                    <select name="order_id" id="return-order-id" class="form-control" required>
                        <option value="">Sélectionnez une commande</option>
                        <?php foreach ($orders as $o): ?>
                            <option value="<?= e((string)$o['id']) ?>"><?= e($o['reference']) ?> — <?= e($o['customer_name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Motif du retour</label>
                    <textarea name="reason" class="form-control" rows="2" required placeholder="Produit endommagé, non-conforme, erreur de commande..."></textarea>
                </div>
            </div>

            <h4 style="margin-bottom: 10px;">Articles à retourner</h4>
            <div id="return-items-container">
                <div class="return-item" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 8px; align-items: center; margin-bottom: 10px;">
                    <select name="product_id[]" class="form-control">
                        <option value="">Produit...</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= e((string)$p['id']) ?>"><?= e($p['name']) ?> (<?= e($p['sku']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="quantity[]" class="form-control" placeholder="Qté" value="1" step="1" min="1">
                    <select name="condition_status[]" class="form-control">
                        <option value="resellable">Revendable</option>
                        <option value="damaged">Endommagé</option>
                        <option value="destroyed">À détruire</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-outline" onclick="removeReturnLine(this)">✕</button>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline" onclick="addReturnLine()" style="margin-bottom: 20px;">
                + Ajouter un article
            </button>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-return').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer le retour</button>
            </div>
        </form>
    </div>
</div>

<script>
const returnItemTpl = `<div class="return-item" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 8px; align-items: center; margin-bottom: 10px;">
    <select name="product_id[]" class="form-control">
        <option value="">Produit...</option>
        <?php foreach ($products as $p): ?><option value="<?= e((string)$p['id']) ?>"><?= e($p['name']) ?></option><?php endforeach; ?>
    </select>
    <input type="number" name="quantity[]" class="form-control" placeholder="Qté" value="1" step="1" min="1">
    <select name="condition_status[]" class="form-control">
        <option value="resellable">Revendable</option>
        <option value="damaged">Endommagé</option>
        <option value="destroyed">À détruire</option>
    </select>
    <button type="button" class="btn btn-sm btn-outline" onclick="removeReturnLine(this)">✕</button>
</div>`;

function addReturnLine() {
    document.getElementById('return-items-container').insertAdjacentHTML('beforeend', returnItemTpl);
}
function removeReturnLine(btn) {
    btn.closest('.return-item')?.remove();
}
</script>
