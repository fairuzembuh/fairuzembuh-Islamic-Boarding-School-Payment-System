<?php
session_start();
require_once '../layout/config.php';
require_once '../layout/functions.php';

(new Auth())->cekSantri();

$tagihan = (new Tagihan())->getTagihanSantri((int)$_SESSION['user_id']);

include '../layout/header.php';
?>
<h2 class="page-title">Semua Tagihan Saya</h2>

<div class="card">
  <div class="table-wrap">
    <table>
      <tr>
        <th>Keterangan</th>
        <th>Jenis</th>
        <th>Bulan / Tahun</th>
        <th>Nominal</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
      <?php foreach ($tagihan as $t): ?>
      <tr>
        <td><?= htmlspecialchars($t['keterangan']) ?></td>
        <td><?= ucfirst(str_replace('_', ' ', $t['jenis'])) ?></td>
        <td>
          <?= $t['bulan']
              ? Helper::namaBulan((int)$t['bulan']) . ' ' . $t['tahun']
              : '-' ?>
        </td>
        <td><?= Helper::formatRupiah($t['nominal']) ?></td>
        <td><?= Helper::labelStatus($t['status']) ?></td>
        <td>
          <?php if ($t['status'] === 'belum'): ?>
            <a href="bayar.php?tagihan_id=<?= $t['id'] ?>" class="btn btn-primary btn-sm">Bayar</a>
          <?php else: ?>
            <span style="color:#999;font-size:12px">—</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($tagihan)): ?>
        <tr><td colspan="6" style="color:#999;text-align:center">Belum ada tagihan.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>

<?php include '../layout/footer.php'; ?>
