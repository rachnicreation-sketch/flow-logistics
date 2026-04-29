<section class="page-header">
    <div class="header-left">
        <h2>Catégories de Produits</h2>
        <p>Gérez les types de produits pour une meilleure organisation du catalogue.</p>
    </div>
    <div class="header-right">
        <button class="btn btn-primary" onclick="document.getElementById('newCategoryPanel').classList.toggle('hidden')">
            <i class="fa-solid fa-plus"></i> Nouvelle Catégorie
        </button>
    </div>
</section>

<section id="newCategoryPanel" class="panel panel-pad hidden mb-6">
    <h3>Ajouter une nouvelle catégorie</h3>
    <form method="post" action="<?= e(url('/categories')) ?>" class="grid-form-2">
        <?= csrf_field() ?>
        <label>Nom de la catégorie<input type="text" name="name" required placeholder="Ex: Électronique"></label>
        <label>Description<input type="text" name="description" placeholder="Courte description..."></label>
        <div class="full-width">
            <button class="btn btn-primary" type="submit">Enregistrer la catégorie</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-header">
        <h3>Liste des catégories</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nom</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td><strong><?= e($c['name']) ?></strong></td>
                    <td><?= e($c['description'] ?? '-') ?></td>
                    <td class="action-row">
                        <button class="btn btn-outline btn-sm" onclick="editCategory(<?= $c['id'] ?>, '<?= e(addslashes($c['name'])) ?>', '<?= e(addslashes($c['description'] ?? '')) ?>')">
                            <i class="fa-solid fa-pen-to-square"></i> Modifier
                        </button>
                        <form method="post" action="<?= e(url('/categories/delete/' . $c['id'])) ?>" style="display:inline;" onsubmit="return confirm('Supprimer cette catégorie ?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline btn-sm btn-danger-text" type="submit"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Modal simple pour l'édition -->
<div id="editCategoryModal" class="modal hidden">
    <div class="modal-content panel panel-pad">
        <h3>Modifier la catégorie</h3>
        <form id="editCategoryForm" method="post" class="grid-form-1">
            <?= csrf_field() ?>
            <label>Nom<input type="text" name="name" id="edit_name" required></label>
            <label>Description<textarea name="description" id="edit_description"></textarea></label>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('editCategoryModal').classList.add('hidden')">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(id, name, description) {
    const modal = document.getElementById('editCategoryModal');
    const form = document.getElementById('editCategoryForm');
    form.action = '<?= e(url('/categories/update/')) ?>' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description;
    modal.classList.remove('hidden');
}
</script>
