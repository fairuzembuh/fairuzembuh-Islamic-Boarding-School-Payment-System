<?php
session_start();
require_once '../layout/config.php';
require_once '../layout/functions.php';

(new Auth())->cekSantri();
(new Tagihan())->autoGenerateBulanan();

$userId    = (int)$_SESSION['user_id'];
$tagihan   = (new Tagihan())->getTagihanBelumBayar($userId);
$riwayat   = (new Pembayaran())->getRiwayat($userId);

include '../layout/header.php';
?>
<h2 class="page-title">Dashboard — <?= htmlspecialchars($_SESSION['nama']) ?></h2>
<?php Helper::showFlash(); ?>

<?php if (!empty($tagihan)): ?>
<div class="notif-bar">
  ⚠️ Kamu memiliki <strong><?= count($tagihan) ?> tagihan</strong> yang belum lunas. Segera lakukan pembayaran!
</div>
<?php endif; ?>

<!-- Tagihan Aktif -->
<div class="card">
  <div class="card-title">Tagihan Aktif</div>
  <?php if (empty($tagihan)): ?>
    <p style="color:#27ae60;font-weight:600">✅ Semua tagihan sudah lunas!</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <tr><th>Keterangan</th><th>Jenis</th><th>Nominal</th><th>Status</th><th>Aksi</th></tr>
      <?php foreach ($tagihan as $t): ?>
      <tr>
        <td><?= htmlspecialchars($t['keterangan']) ?></td>
        <td><?= ucfirst(str_replace('_', ' ', $t['jenis'])) ?></td>
        <td><?= Helper::formatRupiah($t['nominal']) ?></td>
        <td><?= Helper::labelStatus($t['status']) ?></td>
        <td>
          <?php if ($t['status'] === 'belum'): ?>
            <a href="bayar.php?tagihan_id=<?= $t['id'] ?>" class="btn btn-primary btn-sm">Bayar</a>
          <?php else: ?>
            <span style="color:#999;font-size:12px">Menunggu verifikasi</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- Riwayat 5 Terakhir -->
<div class="card">
  <div class="card-title">Riwayat Pembayaran Terakhir</div>
  <?php if (empty($riwayat)): ?>
    <p style="color:#999">Belum ada riwayat pembayaran.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <tr><th>Keterangan</th><th>Nominal</th><th>Tgl Bayar</th><th>Status</th></tr>
      <?php foreach (array_slice($riwayat, 0, 5) as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['ket_tagihan']) ?></td>
        <td><?= Helper::formatRupiah($r['nominal']) ?></td>
        <td><?= $r['tanggal_bayar'] ?></td>
        <td><?= Helper::labelStatus($r['status']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <a href="riwayat.php" class="btn btn-secondary btn-sm" style="margin-top:12px">Lihat semua riwayat</a>
  <?php endif; ?>
</div>

<?php include '../layout/footer.php'; ?>
