<?php
// Hubungkan ke file database menggunakan path yang mundur satu folder (karena file ini di dalam public/)
require_once '../config/db.php';

// Inisialisasi variabel untuk menampung error dan nilai input agar form tidak kosong jika terjadi error
$errors = [];
$name = '';
$category = 'Umum';
$price = '';
$stock = '';

// Proses dijalankan HANYA JIKA form disubmit (metode POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // 1. Ambil input dan normalisasi (hilangkan spasi berlebih)
    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "Umum");
    
    // 2. Filter input angka sesuai saran keamanan materi
    $price = filter_input(INPUT_POST, "price", FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, "stock", FILTER_VALIDATE_INT);

    // 3. Validasi aturan bisnis dari dosen (nama >= 3, harga > 0, stok >= 0)
    if (mb_strlen($name) < 3) {
        $errors["name"] = "Nama minimal 3 karakter.";
    }
    if ($price === false || $price <= 0) {
        $errors["price"] = "Harga harus > 0.";
    }
    if ($stock === false || $stock < 0) {
        $errors["stock"] = "Stok tidak boleh negatif.";
    }

    // 4. Jika lolos validasi (array errors kosong), eksekusi query
    if (empty($errors)) {
        try {
            // Gunakan prepared statement untuk keamanan dari SQL Injection
            $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock) VALUES (:name, :category, :price, :stock)");
            $stmt->execute([
                "name" => $name,
                "category" => $category,
                "price" => $price,
                "stock" => $stock
            ]);

            // 5. Pola PRG (Post-Redirect-Get) untuk mencegah data ganda saat refresh
            header("Location: index.php?status=created");
            exit; // Hentikan script setelah redirect
            
        } catch (PDOException $e) {
            // Tangkap error jika nama produk sudah ada di database (karena syarat UNIQUE di tabel)
            if ($e->getCode() == 23000) {
                $errors["name"] = "Nama produk unik sudah dipakai. Silakan pilih nama lain.";
            } else {
                $errors["db"] = "Terjadi kesalahan sistem: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk</title>
    <!-- CSS akan kita kerjakan di tahap akhir, sekarang kita panggil dulu -->
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="card" style="max-width: 400px; margin: 20px auto; padding: 20px; font-family: sans-serif;">
        <h2>Tambah Produk Baru</h2>

        <?php if (isset($errors["db"])): ?>
            <p style="color: red;"><?= $errors["db"] ?></p>
        <?php endif; ?>

        <!-- Form HTML menggunakan POST untuk mengubah/menambah data -->
        <form action="create.php" method="POST">
            
            <div style="margin-bottom: 15px;">
                <label for="name">Nama Produk</label><br>
                <!-- htmlspecialchars() menjaga nilai input jika validasi gagal, sekaligus mencegah XSS -->
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>" minlength="3" required style="width: 100%; padding: 8px;">
                <?php if (isset($errors["name"])) echo '<div style="color: red; font-size: 12px; mt-1;">' . $errors["name"] . '</div>'; ?>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="category">Kategori</label><br>
                <input type="text" id="category" name="category" value="<?= htmlspecialchars($category, ENT_QUOTES, "UTF-8") ?>" required style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label for="price">Harga</label><br>
                <input type="number" id="price" name="price" min="1" value="<?= htmlspecialchars($price, ENT_QUOTES, "UTF-8") ?>" required style="width: 100%; padding: 8px;">
                <?php if (isset($errors["price"])) echo '<div style="color: red; font-size: 12px;">' . $errors["price"] . '</div>'; ?>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="stock">Stok</label><br>
                <input type="number" id="stock" name="stock" min="0" value="<?= htmlspecialchars($stock, ENT_QUOTES, "UTF-8") ?>" required style="width: 100%; padding: 8px;">
                <?php if (isset($errors["stock"])) echo '<div style="color: red; font-size: 12px;">' . $errors["stock"] . '</div>'; ?>
            </div>

            <button type="submit" style="padding: 10px 15px; background-color: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer;">Simpan Produk</button>
            <a href="index.php" style="margin-left: 10px; text-decoration: none; color: #64748b;">Batal</a>
        </form>
    </div>
</body>
</html>