<?php
session_start();
require_once '../layout/config.php';
require_once '../layout/functions.php';

(new Auth())->cekPengurus();

$bulan = (int)($_GET['bulan'] ?? date('n'));
$tahun = (int)($_GET['tahun'] ?? date('Y'));

$laporan         = new Laporan();
$lap             = $laporan->getLaporanKeuangan($bulan, $tahun);
$total_pemasukan = $lap['totalPemasukanTagihan'] + $lap['totalPemasukanLain'];
$saldo           = $total_pemasukan - $lap['totalPengeluaran'];

include '../layout/header.php';
?>
<h2 class="page-title">Laporan Keuangan</h2>

<div class="card">
  <form method="GET" class="form-row" style="align-items:flex-end">
    <div class="form-group">
      <label>Bulan</label>
      <select name="bulan">
        <?php for ($i = 1; $i <= 12; $i++): ?>
          <option value="<?= $i ?>" <?= $i == $bulan ? 'selected' : '' ?>><?= Helper::namaBulan($i) ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Tahun</label>
      <input type="number" name="tahun" value="<?= $tahun ?>" min="2020" max="2099">
    </div>
    <div class="form-group">
      <button type="submit" class="btn btn-primary">Tampilkan</button>
    </div>
  </form>
</div>

<div class="card">
  <div style="text-align:center;margin-bottom:20px">
    <h2 style="font-size:18px;color:#1a5276">
      Laporan Keuangan Bulan <?= Helper::namaBulan($bulan) . ' ' . $tahun ?>
    </h2>
  </div>

  <!-- Ringkasan -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px">
    <div style="background:#d5f5e3;padding:16px;border-radius:8px;text-align:center">
      <div style="font-size:13px;color:#1e8449">Total Pemasukan</div>
      <div style="font-size:20px;font-weight:700;color:#1e8449"><?= Helper::formatRupiah($total_pemasukan) ?></div>
    </div>
    <div style="background:#fadbd8;padding:16px;border-radius:8px;text-align:center">
      <div style="font-size:13px;color:#a93226">Total Pengeluaran</div>
      <div style="font-size:20px;font-weight:700;color:#a93226"><?= Helper::formatRupiah($lap['totalPengeluaran']) ?></div>
    </div>
    <div style="background:#d6eaf8;padding:16px;border-radius:8px;text-align:center">
      <div style="font-size:13px;color:#1a5276">Saldo Bersih</div>
      <div style="font-size:20px;font-weight:700;color:#1a5276"><?= Helper::formatRupiah($saldo) ?></div>
    </div>
  </div>

  <!-- Pemasukan dari tagihan -->
  <h4 style="margin-bottom:8px;color:#2c3e50">
    A. Pemasukan dari Pembayaran Santri (<?= Helper::formatRupiah($lap['totalPemasukanTagihan']) ?>)
  </h4>
  <div class="table-wrap" style="margin-bottom:20px">
    <table>
      <tr><th>Nama Santri</th><th>Kamar</th><th>Keterangan</th><th>Jenis</th><th>Nominal</th></tr>
      <?php foreach ($lap['pemasukanTagihan'] as $t): ?>
      <tr>
        <td><?= htmlspecialchars($t['nama']) ?></td>
        <td><?= htmlspecialchars($t['kamar']) ?></td>
        <td><?= htmlspecialchars($t['keterangan']) ?></td>
        <td><?= ucfirst(str_replace('_', ' ', $t['jenis'])) ?></td>
        <td><?= Helper::formatRupiah($t['nominal']) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($lap['pemasukanTagihan'])): ?>
        <tr><td colspan="5" style="color:#999;text-align:center">Tidak ada data</td></tr>
      <?php endif; ?>
    </table>
  </div>

  <!-- Transaksi lain -->
  <h4 style="margin-bottom:8px;color:#2c3e50">B. Pemasukan &amp; Pengeluaran Lainnya</h4>
  <div class="table-wrap" style="margin-bottom:20px">
    <table>
      <tr><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Keterangan</th><th>Nominal</th></tr>
      <?php foreach ($lap['transaksiLain'] as $tr): ?>
      <tr>
        <td><?= $tr['tanggal'] ?></td>
        <td><?= $tr['jenis'] === 'pemasukan'
              ? '<span class="badge badge-success">Pemasukan</span>'
              : '<span class="badge badge-danger">Pengeluaran</span>' ?></td>
        <td><?= htmlspecialchars($tr['kategori']) ?></td>
        <td><?= htmlspecialchars($tr['keterangan']) ?></td>
        <td><?= Helper::formatRupiah($tr['nominal']) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($lap['transaksiLain'])): ?>
        <tr><td colspan="5" style="color:#999;text-align:center">Tidak ada data</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>

<?php include '../layout/footer.php'; ?>
