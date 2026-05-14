<?php
session_start();
require_once '../layout/config.php';
require_once '../layout/functions.php';

(new Auth())->cekPengurus();

$user = new User();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $ok = $user->tambah(
            Helper::sanitize($_POST['nama']     ?? ''),
            Helper::sanitize($_POST['username'] ?? ''),
            $_POST['password'] ?? '',
            Helper::sanitize($_POST['kamar']    ?? ''),
            Helper::sanitize($_POST['no_hp']    ?? '')
        );
        Helper::flashMessage(
            $ok ? 'success' : 'danger',
            $ok ? 'Santri berhasil ditambahkan.' : 'Gagal menambah santri (username mungkin sudah dipakai).'
        );

    } elseif ($aksi === 'edit') {
        $user->edit(
            (int)($_POST['id']   ?? 0),
            Helper::sanitize($_POST['nama']  ?? ''),
            Helper::sanitize($_POST['kamar'] ?? ''),
            Helper::sanitize($_POST['no_hp'] ?? ''),
            $_POST['password'] ?? ''
        );
        Helper::flashMessage('success', 'Data santri diperbarui.');

    } elseif ($aksi === 'hapus') {
        $user->hapus((int)($_POST['id'] ?? 0));
        Helper::flashMessage('success', 'Santri dinonaktifkan.');

    } elseif ($aksi === 'aktifkan') {
        $user->aktifkan((int)($_POST['id'] ?? 0));
        Helper::flashMessage('success', 'Santri berhasil diaktifkan kembali.');
    }

    Helper::redirect(BASE_URL . 'pengurus/santri.php');
}

$santri     = $user->getAllSantri();
$nonSantri  = $user->getAllNonSantri();

include '../layout/header.php';
?>
<h2 class="page-title">Kelola Akun Santri</h2>
<?php Helper::showFlash(); ?>

<!-- Form Tambah -->
<div class="card">
  <div class="card-title">Tambah Santri Baru</div>
  <form method="POST">
    <input type="hidden" name="aksi" value="tambah">
    <div class="form-row">
      <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" required></div>
      <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
      <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
      <div class="form-group"><label>Kamar</label><input type="text" name="kamar" placeholder="Contoh: A1"></div>
      <div class="form-group"><label>No. HP</label><input type="text" name="no_hp"></div>
    </div>
    <button type="submit" class="btn btn-primary">Tambah Santri</button>
  </form>
</div>

<!-- Daftar Santri Aktif -->
<div class="card">
  <div class="card-title">Daftar Santri Aktif (<?= count($santri) ?>)</div>
  <div class="table-wrap">
    <table>
      <tr><th>Nama</th><th>Username</th><th>Kamar</th><th>No. HP</th><th>Tgl Daftar</th><th>Aksi</th></tr>
      <?php foreach ($santri as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['nama']) ?></td>
        <td><?= htmlspecialchars($s['username']) ?></td>
        <td><?= htmlspecialchars($s['kamar']) ?></td>
        <td><?= htmlspecialchars($s['no_hp']) ?></td>
        <td><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
        <td>
          <button class="btn btn-warning btn-sm"
                  onclick="openEditSantri(<?= $s['id'] ?>,'<?= addslashes($s['nama']) ?>','<?= addslashes($s['kamar']) ?>','<?= addslashes($s['no_hp']) ?>')">
            Edit
          </button>
          <form method="POST" style="display:inline">
            <input type="hidden" name="aksi" value="hapus">
            <input type="hidden" name="id"   value="<?= $s['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm"
                    onclick="return confirm('Nonaktifkan santri ini?')">Hapus</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<!-- Daftar Santri Non Aktif -->
<div class="card">
  <div class="card-title">Daftar Santri Non Aktif (<?= count($nonSantri) ?>)</div>
  <div class="table-wrap">
    <table>
      <tr><th>Nama</th><th>Username</th><th>Kamar</th><th>No. HP</th><th>Tgl Daftar</th><th>Aksi</th></tr>
      <?php foreach ($nonSantri as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['nama']) ?></td>
        <td><?= htmlspecialchars($s['username']) ?></td>
        <td><?= htmlspecialchars($s['kamar']) ?></td>
        <td><?= htmlspecialchars($s['no_hp']) ?></td>
        <td><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
        <td>
          <button class="btn btn-warning btn-sm"
                  onclick="openEditSantri(<?= $s['id'] ?>,'<?= addslashes($s['nama']) ?>','<?= addslashes($s['kamar']) ?>','<?= addslashes($s['no_hp']) ?>')">
            Edit
          </button>
          <form method="POST" style="display:inline">
            <input type="hidden" name="aksi" value="aktifkan">
            <input type="hidden" name="id"   value="<?= $s['id'] ?>">
            <button type="submit" class="btn btn-aktifkan btn-sm"
                    onclick="return confirm('Aktifkan kembali santri ini?')">Aktifkan</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<!-- Modal Edit Santri -->
<div id="modal-santri" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;
     background:rgba(0,0,0,.5);z-index:999;align-items:center;justify-content:center">
  <div style="background:#fff;padding:28px;border-radius:12px;width:440px;max-width:95%">
    <h3 style="margin-bottom:16px">Edit Data Santri</h3>
    <form method="POST">
      <input type="hidden" name="aksi" value="edit">
      <input type="hidden" name="id"   id="esid">
      <div class="form-group"><label>Nama</label><input type="text" name="nama" id="esnam" required></div>
      <div class="form-group"><label>Kamar</label><input type="text" name="kamar" id="eskam"></div>
      <div class="form-group"><label>No. HP</label><input type="text" name="no_hp" id="eshp"></div>
      <div class="form-group">
        <label>Password Baru <small style="font-weight:normal">(kosongkan jika tidak diubah)</small></label>
        <input type="password" name="password">
      </div>
      <button type="submit" class="btn btn-primary">Simpan</button>
      <button type="button" class="btn btn-secondary"
              onclick="document.getElementById('modal-santri').style.display='none'"
              style="margin-left:8px">Batal</button>
    </form>
  </div>
</div>

<script>
function openEditSantri(id, nama, kamar, hp) {
  document.getElementById('esid').value  = id;
  document.getElementById('esnam').value = nama;
  document.getElementById('eskam').value = kamar;
  document.getElementById('eshp').value  = hp;
  document.getElementById('modal-santri').style.display = 'flex';
}
</script>

<?php include '../layout/footer.php'; ?>
