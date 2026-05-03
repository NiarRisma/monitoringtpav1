<?php session_start(); ?>
<?php include 'header.php'; ?>
<h2>Selamat Datang di Sistem Monitoring TPA Indramayu</h2>
<p>Sistem ini memantau kadar gas Metana, CO₂, suhu, dan kelembapan di area Tempat Pembuangan Akhir (TPA).</p>
<div class="row mt-4">
  <div class="col-md-6">
  <h2 class="mb-4">Visualisasi Real-Time Monitoring</h2>
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
        <h5>Metana (ADC)</h5>
        <canvas id="chartMetana"></canvas>
    </div>
    <div class="col-md-6 mb-4">
        <h5>CO₂ (ADC)</h5>
        <canvas id="chartCO2"></canvas>
    </div>
    </div>
  </div>
  <div class="col-md-6">
    <h4>Data Terakhir</h4>
    <table class="table table-bordered">
      <thead class="table-secondary">
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
<script>
  function loadData() {
    $.get("get_data.php", function(data) {
      $("#data-tabel").html(data);
    });
  }
  loadData();
  setInterval(loadData, 5000);
</script>
<script>
let chartSuhu, chartKelembapan, chartMetana, chartCO2;

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

function updateGrafik() {
  $.getJSON("grafik_data.php", function(data) {
    const waktu = data.map(row => row.waktu);

    const suhu = data.map(row => row.suhu);
    const kelembapan = data.map(row => row.kelembapan);
    const metana = data.map(row => row.metana);
    const co2 = data.map(row => row.co2);

    chartSuhu.data.labels = waktu;
    chartSuhu.data.datasets[0].data = suhu;
    chartSuhu.update();

    chartKelembapan.data.labels = waktu;
    chartKelembapan.data.datasets[0].data = kelembapan;
    chartKelembapan.update();

    chartMetana.data.labels = waktu;
    chartMetana.data.datasets[0].data = metana;
    chartMetana.update();

    chartCO2.data.labels = waktu;
    chartCO2.data.datasets[0].data = co2;
    chartCO2.update();
  });
}

$(document).ready(function() {
  chartSuhu = buatGrafik('chartSuhu', 'Suhu (°C)', 'orange');
  chartKelembapan = buatGrafik('chartKelembapan', 'Kelembapan (%)', 'blue');
  chartMetana = buatGrafik('chartMetana', 'Metana (ADC)', 'green');
  chartCO2 = buatGrafik('chartCO2', 'CO₂ (ADC)', 'red');

  updateGrafik(); // pertama kali
  setInterval(updateGrafik, 5000); // update tiap 5 detik
});
</script>

<?php include('footer.php'); ?>
