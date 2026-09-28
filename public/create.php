<?php
require_once '../config/db.php';

$errors = [];
$name = '';
$category = 'Umum';
$price = '';
$stock = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "Umum");
    $price = filter_input(INPUT_POST, "price", FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, "stock", FILTER_VALIDATE_INT);

    if (mb_strlen($name) < 3) $errors["name"] = "Nama minimal 3 karakter.";
    if ($price === false || $price <= 0) $errors["price"] = "Harga harus > 0.";
    if ($stock === false || $stock < 0) $errors["stock"] = "Stok tidak boleh negatif.";

    // --- LOGIKA UPLOAD GAMBAR ---
    $imageName = null; // Default kosong jika tidak ada gambar
    
    // Cek apakah ada file yang diunggah dan tidak ada error pada file
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        
        // Ambil ekstensi file (misal: jpg, png)
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        // Validasi 1: Cek apakah ekstensinya gambar
        if (!in_array($fileExtension, $allowedExtensions)) {
            $errors["image"] = "Format tidak valid. Hanya JPG, PNG, atau WEBP.";
        } 
        // Validasi 2: Cek ukuran maksimal (2 MB)
        elseif ($fileSize > 2 * 1024 * 1024) {
            $errors["image"] = "Ukuran gambar maksimal 2MB.";
        } 
        else {
            // Generate nama file acak agar jika ada gambar dengan nama sama, tidak saling menimpa
            $imageName = uniqid('img_', true) . '.' . $fileExtension;
            $destination = 'uploads/' . $imageName;

            // Pindahkan file dari tempat sementara ke folder uploads/ kita
            // Kita pindahkan HANYA JIKA tidak ada error lain (nama, harga, dll valid)
            if (empty($errors)) {
                if (!move_uploaded_file($tmpName, $destination)) {
                    $errors["image"] = "Gagal menyimpan gambar ke server.";
                    $imageName = null;
                }
            }
        }
    }
    // ----------------------------

    // Jika semua validasi lolos
    if (empty($errors)) {
        try {
            // Perbarui query INSERT dengan tambahan kolom image
            $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock, image) VALUES (:name, :category, :price, :stock, :image)");
            $stmt->execute([
                "name" => $name,
                "category" => $category,
                "price" => $price,
                "stock" => $stock,
                "image" => $imageName
            ]);

            header("Location: index.php?status=created");
            exit;
            
        } catch (PDOException $e) {
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
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            margin: 0; padding: 20px;
            background: linear-gradient(135deg, #e0f2fe 0%, #fce7f3 100%);
            min-height: 100vh;
            display: flex; justify-content: center; align-items: center;
            color: #334155;
        }
        .card-form {
            width: 100%; max-width: 450px; padding: 30px; border-radius: 20px;
            background: rgba(255, 255, 255, 0.85); box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            backdrop-filter: blur(10px); border: 2px solid #ffffff;
        }
        h2 { margin-top: 0; color: #0284c7; text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block; }
        input { 
            width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; 
            box-sizing: border-box; font-family: inherit;
        }
        input:focus { outline: none; border-color: #7dd3fc; box-shadow: 0 0 0 3px rgba(125, 211, 252, 0.3); }
        input[type="file"] { padding: 7px; background: #ffffff; }
        .error-text { color: #ef4444; font-size: 12px; margin-top: 5px; }
        .btn-submit { 
            width: 100%; padding: 12px; background-color: #bae6fd; color: #0369a1; 
            border: none; border-radius: 8px; font-weight: bold; cursor: pointer; 
            font-size: 15px; margin-top: 10px; transition: all 0.3s;
        }
        .btn-submit:hover { background-color: #7dd3fc; }
        .btn-cancel { 
            display: block; text-align: center; margin-top: 15px; text-decoration: none; 
            color: #64748b; font-size: 14px;
        }
        .btn-cancel:hover { color: #334155; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card-form">
        <h2>Tambah Produk Baru</h2>

        <?php if (isset($errors["db"])): ?>
            <p style="color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 8px; font-size: 14px; text-align: center;">
                <?= $errors["db"] ?>
            </p>
        <?php endif; ?>

        <!-- WAJIB menambahkan enctype agar form bisa memproses file -->
        <form action="create.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label for="name">Nama Produk</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>" required>
                <?php if (isset($errors["name"])) echo '<div class="error-text">' . $errors["name"] . '</div>'; ?>
            </div>

            <div class="form-group">
                <label for="category">Kategori</label>
                <input type="text" id="category" name="category" value="<?= htmlspecialchars($category, ENT_QUOTES, "UTF-8") ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Harga</label>
                <input type="number" id="price" name="price" value="<?= htmlspecialchars($price, ENT_QUOTES, "UTF-8") ?>" required>
                <?php if (isset($errors["price"])) echo '<div class="error-text">' . $errors["price"] . '</div>'; ?>
            </div>

            <div class="form-group">
                <label for="stock">Stok</label>
                <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($stock, ENT_QUOTES, "UTF-8") ?>" required>
                <?php if (isset($errors["stock"])) echo '<div class="error-text">' . $errors["stock"] . '</div>'; ?>
            </div>
            
            <!-- Tambahan Form Input Gambar -->
            <div class="form-group">
                <label for="image">Gambar Produk (Opsional)</label>
                <input type="file" id="image" name="image" accept="image/png, image/jpeg, image/webp">
                <?php if (isset($errors["image"])) echo '<div class="error-text">' . $errors["image"] . '</div>'; ?>
            </div>

            <button type="submit" class="btn-submit">Simpan Produk</button>
            <a href="index.php" class="btn-cancel">Batal & Kembali</a>
        </form>
    </div>
</body>
</html>