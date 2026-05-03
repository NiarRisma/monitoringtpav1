<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "monitoring_tpa";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query langsung cek selisih dengan NOW()
$query = "
    SELECT 
        CASE 
            WHEN MAX(waktu) >= (NOW() - INTERVAL 10 SECOND) 
            THEN 'online' 
            ELSE 'offline' 
        END AS status,
        MAX(waktu) AS last_waktu,
        TIMESTAMPDIFF(SECOND, MAX(waktu), NOW()) AS selisih_detik
    FROM data_sensor
";

$result = $conn->query($query);
$row = $result->fetch_assoc();

// Output hanya status, tapi bisa dipakai debug juga
echo $row['status'];

// Kalau mau debugging, aktifkan baris ini:
// echo 'Status: '.$row['status'].' | Last: '.$row['last_waktu'].' | Selisih: '.$row['selisih_detik'].' detik';

$conn->close();
