<?php
require_once __DIR__ . '/../../core/core.php';
require_admin();
require_once __DIR__ . '/../../controllers/ProductController.php';

$controller = new ProductController();
$brands = $controller->getAllBrands();

$edit_brand = null;
$edit_id = isset($_GET['edit_id']) ? (int) $_GET['edit_id'] : 0;
if ($edit_id > 0) {
    $edit_brand = $controller->getBrandById($edit_id);
}

include __DIR__ . '/../layout/header.php';
?>

<main>
    <h2>Manage Brands</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <p class="flash-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form class="auth-form" action="<?php echo $edit_brand ? '/e-commerce-labs/shoppn/actions/update_brand_action.php' : '/e-commerce-labs/shoppn/actions/add_brand_action.php'; ?>" method="post">
        <?php if ($edit_brand): ?>
            <input type="hidden" name="brand_id" value="<?php echo (int) $edit_brand['brand_id']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="brand_name">Brand Name</label>
            <input type="text" id="brand_name" name="brand_name" placeholder="Enter brand name" value="<?php echo $edit_brand ? htmlspecialchars($edit_brand['brand_name']) : ''; ?>" required>
        </div>

        <button type="submit"><?php echo $edit_brand ? 'Update Brand' : 'Add Brand'; ?></button>
    </form>

    <h3>Brands</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Brand Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($brands)): ?>
                <?php foreach ($brands as $brand): ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string) $brand['brand_id']); ?></td>
                        <td><?php echo htmlspecialchars($brand['brand_name']); ?></td>
                        <td>
                            <a href="brand.php?edit_id=<?php echo (int) $brand['brand_id']; ?>" class="btn btn-secondary">Edit</a>
                            <a href="/e-commerce-labs/shoppn/actions/delete_brand_action.php?brand_id=<?php echo (int) $brand['brand_id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this brand?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No brands found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<?php include __DIR__ . '/../layout/footer.php'; ?>
