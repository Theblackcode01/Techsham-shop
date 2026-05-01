<?php
/**
 * Manage Users Page
 * Admin can add, edit, and delete users
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();
requireAdmin();

$error = '';
$success = '';

// Handle remove user
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    if ($deleteId === $_SESSION['user_id']) {
        setFlashMessage('error', 'You cannot delete your own account while logged in.');
        header('Location: manage_users.php');
        exit();
    }

    if (deleteUserById($pdo, $deleteId)) {
        setFlashMessage('success', 'User deleted successfully.');
    } else {
        setFlashMessage('error', 'Failed to delete user.');
    }
    header('Location: manage_users.php');
    exit();
}

// Handle add/edit user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $username = sanitize($_POST['username']);
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone_number = sanitize($_POST['phone_number']);
    $role = sanitize($_POST['role']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (empty($username) || empty($full_name) || empty($role)) {
        $error = 'Username, full name, and role are required.';
    } elseif (!empty($password) && $password !== $password_confirm) {
        $error = 'Passwords do not match.';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    }

    if (empty($error)) {
        if ($id > 0) {
            $updated = updateUserAccount($pdo, $id, $username, $full_name, $email, $role, $password, $phone_number);
            if ($updated) {
                $success = 'User updated successfully.';
                header('Location: manage_users.php');
                exit();
            } else {
                $error = 'Failed to update user. Please try again.';
            }
        } else {
            $created = createUser($pdo, $username, $full_name, $email, $password, $role, $phone_number);
            if ($created) {
                $success = 'User created successfully.';
                header('Location: manage_users.php');
                exit();
            } else {
                $error = 'Failed to create user. Username may already exist.';
            }
        }
    }
}

// Load user for edit
$editUser = null;
if (isset($_GET['edit'])) {
    $editUser = getUserById($pdo, intval($_GET['edit']));
}

$users = getAllUsers($pdo);
$pageTitle = 'Manage Users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Phone Shop Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="layout">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="top-bar">
                <div>
                    <h1 class="page-title">Manage Users</h1>
                    <div class="breadcrumb">Home / Manage Users</div>
                </div>
            </div>

            <?php
            $flash = getFlashMessage();
            if ($flash):
            ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo $flash['message']; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><?php echo $editUser ? 'Edit User' : 'Add New User'; ?></h2>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?php echo $editUser ? $editUser['id'] : 0; ?>">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" value="<?php echo $editUser ? htmlspecialchars($editUser['username']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo $editUser ? htmlspecialchars($editUser['full_name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $editUser ? htmlspecialchars($editUser['email']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone_number" class="form-control" value="<?php echo $editUser ? htmlspecialchars($editUser['phone_number'] ?? '') : ''; ?>" placeholder="e.g. +255 123 456 789">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Role *</label>
                            <select name="role" class="form-control" required>
                                <option value="user" <?php echo ($editUser && $editUser['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo ($editUser && $editUser['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo $editUser ? 'New Password' : 'Password'; ?> <?php echo $editUser ? '(leave blank to keep current)' : '*'; ?></label>
                            <input type="password" name="password" class="form-control" <?php echo $editUser ? '' : 'required'; ?>>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo $editUser ? 'Confirm New Password' : 'Confirm Password'; ?></label>
                            <input type="password" name="password_confirm" class="form-control" <?php echo $editUser ? '' : 'required'; ?>>
                        </div>
                    </div>
                    <div class="flex gap-1" style="margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary"><?php echo $editUser ? 'Update User' : 'Add User'; ?></button>
                        <?php if ($editUser): ?><a href="manage_users.php" class="btn btn-secondary">Cancel</a><?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">User Accounts (<?php echo count($users); ?>)</h2>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone_number'] ?? ''); ?></td>
                                    <td><span class="badge badge-primary"><?php echo ucfirst($user['role']); ?></span></td>
                                    <td><?php echo formatDateTime($user['created_at']); ?></td>
                                    <td><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?></td>
                                    <td>
                                        <a href="manage_users.php?edit=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <a href="manage_users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?');">Delete</a>
                                        <?php else: ?>
                                            <span class="text-muted">Current</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
