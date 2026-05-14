<?php

include 'koneksi.php';

$query = "
SELECT 
    CASE 
        WHEN MAX(waktu) >= (UTC_TIMESTAMP() - INTERVAL 30 SECOND) 
        THEN 'online' 
        ELSE 'offline' 
    END AS status,

    DATE_ADD(MAX(waktu), INTERVAL 7 HOUR) AS last_waktu,

    TIMESTAMPDIFF(SECOND, MAX(waktu), UTC_TIMESTAMP()) AS selisih_detik

FROM data_sensor
";

$result = $conn->query($query);

$row = $result->fetch_assoc();

echo $row['status'];

// DEBUG:
// echo 'Status: '.$row['status'].' | Last: '.$row['last_waktu'].' | Selisih: '.$row['selisih_detik'].' detik';

$conn->close();

?>