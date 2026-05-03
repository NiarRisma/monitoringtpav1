<?php
session_start();
$pesan = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $conn = new mysqli("localhost", "root", "", "monitoring_tpa");
  $user = $_POST['username'];
  $pass = hash('sha256', $_POST['password']);

  $query = $conn->query("SELECT * FROM users WHERE username='$user' AND password='$pass'");
  if ($query->num_rows == 1) {
    $data = $query->fetch_assoc();
    $_SESSION['login'] = true;
    $_SESSION['username'] = $data['username'];
    $_SESSION['role'] = $data['role'];
    header("Location: admin.php");
    exit;
  } else {
    $pesan = "Login gagal, periksa kembali username dan password!";
  }
}
?>

<?php include 'header.php'; ?>
<div class="d-flex justify-content-center align-items-center" style="min-height: 75vh;">
  <div class="col-md-5">
    <h1 class="text-center">Selamat datang Di Website</h1>
    <h4 class="text-center">Monitoring Gas Metana dan Karbondioksida</h4>
    <h6 class="text-center">Silahkan melakukan login</h5>
    <?php if ($pesan) echo "<div class='alert alert-danger'>$pesan</div>"; ?>
    <form method="post">
      <div class="mb-3">
        <label>Username</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100">Login</button>
    </form>
  </div>
</div>
<?php include 'footer.php'; ?>

