<?php
include 'koneksi.php';

// Fitur Pencarian
$keyword = '';
if (isset($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['cari']);
    $query = "SELECT * FROM produk WHERE nama_sepatu LIKE '%$keyword%' OR id_produk LIKE '%$keyword%'";
} else {
    $query = "SELECT * FROM produk";
}

$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Sepatu - Halaman Utama</title>
    <!-- Google Fonts & FontAwesome Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f4f6f9;
            color: #333;
        }

        /* Navbar */
        nav {
            background: #1e293b;
            padding: 18px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            color: #3b82f6;
        }

        .nav-links {
            display: flex;
            gap: 25px;
        }

        .nav-links a {
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 15px;
        }

        .nav-links a:hover, .nav-links a.active {
            color: #3b82f6;
        }

        /* Hero / Banner Header */
        .hero {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            padding: 50px 5%;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 0 0 20px 20px;
        }

        .hero h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .hero p {
            color: #94a3b8;
            font-size: 16px;
            margin-bottom: 25px;
        }

        /* Search Bar */
        .search-box {
            max-width: 550px;
            margin: 0 auto;
            display: flex;
            background: white;
            padding: 6px;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .search-box input {
            border: none;
            outline: none;
            padding: 12px 20px;
            width: 100%;
            border-radius: 50px;
            font-size: 15px;
        }

        .search-box button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .search-box button:hover {
            background: #2563eb;
        }

        /* Container & Grid Layout */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 50px;
        }

        .section-title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
        }

        /* Product Card Style */
        .card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            border: 1px solid #e2e8f0;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
        }

        .card-img-placeholder {
            height: 180px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 48px;
            position: relative;
        }

        .card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .product-title {
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .price {
            font-size: 20px;
            font-weight: 700;
            color: #e11d48;
            margin-bottom: 12px;
        }

        .stock-info {
            font-size: 13px;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .badge-stok {
            background-color: #dcfce7;
            color: #166534;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-stok.habis {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .btn-detail {
            margin-top: auto;
            text-align: center;
            background: #1e293b;
            color: white;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: background 0.3s;
        }

        .btn-detail:hover {
            background: #3b82f6;
        }

        .empty-state {
            text-align: center;
            padding: 50px 0;
            color: #64748b;
            grid-column: 1 / -1;
        }

        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #cbd5e1;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav>
        <a href="halamanutama.php" class="logo">
            <i class="fa-solid fa-shoe-prints"></i> Toko Sepatu
        </a>
        <div class="nav-links">
            <a href="halamanutama.php" class="active">Halaman Utama</a>
            <a href="produk.php">Data Produk</a>
            <a href="pelanggan.php">Data Pelanggan</a>
            <a href="penjualan.php">Data Penjualan</a>
        </div>
    </nav>

    <!-- Banner Header -->
    <div class="hero">
        <h1>Koleksi Sepatu Terbaik</h1>
        <p>Temukan sepatu favorit Anda dengan harga dan kualitas terbaik</p>

        <form action="halamanutama.php" method="GET" class="search-box">
            <input type="text" name="cari" placeholder="Cari nama sepatu..." value="<?= htmlspecialchars($keyword); ?>">
            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        </form>
    </div>

    <!-- Main Content -->
    <div class="container">
        <h2 class="section-title">
            <i class="fa-solid fa-boxes-stacked"></i> 
            <?= !empty($keyword) ? 'Hasil Pencarian: "' . htmlspecialchars($keyword) . '"' : 'Daftar Produk'; ?>
        </h2>

        <div class="product-grid">
            <?php if (mysqli_num_rows($result) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : 
                    $nama_sepatu = $row['nama_sepatu'] ?? $row['produk'] ?? 'Sepatu Tanpa Nama';
                    $stok = $row['stok'] ?? 0;
                ?>
                    <div class="card">
                        <div class="card-img-placeholder">
                            <i class="fa-solid fa-shoe-prints"></i>
                        </div>
                        <div class="card-body">
                            <h3 class="product-title"><?= htmlspecialchars($nama_sepatu); ?></h3>
                            <div class="price">Rp <?= number_format($row['harga'] ?? 0, 0, ',', '.'); ?></div>
                            
                            <div class="stock-info">
                                <span>Status Stok:</span>
                                <?php if ($stok > 0) : ?>
                                    <span class="badge-stok"><i class="fa-solid fa-check"></i> <?= $stok; ?> Tersedia</span>
                                <?php else : ?>
                                    <span class="badge-stok habis"><i class="fa-solid fa-xmark"></i> Stok Habis</span>
                                <?php endif; ?>
                            </div>

                            <a href="pelanggan.php?id=<?= $row['id_produk']; ?>" class="btn-detail">
                                <i class="fa-solid fa-cart-plus"></i> Beli Sekarang
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="empty-state">
                    <i class="fa-solid fa-box-open"></i>
                    <h3>Produk tidak ditemukan</h3>
                    <p>Coba kata kunci lain atau periksa kembali daftar produk Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>