<?php
$conn = new mysqli(
    "tramway.proxy.rlwy.net",
    "root",
    "TREojfsjiCescusVPTkvpfhttHjGiylt",
    "railway",
    59384
);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set timezone MySQL ke WIB (UTC+7)
$conn->query("SET time_zone = '+07:00'");

// Set timezone PHP ke WIB
date_default_timezone_set('Asia/Jakarta');
?>