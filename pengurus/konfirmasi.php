<?php
session_start();
require_once '../layout/config.php';
require_once '../layout/functions.php';

(new Auth())->cekPengurus();

$pembayaran = new Pembayaran();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = (int)($_POST['id']   ?? 0);
    $aksi = Helper::sanitize($_POST['aksi'] ?? '');
    if ($id && in_array($aksi, ['diterima', 'ditolak'])) {
        $pembayaran->konfirmasi($id, $aksi, $_SESSION['user_id']);
        Helper::flashMessage('success', 'Pembayaran berhasil di' . ($aksi === 'diterima' ? 'terima' : 'tolak') . '.');
    }
    Helper::redirect(BASE_URL . 'pengurus/konfirmasi.php');
}

$pending = $pembayaran->getMenunggu();
include '../layout/header.php';
?>
<h2 class="page-title">Konfirmasi Pembayaran</h2>
<?php Helper::showFlash(); ?>

<div class="card">
  <?php if (empty($pending)): ?>
    <p style="color:#27ae60;font-weight:600">Tidak ada pembayaran yang menunggu konfirmasi.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <tr><th>Santri</th><th>Kamar</th><th>Tagihan</th><th>Nominal</th><th>Tgl Bayar</th><th>Catatan</th><th>Bukti</th><th>Aksi</th></tr>
      <?php foreach ($pending as $p): ?>
      <tr>
        <td><?= htmlspecialchars($p['nama']) ?></td>
        <td><?= htmlspecialchars($p['kamar']) ?></td>
        <td><?= htmlspecialchars($p['ket_tagihan']) ?></td>
        <td><?= Helper::formatRupiah($p['nominal']) ?></td>
        <td><?= $p['tanggal_bayar'] ?></td>
        <td><?= htmlspecialchars($p['catatan']) ?></td>
        <td>
          <?php if ($p['bukti_file']): ?>
            <a href="<?= BASE_URL ?>uploads/<?= htmlspecialchars($p['bukti_file']) ?>" target="_blank" class="btn btn-secondary btn-sm">Lihat Bukti</a>
          <?php else: ?><span style="color:#999">Tidak ada</span><?php endif; ?>
        </td>
        <td>
          <form method="POST" style="display:flex;gap:6px">
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
  <?php endif; ?>
</div>

<?php include '../layout/footer.php'; ?>
