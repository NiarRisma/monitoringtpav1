<?php
require __DIR__ . '/fpdf/fpdf.php';
include 'koneksi.php';

$mode = $_GET['mode'] ?? 'harian';

// ==========================
// MODE BULANAN
// ==========================
if ($mode === 'bulanan') {

    $bulan = $_GET['bulan'] ?? date('Y-m');

    $judul = "LAPORAN BULANAN " . $bulan;

    $sql = "
    SELECT 
        DATE(waktu) as tanggal,

        CASE
            WHEN HOUR(waktu) BETWEEN 6 AND 9 THEN '06:00 - 09:00'
            WHEN HOUR(waktu) BETWEEN 10 AND 13 THEN '10:00 - 13:00'
            WHEN HOUR(waktu) BETWEEN 14 AND 17 THEN '14:00 - 17:00'
            ELSE 'Lainnya'
        END AS periode,

        MAX(suhu) as suhu_tertinggi,
        MIN(suhu) as suhu_terendah,

        MAX(kelembapan) as kelembapan_tertinggi,
        MIN(kelembapan) as kelembapan_terendah,

        MAX(metana) as metana_tertinggi,
        MIN(metana) as metana_terendah,

        MAX(co2) as co2_tertinggi,
        MIN(co2) as co2_terendah

    FROM data_sensor

    WHERE DATE_FORMAT(waktu, '%Y-%m') = '$bulan'

    GROUP BY tanggal, periode

    ORDER BY tanggal ASC
    ";

} else {

    // ==========================
    // MODE HARIAN
    // ==========================
    $judul = 'LAPORAN HARIAN';

    $sql = "
    SELECT * 
    FROM data_sensor
    WHERE DATE(waktu) = CURDATE()
    ORDER BY waktu DESC
    ";
}

$data = $conn->query($sql);

// ==========================
// INISIALISASI PDF
// ==========================
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();

// ==========================
// JUDUL
// ==========================
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,$judul,0,1,'C');

$pdf->Ln(5);

// ==========================
// HEADER TABEL
// ==========================
if ($mode === 'bulanan') {

    $kolom = [
        ['label' => 'Tanggal', 'width' => 25],
        ['label' => 'Periode', 'width' => 30],
        ['label' => 'Suhu', 'width' => 25],
        ['label' => 'Hum', 'width' => 25],
        ['label' => 'CH4', 'width' => 25],
        ['label' => 'CO2', 'width' => 25],
    ];

} else {

    $kolom = [
        ['label' => 'Waktu', 'width' => 40],
        ['label' => 'Suhu', 'width' => 30],
        ['label' => 'Hum', 'width' => 30],
        ['label' => 'CH4', 'width' => 30],
        ['label' => 'CO2', 'width' => 30],
    ];
}

$totalWidth = array_sum(array_column($kolom, 'width'));
$startX = (210 - $totalWidth) / 2;

// ==========================
// CETAK HEADER
// ==========================
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(220,220,220);

$pdf->SetX($startX);

foreach ($kolom as $k) {
    $pdf->Cell($k['width'],10,$k['label'],1,0,'C',true);
}

$pdf->Ln();

// ==========================
// ISI DATA
// ==========================
$pdf->SetFont('Arial','',9);

if ($data->num_rows > 0) {

    while($r = $data->fetch_assoc()) {

        $pdf->SetX($startX);

        if ($mode === 'bulanan') {

            $pdf->Cell(25,8,$r['tanggal'],1);
            $pdf->Cell(30,8,$r['periode'],1);

            $pdf->Cell(
                25,
                8,
                $r['suhu_terendah']."-".$r['suhu_tertinggi']." C",
                1
            );

            $pdf->Cell(
                25,
                8,
                $r['kelembapan_terendah']."-".$r['kelembapan_tertinggi']." %",
                1
            );

            $pdf->Cell(
                25,
                8,
                $r['metana_terendah']."-".$r['metana_tertinggi']." ppm",
                1
            );

            $pdf->Cell(
                25,
                8,
                $r['co2_terendah']."-".$r['co2_tertinggi']." ppm",
                1
            );

        } else {

            $pdf->Cell(40,8,$r['waktu'],1);
            $pdf->Cell(30,8,$r['suhu']." C",1);
            $pdf->Cell(30,8,$r['kelembapan']." %",1);
            $pdf->Cell(30,8,$r['metana']." ppm",1);
            $pdf->Cell(30,8,$r['co2']." ppm",1);
        }

        $pdf->Ln();
    }

} else {

    $pdf->Ln(10);

    $pdf->SetFont('Arial','B',12);

    $pdf->Cell(
        0,
        10,
        'Data not found',
        0,
        1,
        'C'
    );
}

// ==========================
// OUTPUT FILE
// ==========================
$namaFile = "laporan_" . $mode . "_" . date("Ymd") . ".pdf";

$pdf->Output('D', $namaFile);
?>