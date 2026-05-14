<?php
session_start();
require_once 'layout/config.php';
require_once 'layout/functions.php';

$auth = new Auth();

if (!empty($_SESSION['user_id'])) {
    Helper::redirect(BASE_URL . ($_SESSION['role'] === 'pengurus' ? 'pengurus/dashboard.php' : 'santri/dashboard.php'));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = Helper::sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        if ($auth->login($username, $password)) {
            Helper::redirect(BASE_URL . ($_SESSION['role'] === 'pengurus' ? 'pengurus/dashboard.php' : 'santri/dashboard.php'));
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Isi username dan password.';
    }
}

$pengaturan  = (new Pengaturan())->getAll();
$nama_pondok = $pengaturan['nama_pondok'] ?? 'Bendahara Pondok';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($nama_pondok) ?> — Login</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Arial,sans-serif;background:linear-gradient(135deg,#1a5276 0%,#2980b9 60%,#5dade2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center}
.login-wrap{width:100%;max-width:420px;padding:16px}
.login-card{background:#fff;border-radius:16px;padding:40px 36px;box-shadow:0 12px 40px rgba(0,0,0,.2)}
.login-icon{text-align:center;margin-bottom:8px;font-size:48px}
h1{text-align:center;font-size:22px;color:#1a5276;margin-bottom:4px}
.subtitle{text-align:center;color:#7f8c8d;font-size:13px;margin-bottom:28px}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:13px;font-weight:600;color:#555;margin-bottom:5px}
.form-group input{width:100%;padding:11px 14px;border:1.5px solid #ddd;border-radius:8px;font-size:15px;transition:border .2s}
.form-group input:focus{outline:none;border-color:#2980b9;box-shadow:0 0 0 3px rgba(41,128,185,.12)}
.btn-login{width:100%;padding:12px;background:linear-gradient(135deg,#1a5276,#2980b9);color:#fff;border:none;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer;margin-top:8px;transition:opacity .2s}
.btn-login:hover{opacity:.9}
.error{background:#fadbd8;color:#a93226;padding:10px 14px;border-radius:8px;font-size:14px;margin-bottom:16px;border:1px solid #f5b7b1}
</style>
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-icon">🕌</div>
    <h1><?= htmlspecialchars($nama_pondok) ?></h1>
    <p class="subtitle">Sistem Bendahara Online</p>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Masukkan username" autofocus required
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password" required>
      </div>
      <button class="btn-login" type="submit">Masuk</button>
    </form>
  </div>
</div>
</body>
</html>
