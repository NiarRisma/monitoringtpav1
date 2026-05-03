<?php
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
include 'koneksi.php';
$data = $conn->query("SELECT * FROM data_sensor ORDER BY waktu DESC LIMIT $limit");

while($row = $data->fetch_assoc()) {
  echo "<tr>
    <td>{$row['waktu']}</td>
    <td>{$row['suhu']}</td>
    <td>{$row['kelembapan']}</td>
    <td>{$row['metana']}</td>
    <td>{$row['co2']}</td>
  </tr>";
}
?>
