<?php
session_start();
require_once '../layout/config.php';
require_once '../layout/functions.php';

(new Auth())->cekPengurus();

$transaksi = new Transaksi();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    if ($aksi === 'tambah') {
        $ok = $transaksi->tambah(
            Helper::sanitize($_POST['jenis']       ?? ''),
            Helper::sanitize($_POST['kategori']    ?? ''),
            (float)($_POST['nominal']  ?? 0),
            Helper::sanitize($_POST['keterangan']  ?? ''),
            Helper::sanitize($_POST['tanggal']     ?? date('Y-m-d')),
            (int)$_SESSION['user_id']
        );
        Helper::flashMessage($ok ? 'success' : 'danger',
            $ok ? 'Transaksi berhasil dicatat.' : 'Gagal menyimpan transaksi.');
    } elseif ($aksi === 'hapus') {
        $transaksi->hapus((int)($_POST['id'] ?? 0));
        Helper::flashMessage('success', 'Transaksi dihapus.');
    }
    Helper::redirect(BASE_URL . 'pengurus/transaksi.php');
}

$fb   = (int)($_GET['bulan'] ?? date('n'));
$fy   = (int)($_GET['tahun'] ?? date('Y'));
$fj   = Helper::sanitize($_GET['jenis'] ?? '');
$data = $transaksi->getAll($fb, $fy, $fj);

$totalPemasukan   = array_sum(array_map(fn($t) => $t['jenis'] === 'pemasukan'   ? $t['nominal'] : 0, $data));
$totalPengeluaran = array_sum(array_map(fn($t) => $t['jenis'] === 'pengeluaran' ? $t['nominal'] : 0, $data));

include '../layout/header.php';
?>
<h2 class="page-title">Transaksi Keuangan</h2>
<?php Helper::showFlash(); ?>

<!-- Form Tambah -->
<div class="card">
  <div class="card-title">Catat Transaksi Baru</div>
  <form method="POST">
    <input type="hidden" name="aksi" value="tambah">
    <div class="form-row">
      <div class="form-group">
        <label>Jenis</label>
        <select name="jenis" required>
          <option value="pemasukan">Pemasukan</option>
          <option value="pengeluaran">Pengeluaran</option>
        </select>
      </div>
      <div class="form-group">
        <label>Kategori</label>
        <input type="text" name="kategori" placeholder="Contoh: Infaq, Listrik, Makan..." required>
      </div>
      <div class="form-group">
        <label>Nominal (Rp)</label>
        <input type="number" name="nominal" required min="0">
      </div>
      <div class="form-group">
        <label>Tanggal</label>
        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="form-group">
        <label>Keterangan</label>
        <input type="text" name="keterangan" required placeholder="Keterangan singkat transaksi">
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
  </form>
</div>

<!-- Filter -->
<div class="card">
  <form method="GET" class="form-row" style="align-items:flex-end">
    <div class="form-group">
      <label>Bulan</label>
      <select name="bulan">
        <?php for ($i = 1; $i <= 12; $i++): ?>
          <option value="<?= $i ?>" <?= $i == $fb ? 'selected' : '' ?>><?= Helper::namaBulan($i) ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Tahun</label>
      <input type="number" name="tahun" value="<?= $fy ?>" min="2020" max="2099">
    </div>
    <div class="form-group">
      <label>Jenis</label>
      <select name="jenis">
        <option value="">Semua</option>
        <option value="pemasukan"   <?= $fj === 'pemasukan'   ? 'selected' : '' ?>>Pemasukan</option>
        <option value="pengeluaran" <?= $fj === 'pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
      </select>
    </div>
    <div class="form-group">
      <button type="submit" class="btn btn-primary">Tampilkan</button>
    </div>
  </form>

  <!-- Ringkasan periode -->
  <div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap">
    <div style="background:#d5f5e3;padding:12px 20px;border-radius:8px;font-size:14px">
      <span style="color:#1e8449">⬆ Pemasukan: <strong><?= Helper::formatRupiah($totalPemasukan) ?></strong></span>
    </div>
    <div style="background:#fadbd8;padding:12px 20px;border-radius:8px;font-size:14px">
      <span style="color:#a93226">⬇ Pengeluaran: <strong><?= Helper::formatRupiah($totalPengeluaran) ?></strong></span>
    </div>
    <div style="background:#d6eaf8;padding:12px 20px;border-radius:8px;font-size:14px">
      <span style="color:#1a5276">= Saldo: <strong><?= Helper::formatRupiah($totalPemasukan - $totalPengeluaran) ?></strong></span>
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <tr><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Keterangan</th><th>Nominal</th><th>Dicatat Oleh</th><th>Aksi</th></tr>
      <?php foreach ($data as $tr): ?>
      <tr>
        <td><?= htmlspecialchars($tr['tanggal']) ?></td>
        <td>
          <?= $tr['jenis'] === 'pemasukan'
              ? '<span class="badge badge-success">Pemasukan</span>'
              : '<span class="badge badge-danger">Pengeluaran</span>' ?>
        </td>
        <td><?= htmlspecialchars($tr['kategori']) ?></td>
        <td><?= htmlspecialchars($tr['keterangan']) ?></td>
        <td><?= Helper::formatRupiah($tr['nominal']) ?></td>
        <td><?= htmlspecialchars($tr['nama']) ?></td>
        <td>
          <form method="POST" style="display:inline">
            <input type="hidden" name="aksi" value="hapus">
            <input type="hidden" name="id"   value="<?= $tr['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm"
                    onclick="return confirm('Hapus transaksi ini?')">Hapus</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($data)): ?>
        <tr><td colspan="7" style="color:#999;text-align:center">Tidak ada data transaksi.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>

<?php include '../layout/footer.php'; ?>
