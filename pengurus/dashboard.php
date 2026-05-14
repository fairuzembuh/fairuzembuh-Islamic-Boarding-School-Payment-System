<?php
session_start();
require_once '../layout/config.php';
require_once '../layout/functions.php';

(new Auth())->cekPengurus();
(new Tagihan())->autoGenerateBulanan();

$laporan  = new Laporan();
$data     = $laporan->getDashboardPengurus();
$pending  = (new Pembayaran())->getMenunggu();

include '../layout/header.php';
?>
<h2 class="page-title">Dashboard Pengurus — <?= Helper::namaBulan((int)date('n')) . ' ' . date('Y') ?></h2>
<?php Helper::showFlash(); ?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-num"><?= $data['total_santri'] ?></div>
    <div class="stat-label">Total Santri Aktif</div>
  </div>
  <div class="stat-card green">
    <div class="stat-num"><?= $data['sudah_bayar'] ?></div>
    <div class="stat-label">Sudah Bayar Bulan Ini</div>
  </div>
  <div class="stat-card red">
    <div class="stat-num"><?= $data['belum_bayar'] ?></div>
    <div class="stat-label">Belum Bayar Bulan Ini</div>
  </div>
  <div class="stat-card orange">
    <div class="stat-num"><?= $data['menunggu_konfirmasi'] ?></div>
    <div class="stat-label">Menunggu Konfirmasi</div>
  </div>
  <div class="stat-card green">
    <div class="stat-num" style="font-size:18px"><?= Helper::formatRupiah($data['pemasukan_bulan']) ?></div>
    <div class="stat-label">Pemasukan Bulan Ini</div>
  </div>
  <div class="stat-card red">
    <div class="stat-num" style="font-size:18px"><?= Helper::formatRupiah($data['pengeluaran_bulan']) ?></div>
    <div class="stat-label">Pengeluaran Bulan Ini</div>
  </div>
</div>

<?php if (!empty($pending)): ?>
<div class="card">
  <div class="card-title">Pembayaran Menunggu Konfirmasi (<?= count($pending) ?>)</div>
  <div class="table-wrap">
    <table>
      <tr><th>Santri</th><th>Kamar</th><th>Keterangan</th><th>Nominal</th><th>Tgl Bayar</th><th>Bukti</th><th>Aksi</th></tr>
      <?php foreach ($pending as $p): ?>
      <tr>
        <td><?= htmlspecialchars($p['nama']) ?></td>
        <td><?= htmlspecialchars($p['kamar']) ?></td>
        <td><?= htmlspecialchars($p['ket_tagihan']) ?></td>
        <td><?= Helper::formatRupiah($p['nominal']) ?></td>
        <td><?= $p['tanggal_bayar'] ?></td>
        <td>
          <?php if ($p['bukti_file']): ?>
            <a href="<?= BASE_URL ?>uploads/<?= htmlspecialchars($p['bukti_file']) ?>" target="_blank" class="btn btn-secondary btn-sm">Lihat</a>
          <?php else: ?><span style="color:#999">-</span><?php endif; ?>
        </td>
        <td>
          <form method="POST" action="konfirmasi.php" style="display:inline">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button name="aksi" value="diterima" class="btn btn-success btn-sm"
                    onclick="return confirm('Terima pembayaran ini?')">Terima</button>
            <button name="aksi" value="ditolak"  class="btn btn-danger btn-sm"
                    onclick="return confirm('Tolak pembayaran ini?')">Tolak</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include '../layout/footer.php'; ?>
