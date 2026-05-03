<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "monitoring_tpa";

// Ambil data dari parameter GET (sesuai dengan yang dikirim dari Arduino)
$metana = $_GET['metana'];
$co2 = $_GET['co2'];
$suhu = $_GET['suhu'];
$kelembapan = $_GET['kelembapan'];

// Koneksi ke database
$conn = new mysqli($host, $user, $pass, $db);

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
