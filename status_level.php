<?php

include_once 'koneksi.php';

// ======================================================
// AMBIL DATA TERBARU
// ======================================================
if (!function_exists('get_latest_data')) {

    function get_latest_data() {

        global $conn;

        $query = "
            SELECT suhu, kelembapan, metana, co2
            FROM data_sensor
            ORDER BY id DESC
            LIMIT 1
        ";

        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {

            return $result->fetch_assoc();

        } else {

            return [
                'suhu' => 0,
                'kelembapan' => 0,
                'metana' => 0,
                'co2' => 0
            ];
        }
    }
}

// ======================================================
// STATUS LEVEL
// ======================================================
if (!function_exists('get_status')) {

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
}

// ======================================================
// INDIKATOR WARNA
// ======================================================
if (!function_exists('render_indicator')) {

    function render_indicator($label, $value, $type) {

        $status = get_status($type, $value);

        return "
            <div class='d-flex align-items-center mx-3'>
                <div class='spinner-grow text-$status'
                     role='status'
                     style='width:0.9rem; height:0.9rem; margin-right:6px;'>

                    <span class='visually-hidden'>$label</span>

                </div>

                <small class='text-white'>$label</small>
            </div>
        ";
    }
}

// ======================================================
// NOTIFIKASI GLOBAL
// ======================================================
if (!function_exists('get_global_notification')) {

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
                        round($s['value'], 2)

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

            'message' => 'Kondisi udara masih normal'
        ];
    }
}

// ======================================================
// DATA TERBARU + NOTIFIKASI
// ======================================================
$dataTerbaru = get_latest_data();

$notifikasi = get_global_notification($dataTerbaru);

?>