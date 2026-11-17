<?php
include 'koneksi.php';

// Variable tempat menyimpan data yang sedang diedit
$edit_data = null;

// Proses Tambah / Edit Produk
if (isset($_POST['simpan_produk'])) {
    $id_produk   = mysqli_real_escape_string($koneksi, $_POST['id_produk']);
    $nama_sepatu = mysqli_real_escape_string($koneksi, $_POST['nama_sepatu']);
    $harga       = mysqli_real_escape_string($koneksi, $_POST['harga']);
    $stok        = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $is_edit     = $_POST['is_edit'] ?? '0';

    if ($is_edit === '1') {
        // UPDATE jika sedang dalam mode edit
        $query = "UPDATE produk SET nama_sepatu='$nama_sepatu', harga='$harga', stok='$stok' WHERE id_produk='$id_produk'";
    } else {
        // INSERT jika tambah data baru
        $query = "INSERT INTO produk (id_produk, nama_sepatu, harga, stok) VALUES ('$id_produk', '$nama_sepatu', '$harga', '$stok')";
    }

    if (mysqli_query($koneksi, $query)) {
        header("Location: produk.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}

// Ambil Data untuk Edit
if (isset($_GET['edit'])) {
    $id_edit = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $res_edit = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk='$id_edit'");
    if (mysqli_num_rows($res_edit) > 0) {
        $edit_data = mysqli_fetch_assoc($res_edit);
    }
}

// Proses Hapus Produk
if (isset($_GET['hapus'])) {
    $id_produk = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM produk WHERE id_produk='$id_produk'");
    header("Location: produk.php");
    exit();
}

$data_produk = mysqli_query($koneksi, "SELECT * FROM produk");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk - Toko Sepatu</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; margin: 0; }
        nav { background-color: #1a252f; padding: 15px 30px; }
        nav a { color: #ecf0f1; text-decoration: none; margin-right: 15px; font-weight: 500; }
        .container { max-width: 1000px; margin: 30px auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; margin-top: 0; }
        form { margin-bottom: 25px; display: grid; grid-template-columns: repeat(4, 1fr) auto; gap: 10px; }
        input, button, a.btn-batal { padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
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
    <h2><?= $edit_data ? 'Edit Data Produk' : 'Tambah Data Produk'; ?></h2>
    
    <form method="POST">
        <input type="hidden" name="is_edit" value="<?= $edit_data ? '1' : '0'; ?>">
        <input type="text" name="id_produk" placeholder="ID Produk (cth: PRD01)" value="<?= htmlspecialchars($edit_data['id_produk'] ?? ''); ?>" <?= $edit_data ? 'readonly style="background:#eee;"' : 'required'; ?>>
        <input type="text" name="nama_sepatu" placeholder="Nama Sepatu" value="<?= htmlspecialchars($edit_data['nama_sepatu'] ?? $edit_data['produk'] ?? ''); ?>" required>
        <input type="number" name="harga" placeholder="Harga (Rp)" value="<?= htmlspecialchars($edit_data['harga'] ?? ''); ?>" required>
        <input type="number" name="stok" placeholder="Stok" value="<?= htmlspecialchars($edit_data['stok'] ?? ''); ?>" required>
        
        <button type="submit" name="simpan_produk"><?= $edit_data ? 'Update' : 'Tambah'; ?></button>
        <?php if ($edit_data): ?>
            <a href="produk.php" class="btn-batal">Batal</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID Produk</th>
                <th>Nama Sepatu</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($data_produk)) : ?>
            <tr>
                <td><?= htmlspecialchars($row['id_produk'] ?? ''); ?></td>
                <td><?= htmlspecialchars($row['nama_sepatu'] ?? $row['produk'] ?? '-'); ?></td>
                <td>Rp <?= number_format($row['harga'] ?? 0, 0, ',', '.'); ?></td>
                <td><?= htmlspecialchars($row['stok'] ?? '0'); ?></td>
                <td>
                    <a href="produk.php?edit=<?= $row['id_produk']; ?>" class="btn-edit">Edit</a>
                    <a href="produk.php?hapus=<?= $row['id_produk']; ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>