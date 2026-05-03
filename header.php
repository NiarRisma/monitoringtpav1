<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$halamanAktif = basename($_SERVER['PHP_SELF']);
include 'status_level.php';
$data = get_latest_data();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Monitoring TPA Indramayu</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <script src="assets/js/jquery-3.6.0.min.js"></script>
  <script src="assets/js/chart.js"></script>
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <style>
    body { background-color: #f8f9fa; }
    .navbar-brand { font-weight: bold; }
    html, body {
      height: 100%;
    }
    body {
      display: flex;
      flex-direction: column;
    }
    .content-wrapper {
      flex: 1;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid d-flex align-items-center">
    
    <!-- Brand -->
    <a class="navbar-brand me-auto" href="index.php">Monitoring TPA</a>
    
    <!-- Indikator di tengah -->
    <div class="position-absolute top-50 start-50 translate-middle d-flex">
    <?= render_indicator("Suhu", $data['suhu'], "suhu"); ?>
    <?= render_indicator("Kelembapan", $data['kelembapan'], "kelembapan"); ?>
    <?= render_indicator("Metana", $data['metana'], "metana"); ?>
    <?= render_indicator("CO₂", $data['co2'], "co2"); ?>
    </div>

    <!-- Modal Bootstrap untuk Peringatan -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-danger text-white">
        <div class="modal-header">
          <h5 class="modal-title" id="alertModalLabel">⚠️ Peringatan Keselamatan</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="alertMessage">
          <!-- Pesan bahaya akan ditampilkan di sini -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Mengerti</button>
        </div>
      </div>
    </div>
  </div>

    <!-- Menu kanan -->
    <div class="collapse navbar-collapse ms-auto" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if ($halamanAktif !== 'index.php'): ?>
          <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
        <?php endif; ?>

        <?php if (isset($_SESSION['login']) && $_SESSION['role'] == 'admin'): ?>
          <?php if ($halamanAktif !== 'admin.php'): ?>
            <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
        <?php endif; ?>
      </ul>

      <div class="d-flex align-items-center ms-3">
        <span id="alat-status" class="badge rounded-pill bg-secondary d-flex align-items-center px-3 py-2">
          <span class="status-dot border border-white me-2"></span>
          <span>Checking...</span>
        </span>
      </div>
<style>
  .status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    background-color: gray;
    position: relative;
  }

  .status-dot::after {
    content: "";
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    border-radius: 50%;
    background: inherit;
    animation: pulse 1.5s infinite;
    opacity: 0.6;
  }

  @keyframes pulse {
    0% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.6); opacity: 0; }
    100% { transform: scale(1); opacity: 0; }
  }
</style>
</div>
</div>
</nav>
<script>
function updateStatus() {
    fetch('status_alat.php')
        .then(response => response.text())
        .then(status => {
            let badge = document.getElementById("alat-status");
            let dot = badge.querySelector(".status-dot");
            let text = badge.querySelector("span + span");

            if (status.trim() === "online") {
                badge.className = "badge rounded-pill bg-success d-flex align-items-center px-3 py-2";
                dot.style.backgroundColor = "limegreen";
                text.textContent = "Alat Online";
            } else {
                badge.className = "badge rounded-pill bg-danger d-flex align-items-center px-3 py-2";
                dot.style.backgroundColor = "red";
                text.textContent = "Alat Offline";
            }
        })
        .catch(err => console.error("Gagal cek status:", err));
}

updateStatus();
setInterval(updateStatus, 5000);

function checkDangerStatus() {
    // Ambil daftar bahaya yang sudah pernah ditampilkan
    let shownAlerts = JSON.parse(sessionStorage.getItem("shownAlerts") || "[]");
    let bahayaList = [];

    document.querySelectorAll(".spinner-grow").forEach(indikator => {
        if (indikator.classList.contains("text-danger")) {
            let label = indikator.parentElement.querySelector("small").textContent.trim();
            // hanya push kalau belum pernah ditampilkan
            if (!shownAlerts.includes(label)) {
                bahayaList.push(label);
            }
        }
    });

    if (bahayaList.length > 0) {
        let pesan = "";
        bahayaList.forEach(item => {
            if (item.includes("Suhu")) {
                pesan += "📌 Suhu saat ini berada pada level kritis dan berpotensi membahayakan sistem.<br>";
            }
            if (item.includes("Kelembapan")) {
                pesan += "📌 Tingkat kelembapan berada di zona berbahaya dan dapat memicu gangguan.<br>";
            }
            if (item.includes("Metana")) {
                pesan += "📌 Kadar gas Metana telah melampaui ambang batas aman. Segera lakukan langkah pencegahan.<br>";
            }
            if (item.includes("CO₂")) {
                pesan += "📌 Konsentrasi CO₂ berada pada level berbahaya, berpotensi mengganggu kesehatan.<br>";
            }
            // tandai indikator ini sudah pernah ditampilkan
            if (!shownAlerts.includes(item)) {
                shownAlerts.push(item);
            }
        });

        document.getElementById("alertMessage").innerHTML = pesan;

        let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById("alertModal"));
        modal.show();

        // simpan kembali daftar indikator yang sudah ditampilkan
        sessionStorage.setItem("shownAlerts", JSON.stringify(shownAlerts));
    }
}

document.addEventListener("DOMContentLoaded", function() {
    checkDangerStatus();
    setInterval(checkDangerStatus, 3000);
});
</script>

<div class="container mt-4">
