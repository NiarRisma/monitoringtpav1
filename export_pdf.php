<?php
require __DIR__ . '/fpdf/fpdf.php';

include 'koneksi.php';

$mode = $_GET['mode'] ?? 'harian';

$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();

$pdf->SetFont('Arial','B',14);


// ======================================================
// LAPORAN HARIAN
// ======================================================
if ($mode == 'harian') {

    $judul = 'LAPORAN HARIAN MONITORING TPA';

    $sql = "
    SELECT *
    FROM data_sensor
    WHERE DATE(waktu) = CURDATE()
    ORDER BY waktu DESC
    ";

    $data = $conn->query($sql);

    $pdf->Cell(0,10,$judul,0,1,'C');
    $pdf->Ln(5);

    $kolom = [
        ['label'=>'Waktu','width'=>45],
        ['label'=>'Suhu (°C)','width'=>35],
        ['label'=>'Kelembapan (%)','width'=>45],
        ['label'=>'CH4 (ppm)','width'=>35],
        ['label'=>'CO2 (ppm)','width'=>35],
    ];

    $pdf->SetFont('Arial','B',10);

    foreach($kolom as $k){
        $pdf->Cell($k['width'],10,$k['label'],1,0,'C');
    }

    $pdf->Ln();

    $pdf->SetFont('Arial','',10);

    if($data->num_rows > 0){

        while($r = $data->fetch_assoc()){

            $pdf->Cell(45,8,$r['waktu'],1);
            $pdf->Cell(35,8,$r['suhu'].' °C',1);
            $pdf->Cell(45,8,$r['kelembapan'].' %',1);
            $pdf->Cell(35,8,$r['metana'].' ppm',1);
            $pdf->Cell(35,8,$r['co2'].' ppm',1);

            $pdf->Ln();
        }

    } else {

        $pdf->Cell(195,10,'Data not found',1,1,'C');

    }

}


// ======================================================
// LAPORAN BULANAN
// ======================================================
else if ($mode == 'bulanan') {

    $bulan = $_GET['bulan'] ?? date('m');
    $tahun = $_GET['tahun'] ?? date('Y');

    $namaBulan = date('F', mktime(0,0,0,$bulan,1,$tahun));

    $judul = "LAPORAN BULANAN MONITORING TPA";
    $subjudul = strtoupper($namaBulan)." ".$tahun;

    $pdf->Cell(0,10,$judul,0,1,'C');
    $pdf->Cell(0,8,$subjudul,0,1,'C');

    $pdf->Ln(5);

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

    $data = $conn->query($sql);

    $pdf->SetFont('Arial','B',9);

    $kolom = [
        ['label'=>'Tanggal','width'=>28],
        ['label'=>'Suhu Min','width'=>28],
        ['label'=>'Suhu Max','width'=>28],
        ['label'=>'Hum Min','width'=>28],
        ['label'=>'Hum Max','width'=>28],
        ['label'=>'CH4 Min','width'=>28],
        ['label'=>'CH4 Max','width'=>28],
        ['label'=>'CO2 Min','width'=>28],
        ['label'=>'CO2 Max','width'=>28],
    ];

    foreach($kolom as $k){
        $pdf->Cell($k['width'],10,$k['label'],1,0,'C');
    }

    $pdf->Ln();

    $pdf->SetFont('Arial','',9);

    if($data->num_rows > 0){

        while($r = $data->fetch_assoc()){

            $pdf->Cell(28,8,$r['tanggal'],1);

            $pdf->Cell(28,8,$r['suhu_min'].' °C',1);
            $pdf->Cell(28,8,$r['suhu_max'].' °C',1);

            $pdf->Cell(28,8,$r['kelembapan_min'].' %',1);
            $pdf->Cell(28,8,$r['kelembapan_max'].' %',1);

            $pdf->Cell(28,8,$r['metana_min'].' ppm',1);
            $pdf->Cell(28,8,$r['metana_max'].' ppm',1);

            $pdf->Cell(28,8,$r['co2_min'].' ppm',1);
            $pdf->Cell(28,8,$r['co2_max'].' ppm',1);

            $pdf->Ln();
        }

    } else {

        $pdf->Cell(252,12,'Data not found',1,1,'C');

    }

}


$namaFile = "laporan_".$mode."_".date("Ymd_His").".pdf";

$pdf->Output('D', $namaFile);

?>