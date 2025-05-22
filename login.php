<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email)) $errors[] = "Email is required";
    if (empty($password)) $errors[] = "Password is required";

    if (empty($errors)) {
        try {
            // Debug: Check if connection is working
            if (!$conn) {
                throw new Exception("Database connection not established");
            }

            $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }

            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Start session and store user data
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                // Redirect to homepage
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Invalid email or password";
                // Debug: Add more specific error message
                if (!$user) {
                    error_log("No user found with email: " . $email);
                } else {
                    error_log("Password verification failed for email: " . $email);
                }
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $errors[] = "Login failed. Please try again. Error: " . $e->getMessage();
        }
    }
}
?>

<!-- Rest of your HTML remains the same -->

<div class="auth-container">
    <div class="auth-form">
        <h2>Login</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="auth-button">Login</button>
        </form>

        <div class="auth-links">
            <a href="forgot_password.php">Forgot Password?</a>
            <span class="separator">|</span>
            <a href="register.php">Create Account</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 