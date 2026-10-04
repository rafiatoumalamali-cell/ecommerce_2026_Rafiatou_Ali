<?php
require_once __DIR__ . '/../../core/core.php';
require_admin();
require_once __DIR__ . '/../../controllers/ProductController.php';

$controller = new ProductController();
$categories = $controller->getAllCategories();

$edit_category = null;
$edit_id = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : 0;
if ($edit_id > 0) {
    $edit_category = $controller->getCategoryById($edit_id);
}

include __DIR__ . '/../layout/header.php';
?>

<main>
    <h2>Manage Categories</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <p class="flash-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form class="auth-form" action="<?php echo $edit_category ? '/e-commerce-labs/shoppn/actions/update_category_action.php' : '/e-commerce-labs/shoppn/actions/add_category_action.php'; ?>" method="post">
        <?php if ($edit_category): ?>
            <input type="hidden" name="cat_id" value="<?php echo (int) $edit_category['cat_id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="cat_name">Category Name</label>
            <input type="text" id="cat_name" name="cat_name" placeholder="Enter category name" value="<?php echo $edit_category ? htmlspecialchars($edit_category['cat_name']) : ''; ?>" required>
        </div>

        <button type="submit"><?php echo $edit_category ? 'Update Category' : 'Add Category'; ?></button>
    </form>

    <h3>Categories</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string) $category['cat_id']); ?></td>
                        <td><?php echo htmlspecialchars($category['cat_name']); ?></td>
                        <td>
                            <a href="category.php?edit_id=<?php echo (int) $category['cat_id']; ?>" class="btn btn-secondary">Edit</a>
                            <a href="/e-commerce-labs/shoppn/actions/delete_category_action.php?cat_id=<?php echo (int) $category['cat_id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this category?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No categories found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<?php include __DIR__ . '/../layout/footer.php'; ?>
