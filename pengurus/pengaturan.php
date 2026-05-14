<?php
session_start();
require_once '../layout/config.php';
require_once '../layout/functions.php';

(new Auth())->cekPengurus();

$pengaturan = new Pengaturan();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['nama_pondok', 'nominal_bulanan', 'nominal_daftar_ulang', 'hari_generate_tagihan'] as $key) {
        if (isset($_POST[$key])) {
            $pengaturan->simpan($key, Helper::sanitize($_POST[$key]));
        }
    }
    Helper::flashMessage('success', 'Pengaturan berhasil disimpan.');
    Helper::redirect(BASE_URL . 'pengurus/pengaturan.php');
}

$p = $pengaturan->getAll();
include '../layout/header.php';
?>
<h2 class="page-title">Pengaturan Sistem</h2>
<?php Helper::showFlash(); ?>

<div class="card">
  <form method="POST">
    <div class="form-row">
      <div class="form-group">
        <label>Nama Pondok</label>
        <input type="text" name="nama_pondok" value="<?= htmlspecialchars($p['nama_pondok'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Nominal Tagihan Bulanan Default (Rp)</label>
        <input type="number" name="nominal_bulanan" value="<?= htmlspecialchars($p['nominal_bulanan'] ?? '200000') ?>" required>
      </div>
      <div class="form-group">
        <label>Nominal Daftar Ulang Default (Rp)</label>
        <input type="number" name="nominal_daftar_ulang" value="<?= htmlspecialchars($p['nominal_daftar_ulang'] ?? '500000') ?>" required>
      </div>
      <div class="form-group">
        <label>Tanggal Auto-Generate Tagihan Bulanan (1–28)</label>
        <input type="number" name="hari_generate_tagihan" min="1" max="28"
               value="<?= htmlspecialchars($p['hari_generate_tagihan'] ?? '1') ?>" required>
        <small style="color:#999;font-size:12px">Tagihan bulanan akan otomatis dibuat tiap tanggal ini.</small>
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
  </form>
</div>

<?php include '../layout/footer.php'; ?>
