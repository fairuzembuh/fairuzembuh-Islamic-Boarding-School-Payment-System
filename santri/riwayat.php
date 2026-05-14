<?php
session_start();
require_once '../layout/config.php';
require_once '../layout/functions.php';

(new Auth())->cekSantri();

$riwayat = (new Pembayaran())->getRiwayat((int)$_SESSION['user_id']);

include '../layout/header.php';
?>
<h2 class="page-title">Riwayat Pembayaran</h2>

<div class="card">
  <?php if (empty($riwayat)): ?>
    <p style="color:#999">Belum ada riwayat pembayaran.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <tr>
        <th>Keterangan</th>
        <th>Jenis</th>
        <th>Nominal</th>
        <th>Tgl Bayar</th>
        <th>Bukti</th>
        <th>Status</th>
      </tr>
      <?php foreach ($riwayat as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['ket_tagihan']) ?></td>
        <td><?= ucfirst(str_replace('_', ' ', $r['jenis'])) ?></td>
        <td><?= Helper::formatRupiah($r['nominal']) ?></td>
        <td><?= $r['tanggal_bayar'] ?></td>
        <td>
          <?php if ($r['bukti_file']): ?>
            <a href="<?= BASE_URL ?>uploads/<?= htmlspecialchars($r['bukti_file']) ?>"
               target="_blank" class="btn btn-secondary btn-sm">Lihat</a>
          <?php else: ?>
            <span style="color:#999">-</span>
          <?php endif; ?>
        </td>
        <td><?= Helper::labelStatus($r['status']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php include '../layout/footer.php'; ?>
