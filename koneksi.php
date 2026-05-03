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
?>