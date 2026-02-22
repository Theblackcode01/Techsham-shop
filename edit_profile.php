<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$user = getUserById($pdo, $user_id);

$flash = getFlashMessage();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (empty($full_name)) {
        $errors[] = 'Full name is required.';
    }

    if (!empty($password) || !empty($password_confirm)) {
        if ($password !== $password_confirm) {
            $errors[] = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
    }

    if (empty($errors)) {
        $updated = updateUserProfile($pdo, $user_id, $full_name, $email, $password);
        if ($updated) {
            // Refresh session full name
            $_SESSION['full_name'] = $full_name;
            setFlashMessage('success', 'Profile updated successfully.');
            header('Location: edit_profile.php');
            exit();
        } else {
            $errors[] = 'Failed to update profile. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Phone Shop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container">
            <h2>Edit Profile</h2>

            <?php if ($flash): ?>
                <div class="alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert-error">
                    <?php foreach ($errors as $e) echo '<div>' . $e . '</div>'; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars(isset($user['full_name']) ? $user['full_name'] : ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars(isset($user['email']) ? $user['email'] : ''); ?>">
                </div>

                <div class="form-group">
                    <label>New Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control">
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="password_confirm" class="form-control">
                </div>

                <button type="submit" class="btn">Save Changes</button>
            </form>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>
