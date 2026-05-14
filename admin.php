<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
  header("Location: login.php");
  exit;
}
?>

<?php include 'header.php'; ?>

<h2 class="mb-4">Dashboard Admin</h2>

<!-- =========================
     EXPORT PDF
========================= -->
<div class="card mb-4 shadow-sm">
  <div class="card-body">

    <h5 class="mb-3">Export Laporan PDF</h5>

    <div class="mb-3">
      <a href="export_pdf.php?mode=harian" 
         class="btn btn-danger">
         Download Data Harian
      </a>
    </div>

    <!-- FORM BULANAN -->
    <form action="export_pdf.php" method="GET" target="_blank" class="row g-2">

      <input type="hidden" name="mode" value="bulanan">

      <!-- PILIH BULAN -->
      <div class="col-md-4">
        <select name="bulan" class="form-select" required>

          <option value="">Pilih Bulan</option>

          <option value="1">Januari</option>
          <option value="2">Februari</option>
          <option value="3">Maret</option>
          <option value="4">April</option>
          <option value="5">Mei</option>
          <option value="6">Juni</option>
          <option value="7">Juli</option>
          <option value="8">Agustus</option>
          <option value="9">September</option>
          <option value="10">Oktober</option>
          <option value="11">November</option>
          <option value="12">Desember</option>

        </select>
      </div>

      <!-- PILIH TAHUN -->
      <div class="col-md-3">
        <select name="tahun" class="form-select" required>

          <option value="2025">2025</option>
          <option value="2026" selected>2026</option>
          <option value="2027">2027</option>

        </select>
      </div>

      <!-- BUTTON -->
      <div class="col-md-4">
        <button type="submit" class="btn btn-warning w-100">
          Download Data Bulanan
        </button>
      </div>

    </form>

  </div>
</div>

<!-- =========================
     GRAFIK
========================= -->
<div class="row">

  <div class="col-md-6 mb-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5>Suhu</h5>
        <canvas id="chartSuhu"></canvas>
      </div>
    </div>
  </div>

  <div class="col-md-6 mb-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5>Kelembapan</h5>
        <canvas id="chartKelembapan"></canvas>
      </div>
    </div>
  </div>

  <div class="col-md-6 mb-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5>Metana</h5>
        <canvas id="chartMetana"></canvas>
      </div>
    </div>
  </div>

  <div class="col-md-6 mb-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5>CO₂</h5>
        <canvas id="chartCO2"></canvas>
      </div>
    </div>
  </div>

</div>

<!-- =========================
     TABEL DATA
========================= -->
<div class="card shadow-sm mb-5">
  <div class="card-body">

    <h4 class="mb-3">Data Terkini</h4>

    <div class="table-responsive">

      <table class="table table-striped table-bordered">

        <thead class="table-dark">
          <tr>
            <th>Waktu</th>
            <th>Suhu</th>
            <th>Kelembapan</th>
            <th>Metana</th>
            <th>CO₂</th>
          </tr>
        </thead>

        <tbody id="dataAdmin"></tbody>

      </table>

    </div>

  </div>
</div>

<!-- =========================
     SCRIPT GRAFIK
========================= -->
<script>

function buatGrafik(id, label, warna) {

  const ctx = document.getElementById(id).getContext('2d');

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

// =========================
// MEMBUAT GRAFIK
// =========================
let chartSuhu = buatGrafik(
  'chartSuhu',
  'Suhu (°C)',
  'orange'
);

let chartKelembapan = buatGrafik(
  'chartKelembapan',
  'Kelembapan (%)',
  'blue'
);

let chartMetana = buatGrafik(
  'chartMetana',
  'Metana (ppm)',
  'green'
);

let chartCO2 = buatGrafik(
  'chartCO2',
  'CO₂ (ppm)',
  'red'
);

// =========================
// UPDATE GRAFIK
// =========================
function updateGrafikAdmin() {

  $.getJSON("grafik_data.php", function(data) {

    const waktu = data.map(r => r.waktu);

    chartSuhu.data.labels = waktu;
    chartKelembapan.data.labels = waktu;
    chartMetana.data.labels = waktu;
    chartCO2.data.labels = waktu;

    chartSuhu.data.datasets[0].data =
      data.map(r => r.suhu);

    chartKelembapan.data.datasets[0].data =
      data.map(r => r.kelembapan);

    chartMetana.data.datasets[0].data =
      data.map(r => r.metana);

    chartCO2.data.datasets[0].data =
      data.map(r => r.co2);

    chartSuhu.update();
    chartKelembapan.update();
    chartMetana.update();
    chartCO2.update();

  });
}

// =========================
// LOAD TABEL
// =========================
function loadTabelAdmin() {

  $.get("get_data.php?limit=20", function(data) {

    $("#dataAdmin").html(data);

  });

}

// =========================
// AUTO REFRESH
// =========================
updateGrafikAdmin();
loadTabelAdmin();

setInterval(() => {

  updateGrafikAdmin();
  loadTabelAdmin();

}, 5000);

</script>

<?php include 'footer.php'; ?>