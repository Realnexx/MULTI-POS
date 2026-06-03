<?php
require_once __DIR__ . '/../../models/Product.php';
$categories = $categories ?? Product::get_all_categories() ?: [];

$title = "Product Categories Management";
ob_start();
?>

<!-- Header & Action -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold outfit-font text-dark mb-1">Product Categories</h4>
        <p class="text-muted small mb-0">Organize menu items into distinct categories for seamless POS filtering and navigation.</p>
    </div>
    <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
        <i class="fa-solid fa-folder-plus"></i> Add New Category
    </button>
</div>

<!-- Categories Table -->
<div class="card border-0 rounded-4 shadow-sm p-4 bg-white border border-light">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase fs-7 text-muted fw-bold outfit-font">
                <tr>
                    <th class="py-3 px-4 rounded-start-3">ID</th>
                    <th class="py-3">Category Name</th>
                    <th class="py-3">Description</th>
                    <th class="py-3 text-center">Active Products</th>
                    <th class="py-3 px-4 rounded-end-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No categories found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td class="py-3 px-4 fw-bold text-dark">#<?= $cat['id'] ?></td>
                            <td class="py-3 fw-bold text-dark outfit-font">
                                <span class="cat-icon-wrapper bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center me-2 shadow-inner" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                    <i class="fa-solid fa-folder text-primary"></i>
                                </span>
                                <?= htmlspecialchars($cat['name']) ?>
                            </td>
                            <td class="py-3 text-muted small"><?= htmlspecialchars($cat['description']) ?></td>
                            <td class="py-3 text-center">
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold fs-7"><?= intval($cat['product_count']) ?> Items</span>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-medium me-1 edit-category-btn" 
                                        data-id="<?= $cat['id'] ?>" 
                                        data-name="<?= htmlspecialchars($cat['name']) ?>" 
                                        data-description="<?= htmlspecialchars($cat['description']) ?>" 
                                        data-bs-toggle="modal" data-bs-target="#editCategoryModal">
                                    Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-medium delete-category-btn" 
                                        data-id="<?= $cat['id'] ?>" 
                                        data-name="<?= htmlspecialchars($cat['name']) ?>" 
                                        data-count="<?= intval($cat['product_count']) ?>"
                                        data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary-subtle border-0 p-4">
                <h5 class="modal-title fw-bold outfit-font text-dark">Add New Category</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('?route=inventory/categories') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="create">
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium small text-dark">Category Name</label>
                        <input type="text" name="name" class="form-control rounded-pill px-3 py-2 shadow-xs" required placeholder="Shaved Ice / Beverages">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-medium small text-dark">Description</label>
                        <textarea name="description" class="form-control rounded-4 p-3 shadow-xs" rows="3" placeholder="Chilled traditional desserts..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-4 justify-content-between">
                    <button type="button" class="btn btn-outline-light-gray text-dark rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary-subtle border-0 p-4">
                <h5 class="modal-title fw-bold outfit-font text-dark">Edit Category Details</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('?route=inventory/categories') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="category_id" id="edit_cat_id">
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium small text-dark">Category Name</label>
                        <input type="text" name="name" id="edit_cat_name" class="form-control rounded-pill px-3 py-2 shadow-xs" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-medium small text-dark">Description</label>
                        <textarea name="description" id="edit_cat_desc" class="form-control rounded-4 p-3 shadow-xs" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-4 justify-content-between">
                    <button type="button" class="btn btn-outline-light-gray text-dark rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-danger-subtle border-0 p-4">
                <h5 class="modal-title fw-bold outfit-font text-danger">Confirm Deletion</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('?route=inventory/categories') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="category_id" id="delete_cat_id">
                
                <div class="modal-body p-4 text-center">
                    <i class="fa-solid fa-triangle-exclamation fs-1 text-danger mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark mb-2">Delete <span id="delete_cat_name"></span>?</h5>
                    <p class="text-muted small mb-0" id="delete_cat_warning">This action cannot be undone. Any products currently assigned to this category will be marked as Uncategorized.</p>
                </div>
                <div class="modal-footer bg-light border-0 p-4 justify-content-between">
                    <button type="button" class="btn btn-outline-light-gray text-dark rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm" id="confirmDeleteCatBtn">Delete Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $(".edit-category-btn").click(function() {
        $("#edit_cat_id").val($(this).data('id'));
        $("#edit_cat_name").val($(this).data('name'));
        $("#edit_cat_desc").val($(this).data('description'));
    });

    $(".delete-category-btn").click(function() {
        const count = parseInt($(this).data('count'));
        $("#delete_cat_id").val($(this).data('id'));
        $("#delete_cat_name").text($(this).data('name'));

        if (count > 0) {
            $("#delete_cat_warning").html(`<strong>Warning:</strong> There are currently <strong>${count} products</strong> in this category. Deleting it will set them to Uncategorized.`);
        } else {
            $("#delete_cat_warning").text("This category has no active products. It is safe to delete.");
        }
    });
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
