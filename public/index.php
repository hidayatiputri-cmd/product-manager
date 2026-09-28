<?php
// Hubungkan ke database
require_once '../config/db.php';

// Ambil semua data produk dari database, diurutkan dari yang terbaru
$stmt = $pdo->query("SELECT id, name, category, price, stock FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk</title>
    <style>
        /* Desain Background Biru dan Pink Pastel */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 20px; 
            /* Gradient background dari biru pastel ke pink pastel */
            background: linear-gradient(135deg, #e0f2fe 0%, #fce7f3 100%);
            min-height: 100vh;
            color: #334155;
        }
        
        h1 { 
            text-align: center; 
            color: #1e293b; 
            margin-bottom: 30px; 
        }

        /* Pesan Sukses (Alert) dengan tema pink pastel */
        .alert { 
            background-color: #fbcfe8; 
            color: #831843; 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 10px; 
            text-align: center; 
            font-weight: bold; 
            border: 1px solid #f9a8d4; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        /* Tombol Tambah Produk (Biru Pastel) */
        .btn-add { 
            display: block; 
            width: max-content;
            margin: 0 auto 30px auto;
            padding: 12px 25px; 
            background-color: #bae6fd; 
            color: #0369a1; 
            text-decoration: none; 
            border-radius: 25px; 
            font-weight: bold; 
            transition: all 0.3s ease; 
            box-shadow: 0 4px 6px rgba(186, 230, 253, 0.5); 
        }
        .btn-add:hover { 
            background-color: #7dd3fc; 
            transform: translateY(-2px);
        }
        
        /* CSS Flexbox untuk Grid Produk */
        .products { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 20px; 
            align-items: stretch; 
            justify-content: center; 
        }

        /* Desain Card Produk bergaya Glassmorphism ringan */
        .card { 
            flex: 1 1 250px; 
            max-width: 320px; 
            padding: 25px; 
            border: 2px solid #ffffff; 
            border-radius: 20px; 
            background: rgba(255, 255, 255, 0.7); /* Putih agak transparan */
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); 
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .card:hover { 
            transform: translateY(-8px); 
            border-color: #fbcfe8; /* Berubah pink pastel saat di-hover */
            box-shadow: 0 15px 20px -3px rgba(0,0,0,0.1);
        }
        .card h3 { 
            margin-top: 0; 
            color: #0284c7; /* Biru agak gelap untuk judul produk */
            font-size: 1.4rem;
        }
        
        /* Tombol aksi di dalam Card */
        .actions { 
            display: flex; 
            gap: 12px; 
            margin-top: 25px; 
        }
        .btn { 
            padding: 10px 15px; 
            text-decoration: none; 
            border-radius: 10px; 
            font-weight: 600; 
            text-align: center; 
            flex: 1; 
            transition: all 0.3s ease; 
            font-size: 14px; 
        }
        /* Tombol Edit (Biru Pastel) */
        .btn-edit { 
            background-color: #e0f2fe; 
            color: #0369a1; 
        }
        .btn-edit:hover { 
            background-color: #bae6fd; 
        }
        /* Tombol Hapus (Pink Pastel) */
        .btn-delete { 
            background-color: #fce7f3; 
            color: #be185d; 
        }
        .btn-delete:hover { 
            background-color: #fbcfe8; 
        }
    </style>
</head>
<body>
    <h1>Daftar Produk</h1>

    <!-- Menangkap status PRG dari URL (index.php?status=created) -->
    <?php if (($_GET["status"] ?? "") === "created"): ?>
        <div class="alert">✨ Berhasil! Produk baru telah ditambahkan.</div>
    <?php endif; ?>
    <?php if (($_GET["status"] ?? "") === "deleted"): ?>
        <div class="alert">🗑️ Berhasil! Produk telah dihapus.</div>
    <?php endif; ?>
    <?php if (($_GET["status"] ?? "") === "updated"): ?>
        <div class="alert">📝 Berhasil! Produk telah diperbarui.</div>
    <?php endif; ?>

    <a href="create.php" class="btn-add">+ Tambah Produk Baru</a>

    <!-- Menampilkan Card Produk -->
    <div class="products">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $p): ?>
                <div class="card">
                    <!-- Wajib menggunakan htmlspecialchars untuk mencegah celah XSS -->
                    <h3><?= htmlspecialchars($p["name"], ENT_QUOTES, "UTF-8") ?></h3>
                    <p><strong>Kategori:</strong> <?= htmlspecialchars($p["category"], ENT_QUOTES, "UTF-8") ?></p>
                    <p><strong>Harga:</strong> Rp <?= number_format($p["price"], 0, ",", ".") ?></p>
                    <p><strong>Stok:</strong> <?= htmlspecialchars($p["stock"], ENT_QUOTES, "UTF-8") ?></p>
                    
                    <div class="actions">
                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-edit">Edit</a>
                        <a href="#" class="btn btn-delete">Hapus</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; width: 100%; color: #64748b;">Belum ada produk. Silakan tambah produk baru.</p>
        <?php endif; ?>
    </div>
</body>
</html>