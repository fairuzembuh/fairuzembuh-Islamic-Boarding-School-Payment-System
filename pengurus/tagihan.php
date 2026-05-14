<?php
session_start();
require_once '../layout/config.php';
require_once '../layout/functions.php';

(new Auth())->cekPengurus();

$tagihan = new Tagihan();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $uid     = (int)($_POST['user_id']  ?? 0);
        $jenis   = Helper::sanitize($_POST['jenis']      ?? '');
        $nominal = (float)($_POST['nominal']  ?? 0);
        $bulan   = (int)($_POST['bulan']    ?? date('n'));
        $tahun   = (int)($_POST['tahun']    ?? date('Y'));
        $ket     = Helper::sanitize($_POST['keterangan'] ?? '');
        if ($uid && $jenis && $nominal) {
            $tagihan->tambah($uid, $jenis, $nominal, $bulan, $tahun, $ket);
            Helper::flashMessage('success', 'Tagihan berhasil ditambahkan.');
        }

    } elseif ($aksi === 'edit') {
        $tagihan->edit(
            (int)($_POST['id']      ?? 0),
            (float)($_POST['nominal']   ?? 0),
            Helper::sanitize($_POST['keterangan'] ?? '')
        );
        Helper::flashMessage('success', 'Tagihan berhasil diperbarui.');

    } elseif ($aksi === 'hapus') {
        $ok = $tagihan->hapus((int)($_POST['id'] ?? 0));
        Helper::flashMessage($ok ? 'success' : 'danger', $ok ? 'Tagihan dihapus.' : 'Gagal hapus (mungkin sudah diproses).');
    }

    Helper::redirect(BASE_URL . 'pengurus/tagihan.php');
}

$fs          = Helper::sanitize($_GET['status'] ?? '');
$fj          = Helper::sanitize($_GET['jenis']  ?? '');
$q           = Helper::sanitize($_GET['q']      ?? '');
$semuaTagihan = $tagihan->getSemuaTagihan($fs, $fj, $q);
$santri      = (new User())->getAllSantri();
$pengaturan  = (new Pengaturan())->getAll();

include '../layout/header.php';
?>
<h2 class="page-title">Kelola Tagihan</h2>
<?php Helper::showFlash(); ?>

<!-- Tambah Tagihan -->
<div class="card">
  <div class="card-title">Tambah Tagihan Baru</div>
  <form method="POST">
    <input type="hidden" name="aksi" value="tambah">
    <div class="form-row">
      <div class="form-group">
        <label>Santri</label>
        <select name="user_id" required>
          <option value="">-- Pilih santri --</option>
          <?php foreach ($santri as $s): ?>
            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?> (<?= $s['kamar'] ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Jenis Tagihan</label>
        <select name="jenis" required>
          <option value="bulanan">Bulanan</option>
          <option value="daftar_ulang">Daftar Ulang</option>
          <option value="lainnya">Lainnya</option>
        </select>
      </div>
      <div class="form-group">
        <label>Nominal (Rp)</label>
        <input type="number" name="nominal" required min="1000" value="<?= $pengaturan['nominal_bulanan'] ?? 200000 ?>">
      </div>
      <div class="form-group">
        <label>Bulan</label>
        <select name="bulan">
          <?php for ($i = 1; $i <= 12; $i++): ?>
            <option value="<?= $i ?>" <?= $i == date('n') ? 'selected' : '' ?>><?= Helper::namaBulan($i) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Tahun</label>
        <input type="number" name="tahun" value="<?= date('Y') ?>" min="2020" max="2099">
      </div>
      <div class="form-group">
        <label>Keterangan</label>
        <input type="text" name="keterangan" placeholder="Contoh: Tagihan bulanan Januari 2026">
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Tambah Tagihan</button>
  </form>
</div>

<!-- Filter -->
<div class="card">
  <div class="card-title">Filter &amp; Cari</div>
  <form method="GET" class="form-row" style="align-items:flex-end">
    <div class="form-group">
      <label>Status</label>
      <select name="status">
        <option value="">Semua</option>
        <option value="belum"    <?= $fs === 'belum'    ? 'selected' : '' ?>>Belum Bayar</option>
        <option value="menunggu" <?= $fs === 'menunggu' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
        <option value="lunas"    <?= $fs === 'lunas'    ? 'selected' : '' ?>>Lunas</option>
      </select>
    </div>
    <div class="form-group">
      <label>Jenis</label>
      <select name="jenis">
        <option value="">Semua</option>
        <option value="bulanan"     <?= $fj === 'bulanan'     ? 'selected' : '' ?>>Bulanan</option>
        <option value="daftar_ulang"<?= $fj === 'daftar_ulang'? 'selected' : '' ?>>Daftar Ulang</option>
        <option value="lainnya"     <?= $fj === 'lainnya'     ? 'selected' : '' ?>>Lainnya</option>
      </select>
    </div>
    <div class="form-group">
      <label>Cari Nama Santri</label>
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nama / username...">
    </div>
    <div class="form-group">
      <button type="submit" class="btn btn-primary">Cari</button>
      <a href="tagihan.php" class="btn btn-secondary" style="margin-left:6px">Reset</a>
    </div>
  </form>
</div>

<!-- Tabel Tagihan -->
<div class="card">
  <div class="card-title">Daftar Tagihan (<?= count($semuaTagihan) ?> data)</div>
  <div class="table-wrap">
    <table>
      <tr><th>Santri</th><th>Kamar</th><th>Keterangan</th><th>Jenis</th><th>Bulan</th><th>Nominal</th><th>Status</th><th>Aksi</th></tr>
      <?php foreach ($semuaTagihan as $t): ?>
      <tr>
        <td>
          <?= htmlspecialchars($t['nama']) ?>
          <?php if (!$t['user_aktif']): ?>
            <span class="badge badge-danger" style="font-size:11px">nonaktif</span>
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($t['kamar']) ?></td>
        <td><?= htmlspecialchars($t['keterangan']) ?></td>
        <td><?= ucfirst(str_replace('_', ' ', $t['jenis'])) ?></td>
        <td><?= $t['bulan'] ? Helper::namaBulan($t['bulan']) . ' ' . $t['tahun'] : '-' ?></td>
        <td><?= Helper::formatRupiah($t['nominal']) ?></td>
        <td><?= Helper::labelStatus($t['status']) ?></td>
        <td>
          <?php if ($t['status'] === 'belum'): ?>
          <button class="btn btn-warning btn-sm"
                  onclick="openEdit(<?= $t['id'] ?>,<?= $t['nominal'] ?>,'<?= addslashes($t['keterangan']) ?>')">
            Edit
          </button>
          <form method="POST" style="display:inline">
            <input type="hidden" name="aksi" value="hapus">
            <input type="hidden" name="id"   value="<?= $t['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm"
                    onclick="return confirm('Hapus tagihan ini?')">Hapus</button>
          </form>
          <?php else: ?>
            <span style="color:#999;font-size:12px">—</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<!-- Modal Edit Tagihan -->
<div id="modal-edit" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;
     background:rgba(0,0,0,.5);z-index:999;align-items:center;justify-content:center">
  <div style="background:#fff;padding:28px;border-radius:12px;width:400px;max-width:95%">
    <h3 style="margin-bottom:16px">Edit Tagihan</h3>
    <form method="POST">
      <input type="hidden" name="aksi" value="edit">
      <input type="hidden" name="id"   id="edit-id">
      <div class="form-group">
        <label>Nominal (Rp)</label>
        <input type="number" name="nominal" id="edit-nominal" required>
      </div>
      <div class="form-group">
        <label>Keterangan</label>
        <input type="text" name="keterangan" id="edit-ket">
      </div>
      <button type="submit" class="btn btn-primary">Simpan</button>
      <button type="button" class="btn btn-secondary" onclick="closeEdit()" style="margin-left:8px">Batal</button>
    </form>
  </div>
</div>

<script>
function openEdit(id, nom, ket) {
  document.getElementById('edit-id').value      = id;
  document.getElementById('edit-nominal').value = nom;
  document.getElementById('edit-ket').value     = ket;
  document.getElementById('modal-edit').style.display = 'flex';
}
function closeEdit() {
  document.getElementById('modal-edit').style.display = 'none';
}
</script>

<?php include '../layout/footer.php'; ?>
