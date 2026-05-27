<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin: 0;">Chauffeurs — Gestion RH</h2>
    <button class="btn btn-primary" onclick="document.getElementById('modal-driver').style.display='block'">
        <i class="fa-solid fa-id-card"></i> Saisir / Mettre à jour un profil
    </button>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($drivers)): ?>
            <div style="text-align: center; padding: 40px; color: #64748b;">
                <i class="fa-solid fa-user-tie" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                <p>Aucun utilisateur avec le rôle <strong>Driver</strong> trouvé.<br>
                Créez d'abord un utilisateur avec le rôle <em>Driver</em> dans la gestion des utilisateurs.</p>
            </div>
        <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px;">
            <?php foreach ($drivers as $d): ?>
                <div class="card" style="border: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: #0f766e; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.3rem; font-weight: bold; flex-shrink: 0;">
                            <?= strtoupper(substr($d['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <strong style="font-size: 1.05rem;"><?= e($d['name']) ?></strong><br>
                            <span style="color: #64748b; font-size: 0.85rem;"><?= e($d['email']) ?></span>
                        </div>
                        <?php
                        $statusLabels = [
                            'available' => ['label' => 'Disponible', 'class' => 'badge-success'],
                            'on_leave' => ['label' => 'En congé', 'class' => 'badge-warning'],
                            'sick' => ['label' => 'Arrêt maladie', 'class' => 'badge-warning'],
                            'suspended' => ['label' => 'Suspendu', 'class' => 'badge-danger']
                        ];
                        $st = $d['status'] ?? null;
                        if ($st && isset($statusLabels[$st])): ?>
                            <span class="badge <?= $statusLabels[$st]['class'] ?>" style="margin-left: auto;">
                                <?= $statusLabels[$st]['label'] ?>
                            </span>
                        <?php else: ?>
                            <span class="badge badge-secondary" style="margin-left: auto;">Pas de profil</span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($d['license_number'])): ?>
                    <div style="background: #f8fafc; border-radius: 6px; padding: 12px; font-size: 0.9rem; margin-bottom: 15px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                            <div>
                                <span style="color: #64748b; font-size: 0.8rem;">N° Permis</span><br>
                                <strong><?= e($d['license_number']) ?></strong>
                            </div>
                            <div>
                                <span style="color: #64748b; font-size: 0.8rem;">Catégorie</span><br>
                                <strong><?= e($d['license_type']) ?></strong>
                            </div>
                            <div>
                                <span style="color: #64748b; font-size: 0.8rem;">Expiration Permis</span><br>
                                <strong style="<?= strtotime($d['license_expiry']) < time() ? 'color:#dc2626' : '' ?>">
                                    <?= e(date('d/m/Y', strtotime($d['license_expiry']))) ?>
                                    <?= strtotime($d['license_expiry']) < time() ? ' ⚠️ EXPIRÉ' : '' ?>
                                </strong>
                            </div>
                            <div>
                                <span style="color: #64748b; font-size: 0.8rem;">Heures travaillées</span><br>
                                <strong><?= number_format((float)$d['total_hours'], 1) ?> h</strong>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px;">
                            <div>
                                <span style="color: #64748b; font-size: 0.8rem;">Primes</span><br>
                                <strong style="color: #10b981;"><?= number_format((float)$d['bonuses'], 2, ',', ' ') ?> €</strong>
                            </div>
                            <div>
                                <span style="color: #64748b; font-size: 0.8rem;">Sanctions</span><br>
                                <strong style="color: #dc2626;"><?= number_format((float)$d['penalties'], 2, ',', ' ') ?> €</strong>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 15px; color: #94a3b8; font-size: 0.9rem; background: #f8fafc; border-radius: 6px; margin-bottom: 15px;">
                            Profil RH non encore complété.
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                        <!-- Mise à jour statut rapide -->
                        <form method="post" action="<?= e(url('/drivers/' . $d['user_id'] . '/status')) ?>" style="flex: 1;">
                            <?= csrf_field() ?>
                            <select name="status" class="form-control" style="padding: 4px 6px;" onchange="this.form.submit()">
                                <option value="available" <?= ($d['status'] ?? '') === 'available' ? 'selected' : '' ?>>Disponible</option>
                                <option value="on_leave" <?= ($d['status'] ?? '') === 'on_leave' ? 'selected' : '' ?>>En congé</option>
                                <option value="sick" <?= ($d['status'] ?? '') === 'sick' ? 'selected' : '' ?>>Arrêt maladie</option>
                                <option value="suspended" <?= ($d['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspendu</option>
                            </select>
                        </form>
                        <button class="btn btn-sm btn-outline"
                            onclick="fillDriverModal(<?= (int)$d['user_id'] ?>, '<?= addslashes(e($d['name'])) ?>', '<?= addslashes(e($d['license_number'] ?? '')) ?>', '<?= addslashes(e($d['license_type'] ?? 'B')) ?>', '<?= e($d['license_expiry'] ?? '') ?>', <?= (float)($d['total_hours'] ?? 0) ?>, <?= (float)($d['bonuses'] ?? 0) ?>, <?= (float)($d['penalties'] ?? 0) ?>)">
                            ✏️ Modifier
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Saisie Profil Chauffeur -->
<div id="modal-driver" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div style="background: #fff; width: 600px; margin: 80px auto; border-radius: 8px; padding: 25px;">
        <h3 style="margin-top: 0;">Profil Chauffeur (Permis & RH)</h3>
        <form method="post" action="<?= e(url('/drivers')) ?>" id="driver-form">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" id="driver-user-id">

            <div class="form-group" style="margin-bottom: 15px;">
                <label>Chauffeur</label>
                <select name="user_id" id="driver-user-select" class="form-control" required>
                    <option value="">Sélectionnez un chauffeur</option>
                    <?php foreach ($drivers as $d): ?>
                        <option value="<?= e((string)$d['user_id']) ?>"><?= e($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label>N° Permis de conduire</label>
                    <input type="text" name="license_number" id="inp-license-number" class="form-control" required placeholder="12AB34567">
                </div>
                <div class="form-group">
                    <label>Catégorie permis</label>
                    <select name="license_type" id="inp-license-type" class="form-control" required>
                        <option value="B">B — Véhicule léger</option>
                        <option value="C">C — Poids lourds</option>
                        <option value="CE">CE — Semi-remorque</option>
                        <option value="D">D — Transport de personnes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date d'expiration du permis</label>
                    <input type="date" name="license_expiry" id="inp-license-expiry" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Total heures travaillées</label>
                    <input type="number" step="0.5" name="total_hours" id="inp-hours" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label>Primes (€)</label>
                    <input type="number" step="0.01" name="bonuses" id="inp-bonuses" class="form-control" value="0">
                </div>
                <div class="form-group">
                    <label>Sanctions/Retenues (€)</label>
                    <input type="number" step="0.01" name="penalties" id="inp-penalties" class="form-control" value="0">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label>Statut</label>
                <select name="status" class="form-control">
                    <option value="available">Disponible</option>
                    <option value="on_leave">En congé</option>
                    <option value="sick">Arrêt maladie</option>
                    <option value="suspended">Suspendu</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-driver').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer le profil</button>
            </div>
        </form>
    </div>
</div>

<script>
function fillDriverModal(uid, name, licNum, licType, licExp, hours, bonuses, penalties) {
    document.getElementById('driver-user-id').value = uid;
    document.getElementById('driver-user-select').value = uid;
    document.getElementById('inp-license-number').value = licNum;
    document.getElementById('inp-license-type').value = licType;
    document.getElementById('inp-license-expiry').value = licExp;
    document.getElementById('inp-hours').value = hours;
    document.getElementById('inp-bonuses').value = bonuses;
    document.getElementById('inp-penalties').value = penalties;
    document.getElementById('modal-driver').style.display = 'block';
}
// When selecting from modal dropdown, sync hidden input
document.getElementById('driver-user-select')?.addEventListener('change', function() {
    document.getElementById('driver-user-id').value = this.value;
});
</script>
