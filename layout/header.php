<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!class_exists('Pengaturan')) {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/functions.php';
}
$pengaturan  = (new Pengaturan())->getAll();
$nama_pondok = $pengaturan['nama_pondok'] ?? 'Bendahara Pondok';
$role        = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($nama_pondok) ?> — Sistem Bendahara</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f2f5;color:#333;min-height:100vh}
a{text-decoration:none;color:inherit}

/* NAV */
nav{background:linear-gradient(135deg,#1a5276,#2980b9);color:#fff;padding:0 24px;display:flex;align-items:center;justify-content:space-between;height:56px;box-shadow:0 2px 8px rgba(0,0,0,.2)}
.nav-brand{font-size:18px;font-weight:700;letter-spacing:.5px}
.nav-links{display:flex;gap:4px;align-items:center}
.nav-links a{color:rgba(255,255,255,.85);padding:6px 12px;border-radius:6px;font-size:14px;transition:background .2s}
.nav-links a:hover,.nav-links a.active{background:rgba(255,255,255,.2);color:#fff}
.nav-user{display:flex;align-items:center;gap:10px;font-size:14px}
.nav-user .badge-role{background:rgba(255,255,255,.2);padding:3px 10px;border-radius:20px;font-size:12px}
.btn-logout{background:rgba(231,76,60,.8);color:#fff;border:none;padding:6px 14px;border-radius:6px;cursor:pointer;font-size:13px}
.btn-logout:hover{background:#e74c3c}

/* LAYOUT */
.container{max-width:1100px;margin:0 auto;padding:24px 16px}
.page-title{font-size:22px;font-weight:700;color:#1a5276;margin-bottom:20px;padding-bottom:10px;border-bottom:2px solid #2980b9}

/* CARDS */
.card{background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.08);padding:24px;margin-bottom:20px}
.card-title{font-size:16px;font-weight:700;color:#2c3e50;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #eee}

/* STATS */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px}
.stat-card{background:#fff;border-radius:10px;padding:20px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.08);border-top:4px solid #2980b9}
.stat-card.green{border-top-color:#27ae60}
.stat-card.red{border-top-color:#e74c3c}
.stat-card.orange{border-top-color:#e67e22}
.stat-card.purple{border-top-color:#8e44ad}
.stat-num{font-size:28px;font-weight:700;color:#2c3e50}
.stat-label{font-size:13px;color:#7f8c8d;margin-top:4px}

/* TABLES */
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:14px}
th{background:#2980b9;color:#fff;padding:10px 12px;text-align:left;font-weight:600}
td{padding:10px 12px;border-bottom:1px solid #f0f0f0}
tr:hover td{background:#f8f9fa}
tr:last-child td{border-bottom:none}

/* FORMS */
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:13px;font-weight:600;color:#555;margin-bottom:5px}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:9px 12px;border:1px solid #ddd;border-radius:7px;font-size:14px;transition:border .2s}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:#2980b9;box-shadow:0 0 0 3px rgba(41,128,185,.1)}
.form-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px}

/* BUTTONS */
.btn{display:inline-block;padding:9px 18px;border-radius:7px;font-size:14px;font-weight:600;cursor:pointer;border:none;transition:all .2s}
.btn-primary{background:#2980b9;color:#fff}.btn-primary:hover{background:#1a6fa8}
.btn-success{background:#27ae60;color:#fff}.btn-success:hover{background:#219a52}
.btn-danger{background:#e74c3c;color:#fff}.btn-danger:hover{background:#c0392b}
.btn-aktifkan{background:#1e8449;color:#fff}.btn-aktifkan:hover{background:#2c3e50}
.btn-warning{background:#e67e22;color:#fff}.btn-warning:hover{background:#d35400}
.btn-secondary{background:#95a5a6;color:#fff}.btn-secondary:hover{background:#7f8c8d}
.btn-sm{padding:5px 11px;font-size:12px}
.btn-print{background:#8e44ad;color:#fff}.btn-print:hover{background:#7d3c98}

/* BADGES */
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600}
.badge-success{background:#d5f5e3;color:#1e8449}
.badge-danger{background:#fadbd8;color:#a93226}
.badge-warning{background:#fdebd0;color:#b7770d}
.badge-info{background:#d6eaf8;color:#1a5276}

/* ALERTS */
.alert{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:14px}
.alert-success{background:#d5f5e3;color:#1e8449;border:1px solid #a9dfbf}
.alert-danger{background:#fadbd8;color:#a93226;border:1px solid #f5b7b1}
.alert-warning{background:#fdebd0;color:#b7770d;border:1px solid #f9e4b7}

/* NOTIF badge */
.notif-bar{background:#fff3cd;border:1px solid #ffc107;color:#856404;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:14px}

@media print{nav,#no-print{display:none}body{background:#fff}.card{box-shadow:none;border:1px solid #ddd}}
@media(max-width:600px){.nav-links{display:none}.form-row{grid-template-columns:1fr}}
</style>
</head>
<body>
<nav>
  <div class="nav-brand"><?= htmlspecialchars($nama_pondok) ?></div>
  <div class="nav-links">
    <?php if ($role === 'santri'): ?>
      <a href="<?= BASE_URL ?>santri/dashboard.php">Dashboard</a>
      <a href="<?= BASE_URL ?>santri/tagihan.php">Tagihan</a>
      <a href="<?= BASE_URL ?>santri/bayar.php">Bayar</a>
      <a href="<?= BASE_URL ?>santri/riwayat.php">Riwayat</a>
    <?php elseif ($role === 'pengurus'): ?>
      <a href="<?= BASE_URL ?>pengurus/dashboard.php">Dashboard</a>
      <a href="<?= BASE_URL ?>pengurus/tagihan.php">Tagihan</a>
      <a href="<?= BASE_URL ?>pengurus/konfirmasi.php">Konfirmasi</a>
      <a href="<?= BASE_URL ?>pengurus/transaksi.php">Transaksi</a>
      <a href="<?= BASE_URL ?>pengurus/laporan.php">Laporan</a>
      <a href="<?= BASE_URL ?>pengurus/santri.php">Santri</a>
    <?php endif; ?>
  </div>
  <div class="nav-user">
    <span><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></span>
    <span class="badge-role"><?= $role ?></span>
    <form method="POST" action="<?= BASE_URL ?>logout.php" style="margin:0">
      <button class="btn-logout" type="submit">Keluar</button>
    </form>
  </div>
</nav>
<div class="container">
