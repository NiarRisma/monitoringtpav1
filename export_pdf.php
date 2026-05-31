<?php
require __DIR__ . '/fpdf/fpdf.php';
include 'koneksi.php';
$mode = $_GET['mode'] ?? 'harian';

// Tentukan query dan judul berdasarkan mode
if ($mode === 'bulanan') {
    $judul = 'LAPORAN BULANAN';
    $sql = "SELECT * FROM data_sensor WHERE MONTH(waktu) = MONTH(CURDATE()) AND YEAR(waktu) = YEAR(CURDATE()) ORDER BY waktu DESC";
} else {
    $judul = 'LAPORAN HARIAN';
    $sql = "SELECT * FROM data_sensor WHERE DATE(waktu) = CURDATE() ORDER BY waktu DESC";
}

$data = $conn->query($sql);

// Inisialisasi PDF
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();

// Judul di tengah
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10, $judul, 0, 1, 'C');
$pdf->Ln(5);

// Kolom dan lebar
$kolom = [
  ['label' => 'Waktu',     'width' => 40],
  ['label' => 'Suhu (C)', 'width' => 30],
  ['label' => 'Kelembapan','width' => 30],
  ['label' => 'Metana',    'width' => 30],
  ['label' => 'CO2',      'width' => 30],
];

// Hitung total lebar kolom
$totalWidth = array_sum(array_column($kolom, 'width'));
$startX = (210 - $totalWidth) / 2; // Lebar A4 = 210mm

// Header tabel
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(220,220,220);
$pdf->SetX($startX);
foreach ($kolom as $k) {
  $pdf->Cell($k['width'],10,$k['label'],1,0,'C',true);
}
$pdf->Ln();

// Isi data
$pdf->SetFont('Arial','',10);
while($r = $data->fetch_assoc()) {
  $pdf->SetX($startX);
  $pdf->Cell($kolom[0]['width'],8,$r['waktu'],1);
  $pdf->Cell($kolom[1]['width'],8,$r['suhu'],1);
  $pdf->Cell($kolom[2]['width'],8,$r['kelembapan'],1);
  $pdf->Cell($kolom[3]['width'],8,$r['metana'],1);
  $pdf->Cell($kolom[4]['width'],8,$r['co2'],1);
  $pdf->Ln();
}

$namaFile = "laporan_" . $mode . "_" . date("Ymd") . ".pdf";
$pdf->Output('D', $namaFile);
?>
