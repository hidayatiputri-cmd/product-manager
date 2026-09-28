<?php
session_start();
require_once '../config/db.php';

$_SESSION["csrf"] ??= bin2hex(random_bytes(32));

$stmt = $pdo->query("SELECT id, name, category, price, stock, image FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: linear-gradient(135deg, #e0f2fe 0%, #fce7f3 100%); min-height: 100vh; color: #334155; }
        h1 { text-align: center; color: #1e293b; margin-bottom: 30px; }
        .alert { background-color: #fbcfe8; color: #831843; padding: 15px; margin-bottom: 20px; border-radius: 10px; text-align: center; font-weight: bold; border: 1px solid #f9a8d4; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .btn-add { display: block; width: max-content; margin: 0 auto 30px auto; padding: 12px 25px; background-color: #bae6fd; color: #0369a1; text-decoration: none; border-radius: 25px; font-weight: bold; transition: all 0.3s ease; box-shadow: 0 4px 6px rgba(186, 230, 253, 0.5); }
        .btn-add:hover { background-color: #7dd3fc; transform: translateY(-2px); }
        
        .products { display: flex; flex-wrap: wrap; gap: 20px; align-items: stretch; justify-content: center; }
        
        .card { flex: 1 1 250px; max-width: 320px; padding: 20px; border: 2px solid #ffffff; border-radius: 20px; background: rgba(255, 255, 255, 0.7); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); backdrop-filter: blur(10px); transition: all 0.3s ease; display: flex; flex-direction: column; justify-content: space-between; }
        .card:hover { transform: translateY(-8px); border-color: #fbcfe8; box-shadow: 0 15px 20px -3px rgba(0,0,0,0.1); }
        
        /* Gaya untuk Gambar Produk */
        .card-img-container { width: 100%; height: 512px; border-radius: 12px; overflow: hidden; margin-bottom: 15px; background: #e2e8f0; }
        .card-img-container img { width: 100%; height: 100%; object-fit: cover; }
        
        .card h3 { margin: 0 0 10px 0; color: #0284c7; font-size: 1.3rem; }
        .card p { margin: 6px 0; font-size: 14px; }
        
        .actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn { padding: 8px 12px; text-decoration: none; border-radius: 10px; font-weight: 600; text-align: center; flex: 1; transition: all 0.3s ease; font-size: 14px; border: none; cursor: pointer; font-family: inherit; }
        .btn-edit { background-color: #e0f2fe; color: #0369a1; display: block; }
        .btn-edit:hover { background-color: #bae6fd; }
        .btn-delete { background-color: #fce7f3; color: #be185d; width: 100%; box-sizing: border-box; }
        .btn-delete:hover { background-color: #fbcfe8; }
        .form-delete { flex: 1; margin: 0; padding: 0; }
    </style>
</head>
<body>
    <h1>Daftar Produk</h1>

    <?php if (($_GET["status"] ?? "") === "created"): ?>
        <div class="alert">✨ Berhasil! Produk baru beserta gambar telah ditambahkan.</div>
    <?php endif; ?>
    <?php if (($_GET["status"] ?? "") === "deleted"): ?>
        <div class="alert">🗑️ Berhasil! Produk telah dihapus.</div>
    <?php endif; ?>
    <?php if (($_GET["status"] ?? "") === "updated"): ?>
        <div class="alert">📝 Berhasil! Produk telah diperbarui.</div>
    <?php endif; ?>

    <a href="create.php" class="btn-add">+ Tambah Produk Baru</a>

    <div class="products">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $p): ?>
                <div class="card">
                    <div>
                        <!-- Tampilkan Gambar jika ada di database -->
                        <?php if (!empty($p["image"])): ?>
                            <div class="card-img-container">
                                <img src="uploads/<?= htmlspecialchars($p["image"], ENT_QUOTES, "UTF-8") ?>" alt="Foto Produk">
                            </div>
                        <?php endif; ?>

                        <h3><?= htmlspecialchars($p["name"], ENT_QUOTES, "UTF-8") ?></h3>
                        <p><strong>Kategori:</strong> <?= htmlspecialchars($p["category"], ENT_QUOTES, "UTF-8") ?></p>
                        <p><strong>Harga:</strong> Rp <?= number_format($p["price"], 0, ",", ".") ?></p>
                        <p><strong>Stok:</strong> <?= htmlspecialchars($p["stock"], ENT_QUOTES, "UTF-8") ?></p>
                    </div>
                    
                    <div class="actions">
                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-edit">Edit</a>
                        
                        <form method="POST" action="delete.php" class="form-delete" onsubmit="return confirm('Apakah kamu yakin ingin menghapus produk ini?');">
                            <input type="hidden" name="id" value="<?= $p["id"] ?>">
                            <input type="hidden" name="csrf" value="<?= $_SESSION["csrf"] ?>">
                            <button type="submit" class="btn btn-delete">Hapus</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; width: 100%; color: #64748b;">Belum ada produk. Silakan tambah produk baru.</p>
        <?php endif; ?>
    </div>
</body>
</html>