<?php
include 'config.php';

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "Email dan password harus diisi.";
        $message_type = "alert";
    } else {
        // Cari pengguna berdasarkan email
        $stmt = $conn->prepare("SELECT id, nama_lengkap, email, password_hash, role FROM pengguna WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Verifikasi password
            if (password_verify($password, $user['password_hash'])) {
                // Login berhasil, simpan info pengguna ke session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['nama_lengkap'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'] ?? 'user'; // Tambahkan role, default user
                
                // Redirect berdasarkan role
                if ($_SESSION['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $message = "Password salah.";
                $message_type = "alert";
            }
        } else {
            $message = "Email tidak terdaftar.";
            $message_type = "alert";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1>Rental Mobil</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Daftar</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>Login</h2>
            <?php if (!empty($message)): ?>
                <div class="<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a>.</p>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Rental Mobil. All rights reserved.</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>