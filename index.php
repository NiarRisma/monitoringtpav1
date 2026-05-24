<?php 
session_start();

include 'status_level.php';

$dataTerbaru = get_latest_data();
$notifikasi = get_global_notification($dataTerbaru);
?>

<?php include 'header.php'; ?>

<div class="container-fluid mt-4 px-3">

    <!-- =========================
         JUDUL
    ========================= -->
    <div class="mb-4 text-center text-md-start">

        <h2 class="fw-bold">
            Sistem Monitoring TPA Indramayu
        </h2>

        <p class="text-muted">
            Sistem ini memantau kadar gas Metana, CO₂, suhu,
            dan kelembapan di area Tempat Pembuangan Akhir (TPA).
        </p>

    </div>

    <!-- =========================
         NOTIFIKASI STATUS
    ========================= -->
    <div class="alert alert-<?= $notifikasi['class']; ?> shadow-sm">

        <h4 class="mb-2">
            Status : <?= $notifikasi['status']; ?>
        </h4>

        <p class="mb-0">
            <?= $notifikasi['message']; ?>
        </p>

    </div>

    <!-- =========================
         CARD STATUS SENSOR
    ========================= -->
    <div class="row mb-4 g-3">

        <div class="col-6 col-md-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Suhu
                    </h6>

                    <h4>
                        <?= $dataTerbaru['suhu']; ?> °C
                    </h4>

                    <?php
                    $statusSuhu = get_status(
                        'suhu',
                        $dataTerbaru['suhu']
                    );
                    ?>

                    <span class="badge bg-<?= $statusSuhu; ?>">
                        <?= strtoupper($statusSuhu); ?>
                    </span>

                </div>

            </div>

        </div>

        <div class="col-6 col-md-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        Kelembapan
                    </h6>

                    <h4>
                        <?= $dataTerbaru['kelembapan']; ?> %
                    </h4>

                    <?php
                    $statusHum = get_status(
                        'kelembapan',
                        $dataTerbaru['kelembapan']
                    );
                    ?>

                    <span class="badge bg-<?= $statusHum; ?>">
                        <?= strtoupper($statusHum); ?>
                    </span>

                </div>

            </div>

        </div>

        <div class="col-6 col-md-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        CH₄
                    </h6>

                    <h4>
                        <?= $dataTerbaru['metana']; ?> ppm
                    </h4>

                    <?php
                    $statusCH4 = get_status(
                        'metana',
                        $dataTerbaru['metana']
                    );
                    ?>

                    <span class="badge bg-<?= $statusCH4; ?>">
                        <?= strtoupper($statusCH4); ?>
                    </span>

                </div>

            </div>

        </div>

        <div class="col-6 col-md-3">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body text-center">

                    <h6 class="text-muted">
                        CO₂
                    </h6>

                    <h4>
                        <?= $dataTerbaru['co2']; ?> ppm
                    </h4>

                    <?php
                    $statusCO2 = get_status(
                        'co2',
                        $dataTerbaru['co2']
                    );
                    ?>

                    <span class="badge bg-<?= $statusCO2; ?>">
                        <?= strtoupper($statusCO2); ?>
                    </span>

                </div>

            </div>

        </div>

    </div>

    <!-- =========================
         KONTEN
    ========================= -->
    <div class="row g-4">

        <!-- =========================
             GRAFIK
        ========================= -->
        <div class="col-12 col-lg-7">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h4 class="mb-4">
                        Visualisasi Real-Time Monitoring
                    </h4>

                    <div class="row">

                        <div class="col-12 col-md-6 mb-4">

                            <h6 class="mb-3">
                                Suhu (°C)
                            </h6>

                            <canvas id="chartSuhu"></canvas>

                        </div>

                        <div class="col-12 col-md-6 mb-4">

                            <h6 class="mb-3">
                                Kelembapan (%)
                            </h6>

                            <canvas id="chartKelembapan"></canvas>

                        </div>

                        <div class="col-12 col-md-6 mb-4">

                            <h6 class="mb-3">
                                Metana (ppm)
                            </h6>

                            <canvas id="chartMetana"></canvas>

                        </div>

                        <div class="col-12 col-md-6 mb-4">

                            <h6 class="mb-3">
                                CO₂ (ppm)
                            </h6>

                            <canvas id="chartCO2"></canvas>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- =========================
             TABEL DATA
        ========================= -->
        <div class="col-12 col-lg-5">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <h4 class="mb-3">
                        Data Terakhir
                    </h4>

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped align-middle">

                            <thead class="table-dark text-center">

                                <tr>

                                    <th>Waktu</th>
                                    <th>Suhu</th>
                                    <th>Kelembapan</th>
                                    <th>CH₄</th>
                                    <th>CO₂</th>

                                </tr>

                            </thead>

                            <tbody id="data-tabel"></tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- =========================
     LOAD DATA TABEL
========================= -->
<script>

function loadData() {

    $.get("get_data.php", function(data) {

        $("#data-tabel").html(data);

    });

}

loadData();

setInterval(loadData, 5000);

</script>

<!-- =========================
     GRAFIK REALTIME
========================= -->
<script>

let chartSuhu,
    chartKelembapan,
    chartMetana,
    chartCO2;

function buatGrafik(id, label, warna) {

    const ctx = document
        .getElementById(id)
        .getContext('2d');

    return new Chart(ctx, {

        type: 'line',

        data: {

            labels: [],

            datasets: [{

                label: label,
                data: [],
                fill: false,
                borderColor: warna,
                tension: 0.3

            }]
        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            scales: {

                x: {

                    title: {

                        display: true,
                        text: 'Waktu'

                    }

                },

                y: {

                    beginAtZero: false

                }

            }

        }

    });

}

function updateGrafik() {

    $.getJSON("grafik_data.php", function(data) {

        const waktu = data.map(
            row => row.waktu
        );

        chartSuhu.data.labels = waktu;
        chartKelembapan.data.labels = waktu;
        chartMetana.data.labels = waktu;
        chartCO2.data.labels = waktu;

        chartSuhu.data.datasets[0].data =
            data.map(row => row.suhu);

        chartKelembapan.data.datasets[0].data =
            data.map(row => row.kelembapan);

        chartMetana.data.datasets[0].data =
            data.map(row => row.metana);

        chartCO2.data.datasets[0].data =
            data.map(row => row.co2);

        chartSuhu.update();
        chartKelembapan.update();
        chartMetana.update();
        chartCO2.update();

    });

}

$(document).ready(function() {

    chartSuhu = buatGrafik(
        'chartSuhu',
        'Suhu (°C)',
        'orange'
    );

    chartKelembapan = buatGrafik(
        'chartKelembapan',
        'Kelembapan (%)',
        'blue'
    );

    chartMetana = buatGrafik(
        'chartMetana',
        'Metana (ppm)',
        'green'
    );

    chartCO2 = buatGrafik(
        'chartCO2',
        'CO₂ (ppm)',
        'red'
    );

    updateGrafik();

    setInterval(updateGrafik, 5000);

});

</script>

<?php include('footer.php'); ?>