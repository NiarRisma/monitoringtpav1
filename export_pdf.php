<?php
require __DIR__ . '/fpdf/fpdf.php';
include 'koneksi.php';

$mode = $_GET['mode'] ?? 'harian';

if ($mode === 'bulanan') {

    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');

    $judul = "LAPORAN BULANAN $bulan-$tahun";

    $sql = "
    SELECT 
        DATE(waktu) as tanggal,

        MIN(suhu) as suhu_min,
        MAX(suhu) as suhu_max,

        MIN(kelembapan) as kelembapan_min,
        MAX(kelembapan) as kelembapan_max,

        MIN(metana) as metana_min,
        MAX(metana) as metana_max,

        MIN(co2) as co2_min,
        MAX(co2) as co2_max

    FROM data_sensor

    WHERE MONTH(waktu) = '$bulan'
    AND YEAR(waktu) = '$tahun'

    GROUP BY DATE(waktu)

    ORDER BY DATE(waktu) ASC
    ";

} else {

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
// TABEL HEADER
// ==========================
if ($mode === 'bulanan') {

    $kolom = [
      ['label' => 'Tanggal', 'width' => 28],
      ['label' => 'Suhu', 'width' => 40],
      ['label' => 'Hum', 'width' => 40],
      ['label' => 'CH4', 'width' => 40],
      ['label' => 'CO2', 'width' => 40],
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

// HEADER
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

            $pdf->Cell(
                $kolom[0]['width'],
                8,
                $r['tanggal'],
                1
            );

            $pdf->Cell(
                $kolom[1]['width'],
                8,
                $r['suhu_min']." - ".$r['suhu_max']." C",
                1
            );

            $pdf->Cell(
                $kolom[2]['width'],
                8,
                $r['kelembapan_min']." - ".$r['kelembapan_max']." %",
                1
            );

            $pdf->Cell(
                $kolom[3]['width'],
                8,
                $r['metana_min']." - ".$r['metana_max']." ppm",
                1
            );

            $pdf->Cell(
                $kolom[4]['width'],
                8,
                $r['co2_min']." - ".$r['co2_max']." ppm",
                1
            );

        } else {

            $pdf->Cell(
                $kolom[0]['width'],
                8,
                $r['waktu'],
                1
            );

            $pdf->Cell(
                $kolom[1]['width'],
                8,
                $r['suhu']." C",
                1
            );

            $pdf->Cell(
                $kolom[2]['width'],
                8,
                $r['kelembapan']." %",
                1
            );

            $pdf->Cell(
                $kolom[3]['width'],
                8,
                $r['metana']." ppm",
                1
            );

            $pdf->Cell(
                $kolom[4]['width'],
                8,
                $r['co2']." ppm",
                1
            );
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