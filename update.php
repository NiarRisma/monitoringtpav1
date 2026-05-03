<?php
include 'koneksi.php';

// Ambil data dari parameter GET (sesuai dengan yang dikirim dari Arduino)
$suhu = $_GET['field1'] ?? 0;
$kelembapan = $_GET['field2'] ?? 0;
$metana = $_GET['field3'] ?? 0;
$co2 = $_GET['field4'] ?? 0;

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Masukkan data ke tabel
$sql = "INSERT INTO data_sensor (suhu, kelembapan, metana, co2) 
        VALUES ('$suhu', '$kelembapan', '$metana', '$co2')";

if ($conn->query($sql) === TRUE) {
    echo "Data berhasil disimpan";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
