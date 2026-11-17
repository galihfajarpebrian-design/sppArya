<?php
include 'koneksi.php';

$edit_data = null;
$edit_id_produk = '';
$edit_jumlah = 1;

// Proses Tambah / Edit Data Pelanggan & Transaksi
if (isset($_POST['simpan_pelanggan'])) {
    $id_pelanggan   = mysqli_real_escape_string($koneksi, $_POST['id_pelanggan']);
    $nama_pelanggan = mysqli_real_escape_string($koneksi, $_POST['nama_pelanggan']);
    $alamat         = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_hp          = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    
    $id_produk      = mysqli_real_escape_string($koneksi, $_POST['id_produk'] ?? '');
    $jumlah         = (int) ($_POST['jumlah'] ?? 1);
    $is_edit        = $_POST['is_edit'] ?? '0';

    if ($is_edit === '1') {
        // 1. UPDATE data pelanggan
        $query = "UPDATE pelanggan SET nama_pelanggan='$nama_pelanggan', alamat='$alamat', no_hp='$no_hp' WHERE id_pelanggan='$id_pelanggan'";
        if (!mysqli_query($koneksi, $query)) {
            $query = "UPDATE pelanggan SET nama_pelanggan='$nama_pelanggan', alamat='$alamat', telepon='$no_hp' WHERE id_pelanggan='$id_pelanggan'";
            mysqli_query($koneksi, $query);
        }

        // 2. CEK / UPDATE Transaksi Penjualan
        $q_cek_transaksi = mysqli_query($koneksi, "SELECT * FROM penjualan WHERE id_pelanggan='$id_pelanggan'");
        if (mysqli_num_rows($q_cek_transaksi) > 0) {
            $d_trans = mysqli_fetch_assoc($q_cek_transaksi);
            $id_produk_lama = $d_trans['id_produk'];
            $jumlah_lama = $d_trans['jumlah'];

            if (!empty($id_produk)) {
                $q_prod = mysqli_query($koneksi, "SELECT harga FROM produk WHERE id_produk='$id_produk'");
                $d_prod = mysqli_fetch_assoc($q_prod);
                $harga = $d_prod['harga'] ?? 0;
                $total_harga = $harga * $jumlah;

                mysqli_query($koneksi, "UPDATE produk SET stok = stok + $jumlah_lama WHERE id_produk='$id_produk_lama'");
                mysqli_query($koneksi, "UPDATE produk SET stok = stok - $jumlah WHERE id_produk='$id_produk'");
                mysqli_query($koneksi, "UPDATE penjualan SET id_produk='$id_produk', jumlah='$jumlah', total_harga='$total_harga' WHERE id_pelanggan='$id_pelanggan'");
            }
        } else {
            if (!empty($id_produk)) {
                $q_prod = mysqli_query($koneksi, "SELECT harga FROM produk WHERE id_produk='$id_produk'");
                $d_prod = mysqli_fetch_assoc($q_prod);
                $harga = $d_prod['harga'] ?? 0;
                $total_harga = $harga * $jumlah;
                $id_penjualan = 'TRX' . time();
                $tanggal = date('Y-m-d');

                mysqli_query($koneksi, "UPDATE produk SET stok = stok - $jumlah WHERE id_produk='$id_produk'");
                mysqli_query($koneksi, "INSERT INTO penjualan (id_penjualan, tanggal, id_pelanggan, id_produk, jumlah, total_harga) VALUES ('$id_penjualan', '$tanggal', '$id_pelanggan', '$id_produk', '$jumlah', '$total_harga')");
            }
        }

    } else {
        $query = "INSERT INTO pelanggan (id_pelanggan, nama_pelanggan, alamat, no_hp) VALUES ('$id_pelanggan', '$nama_pelanggan', '$alamat', '$no_hp')";
        if (!mysqli_query($koneksi, $query)) {
            $query = "INSERT INTO pelanggan (id_pelanggan, nama_pelanggan, alamat, telepon) VALUES ('$id_pelanggan', '$nama_pelanggan', '$alamat', '$no_hp')";
            mysqli_query($koneksi, $query);
        }

        if (!empty($id_produk)) {
            $q_prod = mysqli_query($koneksi, "SELECT harga, stok FROM produk WHERE id_produk='$id_produk'");
            if ($d_prod = mysqli_fetch_assoc($q_prod)) {
                $harga = $d_prod['harga'];
                $stok = $d_prod['stok'];
                $total_harga = $harga * $jumlah;

                if ($stok >= $jumlah) {
                    $id_penjualan = 'TRX' . time();
                    $tanggal = date('Y-m-d');

                    mysqli_query($koneksi, "UPDATE produk SET stok = stok - $jumlah WHERE id_produk='$id_produk'");
                    mysqli_query($koneksi, "INSERT INTO penjualan (id_penjualan, tanggal, id_pelanggan, id_produk, jumlah, total_harga) VALUES ('$id_penjualan', '$tanggal', '$id_pelanggan', '$id_produk', '$jumlah', '$total_harga')");
                }
            }
        }
    }

    header("Location: pelanggan.php");
    exit();
}

// Ambil Data untuk Di-edit
if (isset($_GET['edit'])) {
    $id_edit = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $res_edit = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE id_pelanggan='$id_edit'");
    if ($res_edit && mysqli_num_rows($res_edit) > 0) {
        $edit_data = mysqli_fetch_assoc($res_edit);
        
        $res_trans = mysqli_query($koneksi, "SELECT * FROM penjualan WHERE id_pelanggan='$id_edit'");
        if ($res_trans && mysqli_num_rows($res_trans) > 0) {
            $d_trans = mysqli_fetch_assoc($res_trans);
            $edit_id_produk = $d_trans['id_produk'];
            $edit_jumlah = $d_trans['jumlah'];
        }
    }
}

// Proses Hapus Data Pelanggan
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    $q_transaksi = mysqli_query($koneksi, "SELECT id_produk, jumlah FROM penjualan WHERE id_pelanggan='$id_hapus'");
    while($t = mysqli_fetch_assoc($q_transaksi)) {
        $p_id = $t['id_produk'];
        $p_jml = $t['jumlah'];
        mysqli_query($koneksi, "UPDATE produk SET stok = stok + $p_jml WHERE id_produk='$p_id'");
    }
    mysqli_query($koneksi, "DELETE FROM penjualan WHERE id_pelanggan='$id_hapus'");
    mysqli_query($koneksi, "DELETE FROM pelanggan WHERE id_pelanggan='$id_hapus'");

    header("Location: pelanggan.php");
    exit();
}

$query_pelanggan = "SELECT pelanggan.*, penjualan.jumlah, penjualan.total_harga, produk.nama_sepatu 
                    FROM pelanggan 
                    LEFT JOIN penjualan ON pelanggan.id_pelanggan = penjualan.id_pelanggan 
                    LEFT JOIN produk ON penjualan.id_produk = produk.id_produk";
$data_pelanggan = mysqli_query($koneksi, $query_pelanggan);

$list_produk = mysqli_query($koneksi, "SELECT * FROM produk");
$selected_product_id = $_GET['id'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pelanggan - Toko Sepatu</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; margin: 0; }
        nav { background-color: #1a252f; padding: 15px 30px; }
        nav a { color: #ecf0f1; text-decoration: none; margin-right: 15px; font-weight: 500; }
        .container { max-width: 1150px; margin: 30px auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; margin-top: 0; }
        form { margin-bottom: 25px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .form-full { grid-column: 1 / -1; display: grid; grid-template-columns: 2fr 1fr auto auto; gap: 10px; }
        input, select, button, a.btn-batal { padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        button { background-color: #27ae60; color: white; border: none; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #219150; }
        a.btn-batal { background-color: #95a5a6; color: white; text-decoration: none; text-align: center; font-weight: bold; line-height: 20px; display: inline-block; }
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
    <h2><?= $edit_data ? 'Edit Data Pelanggan & Pembelian' : 'Tambah Pelanggan & Beli Produk'; ?></h2>
    
    <form method="POST">
        <input type="hidden" name="is_edit" value="<?= $edit_data ? '1' : '0'; ?>">
        
        <input type="text" name="id_pelanggan" placeholder="ID Pelanggan (cth: PLG01)" value="<?= htmlspecialchars($edit_data['id_pelanggan'] ?? ''); ?>" <?= $edit_data ? 'readonly style="background:#eee;"' : 'required'; ?>>
        <input type="text" name="nama_pelanggan" placeholder="Nama Pelanggan" value="<?= htmlspecialchars($edit_data['nama_pelanggan'] ?? ''); ?>" required>
        <input type="text" name="alamat" placeholder="Alamat" value="<?= htmlspecialchars($edit_data['alamat'] ?? ''); ?>" required>
        <input type="text" name="no_hp" placeholder="No. Telepon / HP" value="<?= htmlspecialchars($edit_data['no_hp'] ?? $edit_data['telepon'] ?? $edit_data['no_telepon'] ?? ''); ?>" required>
        
        <div class="form-full">
            <select name="id_produk">
                <option value="">-- Pilih Produk yang Ingin Dibeli (Opsional) --</option>
                <?php 
                mysqli_data_seek($list_produk, 0);
                while ($pr = mysqli_fetch_assoc($list_produk)) : 
                    $selected_prod = (($selected_product_id == $pr['id_produk']) || ($edit_id_produk == $pr['id_produk'])) ? 'selected' : '';
                    $nama_sepatu = $pr['nama_sepatu'] ?? $pr['id_produk'];
                ?>
                    <option value="<?= htmlspecialchars($pr['id_produk']); ?>" <?= $selected_prod; ?>>
                        <?= htmlspecialchars($nama_sepatu); ?> (Stok: <?= $pr['stok']; ?> | Rp <?= number_format($pr['harga'] ?? 0, 0, ',', '.'); ?>)
                    </option>
                <?php endwhile; ?>
            </select>
            
            <input type="number" name="jumlah" placeholder="Jumlah Beli" min="1" value="<?= htmlspecialchars($edit_jumlah); ?>">
            
            <button type="submit" name="simpan_pelanggan"><?= $edit_data ? 'Update Data' : 'Simpan & Transaksi'; ?></button>
            <a href="pelanggan.php" class="btn-batal">Batal</a>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th>Alamat</th>
                <th>No. Telepon</th>
                <th>Produk yang Dibeli</th>
                <th>Jumlah</th>
                <th>Total Harga</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($data_pelanggan && mysqli_num_rows($data_pelanggan) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($data_pelanggan)) : 
                    $nama_sepatu_tampil = $row['nama_sepatu'] ?? '-';
                    $jumlah_beli = $row['jumlah'] ?? '-';
                    $total_harga_beli = isset($row['total_harga']) ? 'Rp ' . number_format($row['total_harga'], 0, ',', '.') : '-';
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['id_pelanggan'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($row['nama_pelanggan'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($row['alamat'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($row['no_hp'] ?? $row['telepon'] ?? $row['no_telepon'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($jumlah_beli !== '-' ? $nama_sepatu_tampil : 'Belum ada pembelian'); ?></td>
                    <td><?= htmlspecialchars($jumlah_beli); ?></td>
                    <td><?= htmlspecialchars($total_harga_beli); ?></td>
                    <td>
                        <a href="pelanggan.php?edit=<?= urlencode($row['id_pelanggan']); ?>" class="btn-edit">Edit</a>
                        <a href="pelanggan.php?hapus=<?= urlencode($row['id_pelanggan']); ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus pelanggan ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center;">Belum ada data pelanggan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>