<?php
include 'koneksi.php';

$edit_data = null;

// Proses Tambah / Edit Data Penjualan
if (isset($_POST['simpan_penjualan'])) {
    $id_penjualan  = mysqli_real_escape_string($koneksi, $_POST['id_penjualan']);
    $tgl_penjualan = mysqli_real_escape_string($koneksi, $_POST['tgl_penjualan']);
    $id_pelanggan  = mysqli_real_escape_string($koneksi, $_POST['id_pelanggan']);
    $id_produk     = mysqli_real_escape_string($koneksi, $_POST['id_produk']);
    $jumlah        = (int) $_POST['jumlah'];
    $is_edit       = $_POST['is_edit'] ?? '0';

    // 1. Ambil data produk (harga dan stok saat ini)
    $q_produk = mysqli_query($koneksi, "SELECT harga, stok FROM produk WHERE id_produk='$id_produk'");
    $d_produk = mysqli_fetch_assoc($q_produk);
    $harga_satuan = $d_produk['harga'] ?? 0;
    $stok_sekarang = $d_produk['stok'] ?? 0;
    $total_harga = $harga_satuan * $jumlah;

    mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS=0;");

    if ($is_edit === '1') {
        // Ambil jumlah lama sebelum di-edit untuk mengembalikan stok lama
        $q_lama = mysqli_query($koneksi, "SELECT id_produk, jumlah FROM penjualan WHERE id_penjualan='$id_penjualan'");
        $d_lama = mysqli_fetch_assoc($q_lama);
        $id_produk_lama = $d_lama['id_produk'];
        $jumlah_lama = $d_lama['jumlah'];

        if ($id_produk_lama === $id_produk) {
            // Jika produknya sama, hitung selisih jumlahnya
            $selisih = $jumlah - $jumlah_lama;
            if ($stok_sekarang < $selisih) {
                echo "<script>alert('Stok tidak mencukupi!'); window.location='penjualan.php';</script>";
                exit();
            }
            mysqli_query($koneksi, "UPDATE produk SET stok = stok - $selisih WHERE id_produk='$id_produk'");
        } else {
            // Jika produknya diganti, kembalikan stok produk lama dan kurangi stok produk baru
            mysqli_query($koneksi, "UPDATE produk SET stok = stok + $jumlah_lama WHERE id_produk='$id_produk_lama'");
            if ($stok_sekarang < $jumlah) {
                echo "<script>alert('Stok produk baru tidak mencukupi!'); window.location='penjualan.php';</script>";
                exit();
            }
            mysqli_query($koneksi, "UPDATE produk SET stok = stok - $jumlah WHERE id_produk='$id_produk'");
        }

        // Update data penjualan
        $query = "UPDATE penjualan SET tanggal='$tgl_penjualan', id_pelanggan='$id_pelanggan', id_produk='$id_produk', jumlah='$jumlah', total_harga='$total_harga' WHERE id_penjualan='$id_penjualan'";
    } else {
        // Validasi apakah stok mencukupi untuk transaksi baru
        if ($stok_sekarang < $jumlah) {
            echo "<script>alert('Stok barang tidak mencukupi! Sisa stok: $stok_sekarang'); window.location='penjualan.php';</script>";
            exit();
        }

        // Kurangi stok produk
        mysqli_query($koneksi, "UPDATE produk SET stok = stok - $jumlah WHERE id_produk='$id_produk'");

        // Insert data penjualan baru
        $query = "INSERT INTO penjualan (id_penjualan, tanggal, id_pelanggan, id_produk, jumlah, total_harga) VALUES ('$id_penjualan', '$tgl_penjualan', '$id_pelanggan', '$id_produk', '$jumlah', '$total_harga')";
    }

    if (mysqli_query($koneksi, $query)) {
        mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS=1;");
        header("Location: penjualan.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}

// Ambil Data untuk Di-edit
if (isset($_GET['edit'])) {
    $id_edit = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $res_edit = mysqli_query($koneksi, "SELECT * FROM penjualan WHERE id_penjualan='$id_edit'");
    if ($res_edit && mysqli_num_rows($res_edit) > 0) {
        $edit_data = mysqli_fetch_assoc($res_edit);
    }
}

// Proses Hapus Data Penjualan (Stok dikembalikan saat dihapus)
if (isset($_GET['hapus'])) {
    $id_penjualan = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Ambil data penjualan untuk mengembalikan stok produk
    $q_del = mysqli_query($koneksi, "SELECT id_produk, jumlah FROM penjualan WHERE id_penjualan='$id_penjualan'");
    if ($d_del = mysqli_fetch_assoc($q_del)) {
        $id_p = $d_del['id_produk'];
        $jml = $d_del['jumlah'];
        // Kembalikan stok
        mysqli_query($koneksi, "UPDATE produk SET stok = stok + $jml WHERE id_produk='$id_p'");
    }

    mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS=0;");
    mysqli_query($koneksi, "DELETE FROM penjualan WHERE id_penjualan='$id_penjualan'");
    mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS=1;");
    header("Location: penjualan.php");
    exit();
}

// Ambil daftar Pelanggan dan Produk untuk Dropdown Form
$list_pelanggan = mysqli_query($koneksi, "SELECT * FROM pelanggan");
$list_produk    = mysqli_query($koneksi, "SELECT * FROM produk");

// Query untuk mencari Barang Paling Laku / Terlaris
$query_terlaris = "SELECT produk.nama_sepatu, SUM(penjualan.jumlah) as total_terjual 
                   FROM penjualan 
                   JOIN produk ON penjualan.id_produk = produk.id_produk 
                   GROUP BY penjualan.id_produk 
                   ORDER BY total_terjual DESC 
                   LIMIT 1";
$result_terlaris = mysqli_query($koneksi, $query_terlaris);
$data_terlaris = mysqli_fetch_assoc($result_terlaris);
$nama_terlaris = '-';
$jumlah_terlaris = 0;
if ($data_terlaris) {
    $nama_terlaris = $data_terlaris['nama_sepatu'] ?? 'Produk Terlaris';
    $jumlah_terlaris = $data_terlaris['total_terjual'];
}

// Query JOIN untuk mengambil data Penjualan, Nama Pelanggan, dan Nama Produk/Sepatu
$query_penjualan = "SELECT penjualan.*, pelanggan.nama_pelanggan, produk.* 
                    FROM penjualan 
                    LEFT JOIN pelanggan ON penjualan.id_pelanggan = pelanggan.id_pelanggan 
                    LEFT JOIN produk ON penjualan.id_produk = produk.id_produk
                    ORDER BY penjualan.id_penjualan ASC";

$data_penjualan = mysqli_query($koneksi, $query_penjualan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Penjualan - Toko Sepatu</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; margin: 0; }
        nav { background-color: #1a252f; padding: 15px 30px; }
        nav a { color: #ecf0f1; text-decoration: none; margin-right: 15px; font-weight: 500; }
        .container { max-width: 1100px; margin: 30px auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; margin-top: 0; }
        .info-box { background: #e8f8f5; border-left: 5px solid #2ecc71; padding: 15px; margin-bottom: 20px; border-radius: 4px; color: #27ae60; font-weight: bold; }
        form { margin-bottom: 25px; display: grid; grid-template-columns: repeat(5, 1fr) auto auto; gap: 10px; }
        input, select, button, a.btn-batal { padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        button { background-color: #27ae60; color: white; border: none; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #219150; }
        a.btn-batal { background-color: #95a5a6; color: white; text-decoration: none; text-align: center; font-weight: bold; line-height: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #34495e; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .btn-edit { color: #2980b9; text-decoration: none; font-weight: bold; margin-right: 10px; }
        .btn-hapus { color: #e74c3c; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<nav>
    <a href="halamanutama.php">Halaman Utama</a>
    <a href="produk.php">Data Produk</a>
    <a href="pelanggan.php">Data Pelanggan</a>
    <a href="penjualan.php">Data Penjualan</a>
</nav>

<div class="container">
    <h2><?= $edit_data ? 'Edit Data Penjualan' : 'Tambah Data Penjualan'; ?></h2>

    <!-- Informasi Produk Terlaris -->
    <div class="info-box">
        🔥 Produk Paling Laku / Terlaris: 
        <span style="color: #2c3e50;"><?= htmlspecialchars($nama_terlaris); ?></span> 
        (Total Terjual: <?= $jumlah_terlaris; ?> pasang)
    </div>
    
    <form method="POST">
        <input type="hidden" name="is_edit" value="<?= $edit_data ? '1' : '0'; ?>">
        
        <input type="text" name="id_penjualan" placeholder="ID Penjualan (cth: TRX01)" value="<?= htmlspecialchars($edit_data['id_penjualan'] ?? ''); ?>" <?= $edit_data ? 'readonly style="background:#eee;"' : 'required'; ?>>
        
        <input type="date" name="tgl_penjualan" value="<?= htmlspecialchars($edit_data['tanggal'] ?? date('Y-m-d')); ?>" required>
        
        <!-- Dropdown Pilih Pelanggan -->
        <select name="id_pelanggan" required>
            <option value="">-- Pilih Pelanggan --</option>
            <?php 
            if ($list_pelanggan) {
                mysqli_data_seek($list_pelanggan, 0);
                while ($p = mysqli_fetch_assoc($list_pelanggan)) : 
                    $selected = (isset($edit_data['id_pelanggan']) && $edit_data['id_pelanggan'] == $p['id_pelanggan']) ? 'selected' : '';
                    $nama_p = $p['nama_pelanggan'] ?? $p['nama'] ?? $p['id_pelanggan'];
            ?>
                    <option value="<?= htmlspecialchars($p['id_pelanggan']); ?>" <?= $selected; ?>>
                        <?= htmlspecialchars($nama_p); ?>
                    </option>
            <?php 
                endwhile; 
            }
            ?>
        </select>

        <!-- Dropdown Pilih Sepatu / Produk -->
        <select name="id_produk" required>
            <option value="">-- Pilih Sepatu --</option>
            <?php 
            if ($list_produk) {
                mysqli_data_seek($list_produk, 0);
                while ($pr = mysqli_fetch_assoc($list_produk)) : 
                    $selected = (isset($edit_data['id_produk']) && $edit_data['id_produk'] == $pr['id_produk']) ? 'selected' : '';
                    $nama_sepatu = $pr['nama_sepatu'] ?? $pr['nama_produk'] ?? $pr['produk'] ?? $pr['nama'] ?? $pr['id_produk'];
            ?>
                    <option value="<?= htmlspecialchars($pr['id_produk']); ?>" <?= $selected; ?>>
                        <?= htmlspecialchars($nama_sepatu); ?> (Stok: <?= $pr['stok']; ?> | Rp <?= number_format($pr['harga'] ?? 0, 0, ',', '.'); ?>)
                    </option>
            <?php 
                endwhile; 
            }
            ?>
        </select>

        <input type="number" name="jumlah" placeholder="Jumlah" value="<?= htmlspecialchars($edit_data['jumlah'] ?? ''); ?>" min="1" required>
        
        <button type="submit" name="simpan_penjualan"><?= $edit_data ? 'Update' : 'Tambah'; ?></button>
        <?php if ($edit_data): ?>
            <a href="penjualan.php" class="btn-batal">Batal</a>
        <?php endif; ?>
    </form>

    <!-- Tabel Data Penjualan -->
    <table>
        <thead>
            <tr>
                <th>ID Penjualan</th>
                <th>Tanggal</th>
                <th>Nama Pelanggan</th>
                <th>Nama Sepatu</th>
                <th>Jumlah</th>
                <th>Total Harga</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($data_penjualan && mysqli_num_rows($data_penjualan) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($data_penjualan)) : ?>
                <?php 
                    $nama_pelanggan_tampil = $row['nama_pelanggan'] ?? $row['id_pelanggan'];
                    $nama_sepatu_tampil = $row['nama_sepatu'] ?? $row['nama_produk'] ?? $row['produk'] ?? $row['nama'] ?? $row['id_produk'];
                    $harga_satuan = $row['harga'] ?? 0;
                    $jumlah_beli = $row['jumlah'] ?? 0;
                    $total_harga = $row['total_harga'] ?? ($harga_satuan * $jumlah_beli);
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['id_penjualan'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($row['tanggal'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($nama_pelanggan_tampil); ?></td>
                    <td><?= htmlspecialchars($nama_sepatu_tampil); ?></td>
                    <td><?= htmlspecialchars($jumlah_beli); ?></td>
                    <td><strong>Rp <?= number_format($total_harga, 0, ',', '.'); ?></strong></td>
                    <td>
                        <a href="penjualan.php?edit=<?= $row['id_penjualan']; ?>" class="btn-edit">Edit</a>
                        <a href="penjualan.php?hapus=<?= $row['id_penjualan']; ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus data penjualan ini? Stok produk akan dikembalikan.')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Belum ada data penjualan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>