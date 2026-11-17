<?php

$koneksi = mysqli_connect(
    "localhost",
    "root",
    "",
    "db_tokopenjualansepatu"
);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

?>