<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "monitoring_tpa";

include 'koneksi.php';
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

function get_latest_data() {
    global $conn;
    $query = "SELECT suhu, kelembapan, metana, co2 FROM data_sensor ORDER BY id DESC LIMIT 1";
    $result = $conn->query($query);
    return $result->fetch_assoc();
}

function get_status($type, $value) {
    switch ($type) {
        case 'suhu':
            if ($value < 28) return 'success';   // hijau aman
            elseif ($value >= 29 && $value <= 38) return 'warning'; // kuning sedang
            else return 'danger';                // merah bahaya
        case 'kelembapan':
            if ($value < 50) return 'success';
            elseif ($value >= 51 && $value <= 80) return 'warning';
            else return 'danger';
        case 'metana':
            if ($value < 1000) return 'success';
            elseif ($value >= 1000 && $value <= 1500) return 'warning';
            else return 'danger';
        case 'co2':
            if ($value < 1000) return 'success';
            elseif ($value >= 1000 && $value <= 1500) return 'warning';
            else return 'danger';
        default:
            return 'secondary';
    }
}

function render_indicator($label, $value, $type) {
    $status = get_status($type, $value);
    return "
        <div class='d-flex align-items-center mx-3'>
            <div class='spinner-grow text-$status' role='status' style='width:0.9rem; height:0.9rem; margin-right:6px;'>
                <span class='visually-hidden'>$label</span>
            </div>
            <small class='text-white'>$label</small>
        </div>
    ";
}
?>
