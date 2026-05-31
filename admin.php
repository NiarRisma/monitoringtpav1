<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
  header("Location: login.php");
  exit;
}
?>

<?php include 'header.php'; ?>
<h2 class="mb-4">Dashboard Admin</h2>

<div class="mb-4">
  <a href="export_pdf.php?mode=harian" class="btn btn-danger me-2">Download Data Harian</a>
  <a href="export_pdf.php?mode=bulanan" class="btn btn-warning">Download Data Bulanan</a>
</div>

<div class="row">
  <div class="col-md-6 mb-4"><h5>Suhu</h5><canvas id="chartSuhu"></canvas></div>
  <div class="col-md-6 mb-4"><h5>Kelembapan</h5><canvas id="chartKelembapan"></canvas></div>
  <div class="col-md-6 mb-4"><h5>Metana</h5><canvas id="chartMetana"></canvas></div>
  <div class="col-md-6 mb-4"><h5>CO₂</h5><canvas id="chartCO2"></canvas></div>
</div>

<h4>Data Terkini</h4>
<table class="table table-striped table-bordered">
  <thead>
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
        x: { title: { display: true, text: 'Waktu' }},
        y: { beginAtZero: false }
      }
    }
  });
}

let chartSuhu = buatGrafik('chartSuhu', 'Suhu (°C)', 'orange');
let chartKelembapan = buatGrafik('chartKelembapan', 'Kelembapan (%)', 'blue');
let chartMetana = buatGrafik('chartMetana', 'Metana (ADC)', 'green');
let chartCO2 = buatGrafik('chartCO2', 'CO₂ (ADC)', 'red');

function updateGrafikAdmin() {
  $.getJSON("grafik_data.php", function(data) {
    const waktu = data.map(r => r.waktu);
    chartSuhu.data.labels = waktu;
    chartKelembapan.data.labels = waktu;
    chartMetana.data.labels = waktu;
    chartCO2.data.labels = waktu;

    chartSuhu.data.datasets[0].data = data.map(r => r.suhu);
    chartKelembapan.data.datasets[0].data = data.map(r => r.kelembapan);
    chartMetana.data.datasets[0].data = data.map(r => r.metana);
    chartCO2.data.datasets[0].data = data.map(r => r.co2);

    chartSuhu.update(); chartKelembapan.update(); chartMetana.update(); chartCO2.update();
  });
}

function loadTabelAdmin() {
  $.get("get_data.php?limit=20", function(data) {
    $("#dataAdmin").html(data);
  });
}

updateGrafikAdmin();
loadTabelAdmin();
setInterval(() => { updateGrafikAdmin(); loadTabelAdmin(); }, 5000);
</script>

<?php include 'footer.php'; ?>
