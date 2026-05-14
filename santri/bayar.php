<?php
session_start();
require_once '../layout/config.php';
require_once '../layout/functions.php';

(new Auth())->cekSantri();

$userId     = (int)$_SESSION['user_id'];
$tagihanId  = (int)($_GET['tagihan_id'] ?? 0);
$pembayaran = new Pembayaran();
$tagObj     = new Tagihan();

// Proses POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid     = (int)($_POST['tagihan_id'] ?? 0);
    $catatan = Helper::sanitize($_POST['catatan'] ?? '');
    $bukti   = '';

    if (!empty($_FILES['bukti']['name'])) {
        $bukti = Helper::uploadBukti($_FILES['bukti']);
        if (!$bukti) {
            Helper::flashMessage('danger', 'File bukti tidak valid. Gunakan JPG, PNG, atau PDF maks 2MB.');
            Helper::redirect(BASE_URL . "santri/bayar.php?tagihan_id=$tid");
        }
    }

    if ($pembayaran->submit($tid, $userId, $bukti, $catatan)) {
        Helper::flashMessage('success', 'Pembayaran berhasil dikirim! Menunggu konfirmasi pengurus.');
        Helper::redirect(BASE_URL . 'santri/dashboard.php');
    } else {
        Helper::flashMessage('danger', 'Gagal mengirim pembayaran. Coba lagi.');
        Helper::redirect(BASE_URL . "santri/bayar.php?tagihan_id=$tid");
    }
}

// Ambil daftar tagihan belum bayar milik santri ini
$semuaTagihan = $tagObj->getTagihanBelumBayar($userId);
if ($tagihanId) {
    // Filter hanya tagihan yang diminta (status belum)
    $tagihanList = array_filter($semuaTagihan, fn($t) => (int)$t['id'] === $tagihanId);
} else {
    $tagihanList = $semuaTagihan;
}

include '../layout/header.php';
?>
<h2 class="page-title">Form Pembayaran</h2>
<?php Helper::showFlash(); ?>

<div class="card">
  <div class="card-title">Kirim Bukti Pembayaran</div>

  <?php if (empty($tagihanList)): ?>
    <p style="color:#27ae60;font-weight:600">Tidak ada tagihan yang perlu dibayar.</p>
    <a href="dashboard.php" class="btn btn-secondary" style="margin-top:12px">← Kembali</a>

  <?php else: ?>
  <form method="POST" enctype="multipart/form-data">
    <div class="form-group">
      <label>Pilih Tagihan yang Dibayar</label>
      <select name="tagihan_id" required>
        <option value="">-- Pilih tagihan --</option>
        <?php foreach ($tagihanList as $t): ?>
          <option value="<?= $t['id'] ?>" <?= (int)$t['id'] === $tagihanId ? 'selected' : '' ?>>
            <?= htmlspecialchars($t['keterangan']) ?> — <?= Helper::formatRupiah($t['nominal']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label>Upload Bukti Pembayaran</label>
      <input type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf">
      <small style="color:#999;font-size:12px">Format JPG, PNG, atau PDF. Maks 2MB.</small>
    </div>

    <div class="form-group">
      <label>Catatan <span style="font-weight:normal;color:#999">(opsional)</span></label>
      <textarea name="catatan" rows="3"
                placeholder="Contoh: Transfer via BRI a.n. Ahmad Fauzi"></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Kirim Pembayaran</button>
    <a href="dashboard.php" class="btn btn-secondary" style="margin-left:8px">Batal</a>
  </form>
  <?php endif; ?>
</div>

<?php include '../layout/footer.php'; ?>
