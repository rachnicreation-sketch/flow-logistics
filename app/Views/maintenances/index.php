<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin: 0; font-size: 1.5rem; color: #1e293b;">Maintenance & SAV Flotte</h2>
    <button class="btn btn-primary" onclick="document.getElementById('modal-add').style.display='block'">
        <i class="fa-solid fa-wrench"></i> Programmer une intervention
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Véhicule</th>
                        <th>Type d'intervention</th>
                        <th>Description</th>
                        <th>Coût estimé/réel</th>
                        <th>Date d'intervention</th>
                        <th>Prochaine échéance</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($maintenances as $maint): ?>
                        <tr>
                            <td>
                                <strong><?= e($maint['plate_number']) ?></strong><br>
                                <span style="font-size: 0.8rem; color: #64748b;"><?= e($maint['model'] ?? '') ?></span>
                            </td>
                            <td>
                                <?php
                                $types = [
                                    'routine' => 'Entretien régulier',
                                    'repair' => 'Réparation SAV',
                                    'inspection' => 'Contrôle Technique',
                                    'insurance' => 'Renouvellement Assurance'
                                ];
                                echo e($types[$maint['type']] ?? $maint['type']);
                                ?>
                            </td>
                            <td><?= e(strlen($maint['description']) > 40 ? substr($maint['description'], 0, 40).'...' : $maint['description']) ?></td>
                            <td><strong><?= number_format((float) $maint['cost'], 2, ',', ' ') ?> €</strong></td>
                            <td><?= e(date('d/m/Y', strtotime($maint['performed_at']))) ?></td>
                            <td style="color: #dc2626; font-weight: bold;">
                                <?= !empty($maint['next_due_at']) ? e(date('d/m/Y', strtotime($maint['next_due_at']))) : '-' ?>
                            </td>
                            <td>
                                <?php if ($maint['status'] === 'planned'): ?>
                                    <span class="badge badge-secondary">Planifiée</span>
                                <?php elseif ($maint['status'] === 'in_progress'): ?>
                                    <span class="badge badge-warning">En cours</span>
                                <?php elseif ($maint['status'] === 'completed'): ?>
                                    <span class="badge badge-success">Terminée</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <form method="post" action="<?= e(url('/maintenances/' . $maint['id'] . '/status')) ?>" style="display: inline-block;">
                                    <?= csrf_field() ?>
                                    <select name="status" class="form-control" style="width: auto; display: inline-block; padding: 2px 5px;" onchange="this.form.submit()">
                                        <option value="planned" <?= $maint['status'] === 'planned' ? 'selected' : '' ?>>Planifiée</option>
                                        <option value="in_progress" <?= $maint['status'] === 'in_progress' ? 'selected' : '' ?>>En cours</option>
                                        <option value="completed" <?= $maint['status'] === 'completed' ? 'selected' : '' ?>>Terminée</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($maintenances)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 30px;">
                                <i class="fa-solid fa-screwdriver-wrench" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                Aucune maintenance enregistrée.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajout Maintenance -->
<div id="modal-add" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: #fff; width: 600px; margin: 100px auto; border-radius: 8px; padding: 20px;">
        <h3 style="margin-top: 0;">Programmer une intervention</h3>
        <form method="post" action="<?= e(url('/maintenances')) ?>">
            <?= csrf_field() ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label>Véhicule concerné</label>
                    <select name="vehicle_id" class="form-control" required>
                        <option value="">Sélectionnez un véhicule</option>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= e((string)$v['id']) ?>"><?= e($v['plate_number']) ?> (<?= e($v['model'] ?? 'N/A') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Type d'intervention</label>
                    <select name="type" class="form-control" required>
                        <option value="routine">Entretien régulier (Vidange, Pneus)</option>
                        <option value="repair">Réparation SAV (Panne)</option>
                        <option value="inspection">Contrôle Technique</option>
                        <option value="insurance">Renouvellement Assurance</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label>Description des travaux / notes</label>
                <textarea name="description" class="form-control" rows="3" required placeholder="Remplacement des plaquettes de frein..."></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label>Coût prévisionnel (€)</label>
                    <input type="number" step="0.01" name="cost" class="form-control" value="0.00">
                </div>
                <div class="form-group">
                    <label>Date prévue</label>
                    <input type="date" name="performed_at" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Prochaine échéance</label>
                    <input type="date" name="next_due_at" class="form-control">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-add').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-primary">Programmer</button>
            </div>
        </form>
    </div>
</div>
