<?php
include '../config.php';

// Cek apakah user sudah login dan role-nya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
// Cek apakah user sudah login dan role-nya admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$message_type = '';

// Handle tambah/edit mobil
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $merek = $_POST['merek'];
    $model = $_POST['model'];
    $tahun = $_POST['tahun'];
    $transmisi = $_POST['transmisi'];
    $jumlah_kursi = $_POST['jumlah_kursi'];
    $harga_per_hari = $_POST['harga_per_hari'];
    $deskripsi = $_POST['deskripsi'];
    $gambar_url = $_POST['gambar_url'];
    $status_ketersediaan = $_POST['status_ketersediaan'];
    $car_id = $_POST['car_id'] ?? null; // Untuk edit

    if (empty($merek) || empty($model) || empty($tahun) || empty($transmisi) || empty($jumlah_kursi) || empty($harga_per_hari)) {
        $message = "Semua kolom wajib (kecuali deskripsi dan gambar) harus diisi.";
        $message_type = "alert";
    } else {
        if ($car_id) { // Mode Edit
            $stmt = $conn->prepare("UPDATE mobil SET merek=?, model=?, tahun=?, transmisi=?, jumlah_kursi=?, harga_per_hari=?, deskripsi=?, gambar_url=?, status_ketersediaan=? WHERE id=?");
            // Ubah dari "ssisdsssi" menjadi "ssisidsssi"
            $stmt->bind_param("ssisidsssi", $merek, $model, $tahun, $transmisi, $jumlah_kursi, $harga_per_hari, $deskripsi, $gambar_url, $status_ketersediaan, $car_id);
            if ($stmt->execute()) {
                $message = "Mobil berhasil diperbarui.";
                $message_type = "success";
            } else {
                $message = "Error: " . $stmt->error;
                $message_type = "alert";
            }
        } else { // Mode Tambah Baru
            $stmt = $conn->prepare("INSERT INTO mobil (merek, model, tahun, transmisi, jumlah_kursi, harga_per_hari, deskripsi, gambar_url, status_ketersediaan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            // Ubah dari "ssisdsss" menjadi "ssisidsss"
            $stmt->bind_param("ssisidsss", $merek, $model, $tahun, $transmisi, $jumlah_kursi, $harga_per_hari, $deskripsi, $gambar_url, $status_ketersediaan);
            if ($stmt->execute()) {
                $message = "Mobil berhasil ditambahkan.";
                $message_type = "success";
            } else {
                $message = "Error: " . $stmt->error;
                $message_type = "alert";
        }
        }
        $stmt->close();
    }
}

// Handle hapus mobil
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $car_id_to_delete = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM mobil WHERE id=?");
    $stmt->bind_param("i", $car_id_to_delete);
    if ($stmt->execute()) {
        $message = "Mobil berhasil dihapus.";
        $message_type = "success";
    } else {
        $message = "Error saat menghapus mobil: " . $stmt->error . ". Pastikan tidak ada pemesanan terkait mobil ini.";
        $message_type = "alert";
    }
    $stmt->close();
    // Redirect untuk menghindari resubmit form
    header("Location: manage_cars.php?msg=" . urlencode($message) . "&type=" . urlencode($message_type));
    exit;
}

// Ambil pesan dari URL setelah redirect
if (isset($_GET['msg']) && isset($_GET['type'])) {
    $message = urldecode($_GET['msg']);
    $message_type = urldecode($_GET['type']);
}

// Ambil data mobil yang ada
$sql = "SELECT * FROM mobil ORDER BY merek, model";
$result = $conn->query($sql);
$cars = [];
while ($row = $result->fetch_assoc()) {
    $cars[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Mobil - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1>Admin Panel</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="../index.php">Beranda Publik</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage_cars.php">Manajemen Mobil</a></li>
                    <li><a href="manage_bookings.php">Manajemen Pemesanan</a></li>
                    <li><a href="../logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2>Manajemen Mobil</h2>

        <?php if (!empty($message)): ?>
            <div class="<?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Tambah / Edit Mobil</h3>
            <?php
            $edit_car = null;
            if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
                $car_id_to_edit = $_GET['id'];
                $stmt_edit = $conn->prepare("SELECT * FROM mobil WHERE id = ?");
                $stmt_edit->bind_param("i", $car_id_to_edit);
                $stmt_edit->execute();
                $result_edit = $stmt_edit->get_result();
                if ($result_edit->num_rows > 0) {
                    $edit_car = $result_edit->fetch_assoc();
                }
                $stmt_edit->close();
            }
            ?>
            <form action="manage_cars.php" method="POST">
                <input type="hidden" name="car_id" value="<?php echo htmlspecialchars($edit_car['id'] ?? ''); ?>">
                <div class="form-group">
                    <label for="merek">Merek:</label>
                    <input type="text" id="merek" name="merek" value="<?php echo htmlspecialchars($edit_car['merek'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="model">Model:</label>
                    <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($edit_car['model'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="tahun">Tahun:</label>
                    <input type="number" id="tahun" name="tahun" value="<?php echo htmlspecialchars($edit_car['tahun'] ?? ''); ?>" required min="1900" max="<?php echo date('Y') + 1; ?>">
                </div>
                <div class="form-group">
                    <label for="transmisi">Transmisi:</label>
                    <select id="transmisi" name="transmisi" required>
                        <option value="Manual" <?php echo (isset($edit_car['transmisi']) && $edit_car['transmisi'] == 'Manual') ? 'selected' : ''; ?>>Manual</option>
                        <option value="Otomatis" <?php echo (isset($edit_car['transmisi']) && $edit_car['transmisi'] == 'Otomatis') ? 'selected' : ''; ?>>Otomatis</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="jumlah_kursi">Jumlah Kursi:</label>
                    <input type="number" id="jumlah_kursi" name="jumlah_kursi" value="<?php echo htmlspecialchars($edit_car['jumlah_kursi'] ?? ''); ?>" required min="1">
                </div>
                <div class="form-group">
                    <label for="harga_per_hari">Harga per Hari (Rp):</label>
                    <input type="number" id="harga_per_hari" name="harga_per_hari" value="<?php echo htmlspecialchars($edit_car['harga_per_hari'] ?? ''); ?>" step="0.01" required min="0">
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi:</label>
                    <textarea id="deskripsi" name="deskripsi"><?php echo htmlspecialchars($edit_car['deskripsi'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="gambar_url">URL Gambar:</label>
                    <input type="text" id="gambar_url" name="gambar_url" value="<?php echo htmlspecialchars($edit_car['gambar_url'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="status_ketersediaan">Status Ketersediaan:</label>
                    <select id="status_ketersediaan" name="status_ketersediaan" required>
                        <option value="Tersedia" <?php echo (isset($edit_car['status_ketersediaan']) && $edit_car['status_ketersediaan'] == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                        <option value="Tidak Tersedia" <?php echo (isset($edit_car['status_ketersediaan']) && $edit_car['status_ketersediaan'] == 'Tidak Tersedia') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                        <option value="Dalam Perbaikan" <?php echo (isset($edit_car['status_ketersediaan']) && $edit_car['status_ketersediaan'] == 'Dalam Perbaikan') ? 'selected' : ''; ?>>Dalam Perbaikan</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo $edit_car ? 'Update Mobil' : 'Tambah Mobil'; ?></button>
                <?php if ($edit_car): ?>
                    <a href="manage_cars.php" class="btn btn-danger">Batal Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <h3>Daftar Mobil</h3>
        <?php if (empty($cars)): ?>
            <p>Belum ada mobil yang terdaftar.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Merek</th>
                        <th>Model</th>
                        <th>Tahun</th>
                        <th>Harga/Hari</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cars as $car): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($car['id']); ?></td>
                            <td><?php echo htmlspecialchars($car['merek']); ?></td>
                            <td><?php echo htmlspecialchars($car['model']); ?></td>
                            <td><?php echo htmlspecialchars($car['tahun']); ?></td>
                            <td>Rp <?php echo number_format($car['harga_per_hari'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($car['status_ketersediaan']); ?></td>
                            <td>
                                <a href="manage_cars.php?action=edit&id=<?php echo htmlspecialchars($car['id']); ?>" class="btn">Edit</a>
                                <a href="manage_cars.php?action=delete&id=<?php echo htmlspecialchars($car['id']); ?>" class="btn btn-danger" onclick="return confirm('Anda yakin ingin menghapus mobil ini? Ini juga akan menghapus semua pemesanan terkait.');">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Rental Mobil. All rights reserved.</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>