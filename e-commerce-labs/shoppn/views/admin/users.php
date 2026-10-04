<?php
require_once __DIR__ . '/../../core/core.php';
require_admin();
require_once __DIR__ . '/../../controllers/CustomerController.php';

$search = isset($_GET['search']) && is_string($_GET['search']) ? trim($_GET['search']) : '';
$roleInput = isset($_GET['role']) && is_scalar($_GET['role']) ? (string) $_GET['role'] : '0';
$statusInput = isset($_GET['status']) && is_scalar($_GET['status']) ? (string) $_GET['status'] : '-1';
$roleFilter = in_array($roleInput, ['0', '1', '2'], true) ? $roleInput : '0';
$statusFilter = in_array($statusInput, ['-1', '0', '1'], true) ? $statusInput : '-1';
$controller = new CustomerController();
$customers = $controller->getAllCustomers($search, $roleFilter, $statusFilter);

$viewUser = null;
$viewId = filter_input(INPUT_GET, 'view_id', FILTER_VALIDATE_INT);
if ($viewId !== null && $viewId !== false && $viewId > 0) {
    $viewUser = $controller->getCustomerById($viewId);
}

$csrfToken = csrf_token();
$filterQuery = http_build_query([
    'search' => $search,
    'role' => $roleFilter,
    'status' => $statusFilter,
]);

include __DIR__ . '/../layout/header.php';
?>

<main>
    <div class="page-header">
        <div>
            <p class="eyebrow">Administration</p>
            <h2>Manage Users</h2>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <p class="flash-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <?php if ($viewUser): ?>
        <section class="user-detail-panel">
            <div class="user-detail-heading">
                <div>
                    <p class="eyebrow">Read-only profile</p>
                    <h3><?php echo htmlspecialchars($viewUser['customer_name']); ?></h3>
                </div>
                <a class="btn btn-secondary" href="users.php?<?php echo htmlspecialchars($filterQuery); ?>">Close</a>
            </div>
            <dl class="user-detail-grid">
                <div><dt>Email</dt><dd><?php echo htmlspecialchars($viewUser['customer_email']); ?></dd></div>
                <div><dt>Country</dt><dd><?php echo htmlspecialchars($viewUser['customer_country']); ?></dd></div>
                <div><dt>City</dt><dd><?php echo htmlspecialchars($viewUser['customer_city']); ?></dd></div>
                <div><dt>Contact</dt><dd><?php echo htmlspecialchars($viewUser['customer_contact']); ?></dd></div>
                <div><dt>Role</dt><dd><?php echo (int) $viewUser['user_role'] === 1 ? 'Administrator' : 'Customer'; ?></dd></div>
                <div><dt>Account status</dt><dd><?php echo (int) $viewUser['is_active'] === 1 ? 'Active' : 'Deactivated'; ?></dd></div>
            </dl>
        </section>
    <?php endif; ?>

    <section class="table-panel">
        <div class="table-header">
            <div>
                <h3>Users</h3>
                <p class="table-subtitle">View accounts, filter the list, and manage access.</p>
            </div>
        </div>

        <form class="user-filter-form" method="get" action="users.php">
            <div class="filter-search">
                <label for="user-search">Search users</label>
                <input id="user-search" type="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name or email">
            </div>
            <div>
                <label for="user-role-filter">Role</label>
                <select id="user-role-filter" name="role">
                    <option value="0" <?php echo (string) $roleFilter === '0' ? 'selected' : ''; ?>>All roles</option>
                    <option value="2" <?php echo (string) $roleFilter === '2' ? 'selected' : ''; ?>>Customer</option>
                    <option value="1" <?php echo (string) $roleFilter === '1' ? 'selected' : ''; ?>>Administrator</option>
                </select>
            </div>
            <div>
                <label for="user-status-filter">Status</label>
                <select id="user-status-filter" name="status">
                    <option value="-1" <?php echo (string) $statusFilter === '-1' ? 'selected' : ''; ?>>All statuses</option>
                    <option value="1" <?php echo (string) $statusFilter === '1' ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo (string) $statusFilter === '0' ? 'selected' : ''; ?>>Deactivated</option>
                </select>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary" type="submit">Apply filters</button>
                <a class="btn btn-secondary" href="users.php">Reset</a>
            </div>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Location</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Manage access</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $user): ?>
                            <?php
                            $userId = (int) $user['customer_id'];
                            $isSelf = $userId === (int) ($_SESSION['customer_id'] ?? 0);
                            $isActive = (int) $user['is_active'] === 1;
                            $isAdmin = (int) $user['user_role'] === 1;
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['customer_name']); ?></strong>
                                    <span class="user-email"><?php echo htmlspecialchars($user['customer_email']); ?></span>
                                    <span class="user-id">ID #<?php echo $userId; ?></span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($user['customer_city']); ?><br>
                                    <span class="user-email"><?php echo htmlspecialchars($user['customer_country']); ?></span>
                                </td>
                                <td>
                                    <span class="role-badge <?php echo $isAdmin ? 'admin' : 'customer'; ?>">
                                        <?php echo $isAdmin ? 'Admin' : 'Customer'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $isActive ? 'active' : 'inactive'; ?>">
                                        <?php echo $isActive ? 'Active' : 'Deactivated'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!$isSelf): ?>
                                        <div class="user-manage-controls">
                                            <form method="post" action="/e-commerce-labs/shoppn/actions/manage_user_action.php">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                <input type="hidden" name="customer_id" value="<?php echo $userId; ?>">
                                                <input type="hidden" name="action" value="role">
                                                <input type="hidden" name="role" value="<?php echo $isAdmin ? 2 : 1; ?>">
                                                <input type="hidden" name="return_query" value="<?php echo htmlspecialchars($filterQuery); ?>">
                                                <button class="btn btn-secondary btn-compact" type="submit">
                                                    Make <?php echo $isAdmin ? 'customer' : 'admin'; ?>
                                                </button>
                                            </form>
                                            <form method="post" action="/e-commerce-labs/shoppn/actions/manage_user_action.php">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                <input type="hidden" name="customer_id" value="<?php echo $userId; ?>">
                                                <input type="hidden" name="action" value="status">
                                                <input type="hidden" name="active" value="<?php echo $isActive ? 0 : 1; ?>">
                                                <input type="hidden" name="return_query" value="<?php echo htmlspecialchars($filterQuery); ?>">
                                                <button class="btn <?php echo $isActive ? 'btn-secondary' : 'btn-primary'; ?> btn-compact" type="submit">
                                                    <?php echo $isActive ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="user-email">Current account</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a class="btn btn-secondary btn-compact" href="users.php?<?php echo htmlspecialchars($filterQuery); ?>&amp;view_id=<?php echo $userId; ?>">View</a>
                                        <?php if (!$isSelf): ?>
                                            <form method="post" action="/e-commerce-labs/shoppn/actions/manage_user_action.php" onsubmit="return confirm('Permanently delete this user and related account data?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                <input type="hidden" name="customer_id" value="<?php echo $userId; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="return_query" value="<?php echo htmlspecialchars($filterQuery); ?>">
                                                <button class="btn btn-danger btn-compact" type="submit">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No users match these filters.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../layout/footer.php'; ?>
