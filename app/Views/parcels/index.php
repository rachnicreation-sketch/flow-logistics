<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;">Gestion des Colis & Expéditions</h2>
    <button class="btn btn-primary" onclick="document.getElementById('modal-add').style.display='block'">
        <i class="fa-solid fa-plus"></i> Nouveau Colis
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tracking / Code-barres</th>
                        <th>Livraison Liée</th>
                        <th>Commande</th>
                        <th>Poids (kg)</th>
                        <th>Dimensions</th>
                        <th>Statut</th>
                        <th>Date Création</th>
                        <th style="text-align: right;">Mettre à jour</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parcels as $parcel): ?>
                        <tr>
                            <td>
                                <div style="font-weight: bold; color: #0f766e;"><?= e($parcel['tracking_number']) ?></div>
                                <div style="font-size: 0.8rem; color: #64748b;"><i class="fa-solid fa-barcode"></i> <?= e($parcel['barcode']) ?></div>
                            </td>
                            <td>
                                Livraison #<?= e((string)$parcel['delivery_id']) ?><br>
                                <span style="font-size: 0.8rem; color: #64748b;">(<?= e($parcel['plate_number'] ?? 'Aucun véhicule') ?>)</span>
                            </td>
                            <td><?= e($parcel['order_reference']) ?></td>
                            <td><?= e((string)(float) $parcel['weight_kg']) ?></td>
                            <td><?= e($parcel['dimensions'] ?: '-') ?></td>
                            <td>
                                <?php if ($parcel['status'] === 'prepared'): ?>
                                    <span class="badge badge-secondary">Préparé</span>
                                <?php elseif ($parcel['status'] === 'scanned'): ?>
                                    <span class="badge badge-primary">Scanné</span>
                                <?php elseif ($parcel['status'] === 'loaded'): ?>
                                    <span class="badge badge-warning">Chargé</span>
                                <?php elseif ($parcel['status'] === 'delivered'): ?>
                                    <span class="badge badge-success">Livré</span>
                                <?php elseif ($parcel['status'] === 'lost'): ?>
                                    <span class="badge badge-danger">Perdu</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><?= e($parcel['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= e(date('d/m/Y H:i', strtotime($parcel['created_at']))) ?></td>
                            <td style="text-align: right;">
                                <form method="post" action="<?= e(url('/parcels/' . $parcel['id'] . '/status')) ?>" style="display: inline-block;">
                                    <?= csrf_field() ?>
                                    <select name="status" class="form-control" style="width: auto; display: inline-block; padding: 2px 5px;" onchange="this.form.submit()">
                                        <option value="prepared" <?= $parcel['status'] === 'prepared' ? 'selected' : '' ?>>Préparé</option>
                                        <option value="scanned" <?= $parcel['status'] === 'scanned' ? 'selected' : '' ?>>Scanné</option>
                                        <option value="loaded" <?= $parcel['status'] === 'loaded' ? 'selected' : '' ?>>Chargé</option>
                                        <option value="delivered" <?= $parcel['status'] === 'delivered' ? 'selected' : '' ?>>Livré</option>
                                        <option value="lost" <?= $parcel['status'] === 'lost' ? 'selected' : '' ?>>Perdu</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($parcels)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 30px;">
                                <i class="fa-solid fa-box-open" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                Aucun colis généré pour le moment.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajout Colis -->
<div id="modal-add" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: #fff; width: 500px; margin: 100px auto; border-radius: 8px; padding: 20px;">
        <h3 style="margin-top: 0;">Générer une étiquette Colis</h3>
        <form method="post" action="<?= e(url('/parcels')) ?>">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Livraison associée</label>
                <select name="delivery_id" class="form-control" required>
                    <option value="">Sélectionnez une livraison en cours</option>
                    <?php foreach ($deliveries as $d): ?>
                        <option value="<?= e((string)$d['id']) ?>">Livraison #<?= e((string)$d['id']) ?> (Commande <?= e((string)$d['order_id']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label>Poids estimé (kg)</label>
                    <input type="number" step="0.1" name="weight_kg" class="form-control" value="1.0">
                </div>
                <div class="form-group">
                    <label>Dimensions (ex: 50x30x20)</label>
                    <input type="text" name="dimensions" class="form-control" placeholder="LxlxH">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-add').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-primary">Générer le Colis</button>
            </div>
        </form>
    </div>
</div>
