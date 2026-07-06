<?php 
session_start();

include 'status_level.php';

$dataTerbaru = get_latest_data();
$notifikasi = get_global_notification($dataTerbaru);
?>

<?php include 'header.php'; ?>

<div class="container mt-4">

    <!-- =========================
         JUDUL
    ========================= -->
    <div class="mb-4">
        <h2>Selamat Siang di Sistem Monitoring TPA Indramayu</h2>

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

    <div class="row mt-4">

        <!-- =========================
             GRAFIK
        ========================= -->
        <div class="col-md-6">

            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <h4 class="mb-4">
                        Visualisasi Real-Time Monitoring
                    </h4>

                    <div class="row">

                        <div class="col-md-6 mb-4">
                            <h5>Suhu (°C)</h5>
                            <canvas id="chartSuhu"></canvas>
                        </div>

                        <div class="col-md-6 mb-4">
                            <h5>Kelembapan (%)</h5>
                            <canvas id="chartKelembapan"></canvas>
                        </div>

                        <div class="col-md-6 mb-4">
                            <h5>Metana (ppm)</h5>
                            <canvas id="chartMetana"></canvas>
                        </div>

                        <div class="col-md-6 mb-4">
                            <h5>CO₂ (ppm)</h5>
                            <canvas id="chartCO2"></canvas>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- =========================
             TABEL DATA
        ========================= -->
        <div class="col-md-6">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h4 class="mb-3">Data Terakhir</h4>

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped">

                            <thead class="table-dark">

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
                tension: 0.1

            }]
        },

        options: {

            responsive: true,

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