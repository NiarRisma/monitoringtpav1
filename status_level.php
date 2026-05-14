<?php

include 'koneksi.php';

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// ======================================================
// AMBIL DATA TERBARU
// ======================================================
function get_latest_data() {

    global $conn;

    $query = "
        SELECT suhu, kelembapan, metana, co2
        FROM data_sensor
        ORDER BY id DESC
        LIMIT 1
    ";

    $result = $conn->query($query);

    return $result->fetch_assoc();
}

// ======================================================
// STATUS MASING-MASING SENSOR
// ======================================================
function get_status($type, $value) {

    switch ($type) {

        case 'suhu':

            if ($value < 28) {
                return 'success';
            } elseif ($value >= 29 && $value <= 38) {
                return 'warning';
            } else {
                return 'danger';
            }

        case 'kelembapan':

            if ($value < 50) {
                return 'success';
            } elseif ($value >= 51 && $value <= 80) {
                return 'warning';
            } else {
                return 'danger';
            }

        case 'metana':

            if ($value < 1000) {
                return 'success';
            } elseif ($value >= 1000 && $value <= 1500) {
                return 'warning';
            } else {
                return 'danger';
            }

        case 'co2':

            if ($value < 1000) {
                return 'success';
            } elseif ($value >= 1000 && $value <= 1500) {
                return 'warning';
            } else {
                return 'danger';
            }

        default:
            return 'secondary';
    }
}

// ======================================================
// RENDER INDIKATOR WARNA
// ======================================================
function render_indicator($label, $value, $type) {

    $status = get_status($type, $value);

    return "
        <div class='d-flex align-items-center mx-3'>

            <div
                class='spinner-grow text-$status'
                role='status'
                style='width:0.9rem; height:0.9rem; margin-right:6px;'>

                <span class='visually-hidden'>$label</span>

            </div>

            <small class='text-white'>$label</small>

        </div>
    ";
}

// ======================================================
// NOTIFIKASI GLOBAL
// ======================================================
function get_global_notification($data) {

    $sensor = [

        'metana' => [
            'label' => 'CH4',
            'value' => $data['metana']
        ],

        'co2' => [
            'label' => 'CO2',
            'value' => $data['co2']
        ],

        'suhu' => [
            'label' => 'Suhu',
            'value' => $data['suhu']
        ],

        'kelembapan' => [
            'label' => 'Kelembapan',
            'value' => $data['kelembapan']
        ]
    ];

    // ==================================================
    // PRIORITAS BAHAYA
    // ==================================================
    foreach ($sensor as $type => $s) {

        $status = get_status($type, $s['value']);

        if ($status == 'danger') {

            return [
                'class' => 'danger',
                'status' => 'BAHAYA',
                'message' =>
                    $s['label'] .
                    ' mencapai ' .
                    $s['value']
            ];
        }
    }

    // ==================================================
    // PRIORITAS WARNING
    // ==================================================
    foreach ($sensor as $type => $s) {

        $status = get_status($type, $s['value']);

        if ($status == 'warning') {

            return [
                'class' => 'warning',
                'status' => 'WASPADA',
                'message' =>
                    $s['label'] .
                    ' berada pada level waspada'
            ];
        }
    }

    // ==================================================
    // NORMAL
    // ==================================================
    return [
        'class' => 'success',
        'status' => 'AMAN',
        'message' => 'Kondisi udara normal'
    ];
}

// ======================================================
// AMBIL DATA & NOTIFIKASI
// ======================================================
$dataTerbaru = get_latest_data();

$notifikasi = get_global_notification($dataTerbaru);

?>