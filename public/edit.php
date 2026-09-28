<?php
require_once '../config/db.php';

$errors = [];

// 1. Tangkap ID dari URL (metode GET) dan pastikan itu angka
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

// Jika tidak ada ID yang valid, kembalikan ke halaman depan
if (!$id) {
    header("Location: index.php");
    exit;
}

// 2. Ambil data produk saat ini untuk mengisi form awal
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(["id" => $id]);
$product = $stmt->fetch();

// Jika produk dengan ID tersebut tidak ditemukan di database
if (!$product) {
    die("Produk tidak ditemukan.");
}

// Set variabel awal dari database
$name = $product['name'];
$category = $product['category'];
$price = $product['price'];
$stock = $product['stock'];

// 3. Proses jika form disubmit (metode POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Ambil input dan normalisasi
    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "Umum");
    $price = filter_input(INPUT_POST, "price", FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, "stock", FILTER_VALIDATE_INT);

    // Validasi aturan bisnis dari dosen
    if (mb_strlen($name) < 3) {
        $errors["name"] = "Nama minimal 3 karakter.";
    }
    if ($price === false || $price <= 0) {
        $errors["price"] = "Harga harus > 0.";
    }
    if ($stock === false || $stock < 0) {
        $errors["stock"] = "Stok tidak boleh negatif.";
    }

    // Jika lolos validasi, lakukan proses UPDATE
    if (empty($errors)) {
        try {
            // Prepared statement untuk UPDATE data berdasarkan ID
            $stmt = $pdo->prepare("UPDATE products SET name=:name, category=:category, price=:price, stock=:stock WHERE id=:id");
            $stmt->execute([
                "name" => $name,
                "category" => $category,
                "price" => $price,
                "stock" => $stock,
                "id" => $id
            ]);

            // Redirect ke index dengan status updated
            header("Location: index.php?status=updated");
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
    <title>Edit Produk</title>
    <style>
        /* Desain disamakan dengan tema pastel di index.php */
        body { 
            font-family: 'Segoe UI', sans-serif; 
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #e0f2fe 0%, #fce7f3 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #334155;
        }
        .card-form {
            width: 100%;
            max-width: 450px;
            padding: 30px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.85);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            backdrop-filter: blur(10px);
            border: 2px solid #ffffff;
        }
        h2 { margin-top: 0; color: #0284c7; text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: 600; font-size: 14px; margin-bottom: 5px; display: block; }
        input { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #cbd5e1; 
            border-radius: 8px; 
            box-sizing: border-box; 
            font-family: inherit;
        }
        input:focus { outline: none; border-color: #7dd3fc; box-shadow: 0 0 0 3px rgba(125, 211, 252, 0.3); }
        .error-text { color: #ef4444; font-size: 12px; margin-top: 5px; }
        .btn-submit { 
            width: 100%; 
            padding: 12px; 
            background-color: #bae6fd; 
            color: #0369a1; 
            border: none; 
            border-radius: 8px; 
            font-weight: bold; 
            cursor: pointer; 
            font-size: 15px;
            margin-top: 10px;
            transition: all 0.3s;
        }
        .btn-submit:hover { background-color: #7dd3fc; }
        .btn-cancel { 
            display: block; 
            text-align: center; 
            margin-top: 15px; 
            text-decoration: none; 
            color: #64748b; 
            font-size: 14px;
        }
        .btn-cancel:hover { color: #334155; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card-form">
        <h2>Edit Produk</h2>

        <?php if (isset($errors["db"])): ?>
            <p style="color: #ef4444; background: #fee2e2; padding: 10px; border-radius: 8px; font-size: 14px; text-align: center;">
                <?= $errors["db"] ?>
            </p>
        <?php endif; ?>

        <!-- Perhatikan URL action-nya tetap membawa parameter ID produk -->
        <form action="edit.php?id=<?= $id ?>" method="POST">
            
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

            <button type="submit" class="btn-submit">Simpan Perubahan</button>
            <a href="index.php" class="btn-cancel">Batal & Kembali</a>
        </form>
    </div>
</body>
</html>