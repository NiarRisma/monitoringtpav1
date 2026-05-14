<?php
include 'koneksi.php';

$koneksi = $conn;

$data = $koneksi->query("
SELECT 
DATE_ADD(waktu, INTERVAL 7 HOUR) as waktu,
suhu,
kelembapan,
metana,
co2
FROM data_sensor
ORDER BY waktu DESC
LIMIT 10
");

$response = [];

while($row = $data->fetch_assoc()) {
  $response[] = [
    'waktu' => $row['waktu'],
    'suhu' => floatval($row['suhu']),
    'kelembapan' => floatval($row['kelembapan']),
    'metana' => intval($row['metana']),
    'co2' => intval($row['co2'])
  ];
}

echo json_encode(array_reverse($response));
?>