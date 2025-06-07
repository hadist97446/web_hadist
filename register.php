<?php
include 'config.php';

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $nomor_telepon = $_POST['nomor_telepon'];
    $alamat = $_POST['alamat'];

    // Validasi input
    if (empty($nama_lengkap) || empty($email) || empty($password)) {
        $message = "Nama lengkap, email, dan password harus diisi.";
        $message_type = "alert";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid.";
        $message_type = "alert";
    } else {
        // Cek apakah email sudah terdaftar
        $stmt_check = $conn->prepare("SELECT id FROM pengguna WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = "Email ini sudah terdaftar. Silakan gunakan email lain atau login.";
            $message_type = "alert";
        } else {
            // Hash password sebelum disimpan ke database
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt_insert = $conn->prepare("INSERT INTO pengguna (nama_lengkap, email, password_hash, nomor_telepon, alamat) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssss", $nama_lengkap, $email, $password_hash, $nomor_telepon, $alamat);

            if ($stmt_insert->execute()) {
                $message = "Pendaftaran berhasil! Anda sekarang bisa <a href='login.php'>login</a>.";
                $message_type = "success";
            } else {
                $message = "Error saat pendaftaran: " . $stmt_insert->error;
                $message_type = "alert";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru</title>
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
            <h2>Daftar Akun Baru</h2>
            <?php if (!empty($message)): ?>
                <div class="<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap:</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="nomor_telepon">Nomor Telepon:</label>
                    <input type="text" id="nomor_telepon" name="nomor_telepon">
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat:</label>
                    <textarea id="alamat" name="alamat"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Daftar</button>
            </form>
            <p>Sudah punya akun? <a href="login.php">Login di sini</a>.</p>
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