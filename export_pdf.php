<?php
require __DIR__ . '/fpdf/fpdf.php';
include 'koneksi.php';

$mode = $_GET['mode'] ?? 'harian';

// ======================================================
// MODE BULANAN
// ======================================================
if ($mode === 'bulanan') {

    $bulan = $_GET['bulan'] ?? date('Y-m');

    // ======================================================
    // FORMAT JUDUL BULAN INDONESIA
    // ======================================================
    list($tahun, $bulanAngka) = explode('-', $bulan);

    $namaBulan = [
        '01' => 'JANUARI',
        '02' => 'FEBRUARI',
        '03' => 'MARET',
        '04' => 'APRIL',
        '05' => 'MEI',
        '06' => 'JUNI',
        '07' => 'JULI',
        '08' => 'AGUSTUS',
        '09' => 'SEPTEMBER',
        '10' => 'OKTOBER',
        '11' => 'NOVEMBER',
        '12' => 'DESEMBER'
    ];

    $judul = "LAPORAN BULAN " . $namaBulan[$bulanAngka] . " " . $tahun;

    // ======================================================
    // AMBIL SEMUA TANGGAL
    // ======================================================
    $queryTanggal = "
        SELECT DISTINCT DATE(waktu) as tanggal
        FROM data_sensor
        WHERE DATE_FORMAT(waktu, '%Y-%m') = '$bulan'
        ORDER BY tanggal ASC
    ";

    $tanggalResult = $conn->query($queryTanggal);

} else {

    // ======================================================
    // MODE HARIAN
    // ======================================================
    $judul = 'LAPORAN HARIAN';

    $sql = "
        SELECT *
        FROM data_sensor
        WHERE DATE(waktu) = CURDATE()
        ORDER BY waktu DESC
    ";

    $data = $conn->query($sql);
}

// ======================================================
// PDF
// ======================================================
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, $judul, 0, 1, 'C');

$pdf->Ln(5);

// ======================================================
// MODE BULANAN
// ======================================================
if ($mode === 'bulanan') {

    if ($tanggalResult->num_rows > 0) {

        while ($tgl = $tanggalResult->fetch_assoc()) {

            $tanggal = $tgl['tanggal'];

            // ==========================================
            // JUDUL TANGGAL
            // ==========================================
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetFillColor(230,230,230);

            $pdf->Cell(0, 8, "Tanggal : " . $tanggal, 1, 1, 'L', true);

            // ==========================================
            // HEADER TABEL
            // ==========================================
            $pdf->SetFont('Arial', 'B', 9);

            $pdf->Cell(35,8,'Periode / Jam',1,0,'C');
            $pdf->Cell(35,8,'CO2 (ppm)',1,0,'C');
            $pdf->Cell(35,8,'CH4 (ppm)',1,0,'C');
            $pdf->Cell(35,8,'Suhu (C)',1,0,'C');
            $pdf->Cell(40,8,'Kelembapan (%)',1,1,'C');

            // ==========================================
            // DAFTAR JAM
            // ==========================================
            $jamList = [
                'PAGI' => [6,7,8,9],
                'SIANG' => [10,11,12,13],
                'SORE' => [14,15,16,17]
            ];

            $pdf->SetFont('Arial', '', 9);

            foreach ($jamList as $periode => $jamArray) {

                // ==========================================
                // BARIS PERIODE
                // ==========================================
                $pdf->SetFont('Arial', 'B', 9);

                $pdf->Cell(180,7,"PERIODE $periode",1,1,'L');

                $pdf->SetFont('Arial', '', 9);

                foreach ($jamArray as $jam) {

                    $sqlJam = "
                        SELECT
                            AVG(co2) as avg_co2,
                            AVG(metana) as avg_ch4,
                            AVG(suhu) as avg_suhu,
                            AVG(kelembapan) as avg_kelembapan
                        FROM data_sensor
                        WHERE DATE(waktu) = '$tanggal'
                        AND HOUR(waktu) = $jam
                    ";

                    $resultJam = $conn->query($sqlJam);
                    $rowJam = $resultJam->fetch_assoc();

                    $co2 = $rowJam['avg_co2']
                        ? round($rowJam['avg_co2'],2)." ppm"
                        : "-";

                    $ch4 = $rowJam['avg_ch4']
                        ? round($rowJam['avg_ch4'],2)." ppm"
                        : "-";

                    $suhu = $rowJam['avg_suhu']
                        ? round($rowJam['avg_suhu'],2)." C"
                        : "-";

                    $hum = $rowJam['avg_kelembapan']
                        ? round($rowJam['avg_kelembapan'],2)." %"
                        : "-";

                    $pdf->Cell(35,8,sprintf("%02d:00",$jam),1);
                    $pdf->Cell(35,8,$co2,1);
                    $pdf->Cell(35,8,$ch4,1);
                    $pdf->Cell(35,8,$suhu,1);
                    $pdf->Cell(40,8,$hum,1);
                    $pdf->Ln();
                }
            }

            // ==========================================
            // MIN MAX HARIAN
            // ==========================================
            $sqlMinMax = "
                SELECT
                    MIN(co2) as min_co2,
                    MAX(co2) as max_co2,

                    MIN(metana) as min_ch4,
                    MAX(metana) as max_ch4,

                    MIN(suhu) as min_suhu,
                    MAX(suhu) as max_suhu,

                    MIN(kelembapan) as min_hum,
                    MAX(kelembapan) as max_hum

                FROM data_sensor
                WHERE DATE(waktu) = '$tanggal'
            ";

            $minmax = $conn->query($sqlMinMax)->fetch_assoc();

            $pdf->Ln(2);

            $pdf->SetFont('Arial','B',9);

            $pdf->Cell(90,8,'Keterangan',1,0,'C');
            $pdf->Cell(90,8,'Nilai',1,1,'C');

            $pdf->SetFont('Arial','',9);

            $dataMinMax = [

                ['Min CO2', $minmax['min_co2'].' ppm'],
                ['Max CO2', $minmax['max_co2'].' ppm'],

                ['Min CH4', $minmax['min_ch4'].' ppm'],
                ['Max CH4', $minmax['max_ch4'].' ppm'],

                ['Min Suhu', $minmax['min_suhu'].' C'],
                ['Max Suhu', $minmax['max_suhu'].' C'],

                ['Min Kelembapan', $minmax['min_hum'].' %'],
                ['Max Kelembapan', $minmax['max_hum'].' %'],
            ];

            foreach ($dataMinMax as $d) {

                $pdf->Cell(90,8,$d[0],1);
                $pdf->Cell(90,8,$d[1],1);
                $pdf->Ln();
            }

            $pdf->Ln(5);

            // ==========================================
            // AUTO PAGE BREAK
            // ==========================================
            if ($pdf->GetY() > 240) {
                $pdf->AddPage();
            }
        }

    } else {

        $pdf->Ln(20);

        $pdf->SetFont('Arial','B',14);

        $pdf->Cell(
            0,
            10,
            'Data not found',
            0,
            1,
            'C'
        );
    }

} else {

    // ======================================================
    // MODE HARIAN
    // ======================================================
    $pdf->SetFont('Arial','B',10);

    $pdf->Cell(40,10,'Waktu',1);
    $pdf->Cell(30,10,'Suhu',1);
    $pdf->Cell(30,10,'Kelembapan',1);
    $pdf->Cell(30,10,'CH4',1);
    $pdf->Cell(30,10,'CO2',1);

    $pdf->Ln();

    $pdf->SetFont('Arial','',9);

    if ($data->num_rows > 0) {

        while($r = $data->fetch_assoc()) {

            $pdf->Cell(40,8,$r['waktu'],1);
            $pdf->Cell(30,8,$r['suhu'].' C',1);
            $pdf->Cell(30,8,$r['kelembapan'].' %',1);
            $pdf->Cell(30,8,$r['metana'].' ppm',1);
            $pdf->Cell(30,8,$r['co2'].' ppm',1);

            $pdf->Ln();
        }

    } else {

        $pdf->Ln(20);

        $pdf->Cell(0,10,'Data not found',0,1,'C');
    }
}

// ======================================================
// OUTPUT
// ======================================================
$namaFile = "laporan_" . $mode . "_" . date("Ymd") . ".pdf";

$pdf->Output('D', $namaFile);
?>