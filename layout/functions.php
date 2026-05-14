<?php
require_once __DIR__ . '/config.php';

// ============================================================
//  CLASS AUTH — Autentikasi & Hak Akses
// ============================================================
class Auth {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login(string $username, string $password): bool {
        $username = $this->db->escape($username);
        $result   = $this->db->query("SELECT * FROM users WHERE username='$username' AND aktif=1");
        if ($result && $row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id']  = $row['id'];
                $_SESSION['nama']     = $row['nama'];
                $_SESSION['role']     = $row['role'];
                $_SESSION['username'] = $row['username'];
                return true;
            }
        }
        return false;
    }

    public function logout(): void {
        session_destroy();
        header("Location: " . BASE_URL . "index.php");
        exit;
    }

    public function cekLogin(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "index.php");
            exit;
        }
    }

    public function cekPengurus(): void {
        $this->cekLogin();
        if ($_SESSION['role'] !== 'pengurus') {
            header("Location: " . BASE_URL . "santri/dashboard.php");
            exit;
        }
    }

    public function cekSantri(): void {
        $this->cekLogin();
        if ($_SESSION['role'] !== 'santri') {
            header("Location: " . BASE_URL . "pengurus/dashboard.php");
            exit;
        }
    }
}

// ============================================================
//  CLASS USER — Manajemen Data Pengguna / Santri
// ============================================================
class User {
    private Database $db;

    // Properties (encapsulation)
    private int    $id;
    private string $nama;
    private string $username;
    private string $kamar;
    private string $noHp;
    private bool   $aktif;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // --- Getter & Setter ---
    public function getId(): int           { return $this->id; }
    public function getNama(): string      { return $this->nama; }
    public function getUsername(): string  { return $this->username; }
    public function getKamar(): string     { return $this->kamar; }
    public function getNoHp(): string      { return $this->noHp; }
    public function isAktif(): bool        { return $this->aktif; }

    public function setNama(string $nama): void     { $this->nama     = $nama; }
    public function setKamar(string $kamar): void   { $this->kamar    = $kamar; }
    public function setNoHp(string $noHp): void     { $this->noHp     = $noHp; }

    // --- Methods ---
    public function getAllSantri(): array {
        $result = $this->db->query("SELECT * FROM users WHERE role='santri' AND aktif=1 ORDER BY nama ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAllNonSantri(): array {
        $result = $this->db->query("SELECT * FROM users WHERE role='santri' AND aktif=0 ORDER BY nama ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id): ?array {
        $id     = (int)$id;
        $result = $this->db->query("SELECT * FROM users WHERE id=$id");
        return $result ? $result->fetch_assoc() : null;
    }

    public function tambah(string $nama, string $username, string $password, string $kamar, string $noHp): bool {
        $nama     = $this->db->escape($nama);
        $username = $this->db->escape($username);
        $kamar    = $this->db->escape($kamar);
        $noHp     = $this->db->escape($noHp);
        $hash     = password_hash($password, PASSWORD_DEFAULT);
        return (bool)$this->db->query(
            "INSERT INTO users (nama,username,password,role,kamar,no_hp)
             VALUES ('$nama','$username','$hash','santri','$kamar','$noHp')"
        );
    }

    public function edit(int $id, string $nama, string $kamar, string $noHp, string $password = ''): bool {
        $id    = (int)$id;
        $nama  = $this->db->escape($nama);
        $kamar = $this->db->escape($kamar);
        $noHp  = $this->db->escape($noHp);
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            return (bool)$this->db->query(
                "UPDATE users SET nama='$nama',kamar='$kamar',no_hp='$noHp',password='$hash' WHERE id=$id"
            );
        }
        return (bool)$this->db->query(
            "UPDATE users SET nama='$nama',kamar='$kamar',no_hp='$noHp' WHERE id=$id"
        );
    }

    public function hapus(int $id): bool {
        $id  = (int)$id;
        $cek = $this->db->query("SELECT COUNT(*) AS total FROM tagihan WHERE user_id=$id");
        $jml = (int)$cek->fetch_assoc()['total'];
        if ($jml === 0) {
            return (bool)$this->db->query("DELETE FROM users WHERE id=$id AND role='santri'");
        }
        return (bool)$this->db->query("UPDATE users SET aktif=0 WHERE id=$id AND role='santri'");
    }

    public function aktifkan(int $id): bool {
        $id = (int)$id;
        return (bool)$this->db->query("UPDATE users SET aktif=1 WHERE id=$id AND role='santri'");
    }
}

// ============================================================
//  CLASS TAGIHAN — Mengelola Tagihan Santri
// ============================================================
class Tagihan {
    private Database $db;

    // Properties
    private int    $id;
    private int    $userId;
    private string $jenis;
    private float  $nominal;
    private int    $bulan;
    private int    $tahun;
    private string $keterangan;
    private string $status;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // --- Getter & Setter ---
    public function getId(): int             { return $this->id; }
    public function getUserId(): int         { return $this->userId; }
    public function getJenis(): string       { return $this->jenis; }
    public function getNominal(): float      { return $this->nominal; }
    public function getBulan(): int          { return $this->bulan; }
    public function getTahun(): int          { return $this->tahun; }
    public function getKeterangan(): string  { return $this->keterangan; }
    public function getStatus(): string      { return $this->status; }

    public function setNominal(float $nominal): void        { $this->nominal    = $nominal; }
    public function setKeterangan(string $ket): void        { $this->keterangan = $ket; }

    // --- Methods ---
    public function getTagihanSantri(int $userId): array {
        $userId = (int)$userId;
        $result = $this->db->query(
            "SELECT * FROM tagihan WHERE user_id=$userId ORDER BY tahun DESC, bulan DESC"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTagihanBelumBayar(int $userId): array {
        $userId = (int)$userId;
        $result = $this->db->query(
            "SELECT * FROM tagihan WHERE user_id=$userId AND status IN ('belum','menunggu')
             ORDER BY tahun ASC, bulan ASC"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getSemuaTagihan(string $filterStatus = '', string $filterJenis = '', string $search = ''): array {
        $where = "WHERE 1=1";
        if ($filterStatus) {
            $fs     = $this->db->escape($filterStatus);
            $where .= " AND t.status='$fs'";
        }
        if ($filterJenis) {
            $fj     = $this->db->escape($filterJenis);
            $where .= " AND t.jenis='$fj'";
        }
        if ($search) {
            $s      = $this->db->escape($search);
            $where .= " AND (u.nama LIKE '%$s%' OR u.username LIKE '%$s%')";
        }
        $result = $this->db->query(
            "SELECT t.*, u.nama, u.kamar, u.aktif AS user_aktif
             FROM tagihan t JOIN users u ON t.user_id=u.id
             $where ORDER BY t.created_at DESC"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function tambah(int $userId, string $jenis, float $nominal, int $bulan, int $tahun, string $keterangan): bool {
        $userId     = (int)$userId;
        $jenis      = $this->db->escape($jenis);
        $nominal    = (float)$nominal;
        $bulan      = (int)$bulan;
        $tahun      = (int)$tahun;
        $keterangan = $this->db->escape($keterangan);
        return (bool)$this->db->query(
            "INSERT INTO tagihan (user_id,jenis,nominal,bulan,tahun,keterangan)
             VALUES ($userId,'$jenis',$nominal,$bulan,$tahun,'$keterangan')"
        );
    }

    public function edit(int $id, float $nominal, string $keterangan): bool {
        $id         = (int)$id;
        $nominal    = (float)$nominal;
        $keterangan = $this->db->escape($keterangan);
        return (bool)$this->db->query(
            "UPDATE tagihan SET nominal=$nominal, keterangan='$keterangan' WHERE id=$id"
        );
    }

    public function hapus(int $id): bool {
        $id = (int)$id;
        return (bool)$this->db->query("DELETE FROM tagihan WHERE id=$id AND status='belum'");
    }

    public function autoGenerateBulanan(): void {
        $pengaturan    = (new Pengaturan())->getAll();
        $hariGenerate  = (int)($pengaturan['hari_generate_tagihan'] ?? 1);
        $hariIni       = (int)date('j');
        $bulanIni      = (int)date('n');
        $tahunIni      = (int)date('Y');

        if ($hariIni < $hariGenerate) return;

        $cek = $this->db->query(
            "SELECT id FROM tagihan WHERE jenis='bulanan' AND bulan=$bulanIni AND tahun=$tahunIni LIMIT 1"
        );
        if ($cek && $cek->num_rows > 0) return;

        $nominal    = (float)($pengaturan['nominal_bulanan'] ?? 200000);
        $santri     = $this->db->query("SELECT id FROM users WHERE role='santri' AND aktif=1");
        $bulanNama  = Helper::namaBulan($bulanIni);
        while ($row = $santri->fetch_assoc()) {
            $uid = (int)$row['id'];
            $this->db->query(
                "INSERT INTO tagihan (user_id,jenis,nominal,bulan,tahun,keterangan)
                 VALUES ($uid,'bulanan',$nominal,$bulanIni,$tahunIni,
                         'Tagihan bulanan $bulanNama $tahunIni')"
            );
        }
    }
}

// ============================================================
//  CLASS PEMBAYARAN — Mengelola Transaksi Pembayaran Santri
// ============================================================
class Pembayaran {
    private Database $db;

    // Properties
    private int    $id;
    private int    $tagihanId;
    private int    $userId;
    private string $buktiFIle;
    private string $catatan;
    private string $status;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // --- Getter & Setter ---
    public function getId(): int            { return $this->id; }
    public function getTagihanId(): int     { return $this->tagihanId; }
    public function getUserId(): int        { return $this->userId; }
    public function getBuktiFile(): string  { return $this->buktiFIle; }
    public function getCatatan(): string    { return $this->catatan; }
    public function getStatus(): string     { return $this->status; }

    public function setStatus(string $status): void { $this->status = $status; }

    // --- Methods ---
    public function submit(int $tagihanId, int $userId, string $buktiFile, string $catatan): bool {
        $tagihanId = (int)$tagihanId;
        $userId    = (int)$userId;
        $buktiFile = $this->db->escape($buktiFile);
        $catatan   = $this->db->escape($catatan);
        $tanggal   = date('Y-m-d');
        $ok        = $this->db->query(
            "INSERT INTO pembayaran (tagihan_id,user_id,tanggal_bayar,bukti_file,catatan)
             VALUES ($tagihanId,$userId,'$tanggal','$buktiFile','$catatan')"
        );
        if ($ok) {
            $this->db->query("UPDATE tagihan SET status='menunggu' WHERE id=$tagihanId");
        }
        return (bool)$ok;
    }

    public function getMenunggu(): array {
        $result = $this->db->query(
            "SELECT p.*, t.jenis, t.bulan, t.tahun, t.nominal,
                    t.keterangan AS ket_tagihan, u.nama, u.kamar
             FROM pembayaran p
             JOIN tagihan t ON p.tagihan_id=t.id
             JOIN users   u ON p.user_id=u.id
             WHERE p.status='menunggu'
             ORDER BY p.created_at ASC"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getRiwayat(int $userId): array {
        $userId = (int)$userId;
        $result = $this->db->query(
            "SELECT p.*, t.jenis, t.bulan, t.tahun, t.nominal,
                    t.keterangan AS ket_tagihan
             FROM pembayaran p
             JOIN tagihan t ON p.tagihan_id=t.id
             WHERE p.user_id=$userId
             ORDER BY p.created_at DESC"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function konfirmasi(int $pembayaranId, string $status, int $pengurusId): bool {
        $pembayaranId = (int)$pembayaranId;
        $pengurusId   = (int)$pengurusId;
        $status       = $this->db->escape($status);
        $now          = date('Y-m-d H:i:s');
        $ok           = $this->db->query(
            "UPDATE pembayaran
             SET status='$status', dikonfirmasi_oleh=$pengurusId, tanggal_konfirmasi='$now'
             WHERE id=$pembayaranId"
        );
        if ($ok) {
            $p = $this->db->query(
                "SELECT tagihan_id FROM pembayaran WHERE id=$pembayaranId"
            )->fetch_assoc();
            $tid = (int)$p['tagihan_id'];
            if ($status === 'diterima') {
                $this->db->query("UPDATE tagihan SET status='lunas' WHERE id=$tid");
            } elseif ($status === 'ditolak') {
                $this->db->query("UPDATE tagihan SET status='belum' WHERE id=$tid");
            }
        }
        return (bool)$ok;
    }
}

// ============================================================
//  CLASS TRANSAKSI — Mengelola Transaksi Keuangan Lainnya
// ============================================================
class Transaksi {
    private Database $db;

    // Properties
    private int    $id;
    private string $jenis;
    private string $kategori;
    private float  $nominal;
    private string $keterangan;
    private string $tanggal;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // --- Getter & Setter ---
    public function getId(): int            { return $this->id; }
    public function getJenis(): string      { return $this->jenis; }
    public function getKategori(): string   { return $this->kategori; }
    public function getNominal(): float     { return $this->nominal; }
    public function getKeterangan(): string { return $this->keterangan; }
    public function getTanggal(): string    { return $this->tanggal; }

    public function setNominal(float $nominal): void      { $this->nominal    = $nominal; }
    public function setKeterangan(string $ket): void      { $this->keterangan = $ket; }

    // --- Methods ---
    public function tambah(string $jenis, string $kategori, float $nominal, string $keterangan, string $tanggal, int $userId): bool {
        $jenis      = $this->db->escape($jenis);
        $kategori   = $this->db->escape($kategori);
        $nominal    = (float)$nominal;
        $keterangan = $this->db->escape($keterangan);
        $tanggal    = $this->db->escape($tanggal);
        $userId     = (int)$userId;
        return (bool)$this->db->query(
            "INSERT INTO transaksi (jenis,kategori,nominal,keterangan,tanggal,dicatat_oleh)
             VALUES ('$jenis','$kategori',$nominal,'$keterangan','$tanggal',$userId)"
        );
    }

    public function getAll(string $bulan = '', string $tahun = '', string $jenis = ''): array {
        $where = "WHERE 1=1";
        if ($bulan) $where .= " AND MONTH(tanggal)=" . (int)$bulan;
        if ($tahun) $where .= " AND YEAR(tanggal)="  . (int)$tahun;
        if ($jenis) {
            $j = $this->db->escape($jenis);
            $where .= " AND jenis='$j'";
        }
        $result = $this->db->query(
            "SELECT tr.*, u.nama
             FROM transaksi tr JOIN users u ON tr.dicatat_oleh=u.id
             $where ORDER BY tanggal DESC"
        );
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function hapus(int $id): bool {
        $id = (int)$id;
        return (bool)$this->db->query("DELETE FROM transaksi WHERE id=$id");
    }
}

// ============================================================
//  CLASS LAPORAN — extends Transaksi (Inheritance)
//  Mewarisi kemampuan baca transaksi + tambah fitur laporan
// ============================================================
class Laporan extends Transaksi {

    private Database $db;

    public function __construct() {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function getDashboardPengurus(): array {
        $bulan = (int)date('n');
        $tahun = (int)date('Y');
        $data  = [];

        $r = $this->db->query("SELECT COUNT(*) AS total FROM users WHERE role='santri' AND aktif=1");
        $data['total_santri'] = $r->fetch_assoc()['total'];

        $r = $this->db->query(
            "SELECT COUNT(*) AS total FROM tagihan
             WHERE jenis='bulanan' AND bulan=$bulan AND tahun=$tahun AND status='lunas'"
        );
        $data['sudah_bayar'] = $r->fetch_assoc()['total'];

        $r = $this->db->query(
            "SELECT COUNT(*) AS total FROM tagihan
             WHERE jenis='bulanan' AND bulan=$bulan AND tahun=$tahun AND status IN ('belum','menunggu')"
        );
        $data['belum_bayar'] = $r->fetch_assoc()['total'];

        $r = $this->db->query("SELECT COUNT(*) AS total FROM pembayaran WHERE status='menunggu'");
        $data['menunggu_konfirmasi'] = $r->fetch_assoc()['total'];

        $r = $this->db->query(
            "SELECT COALESCE(SUM(nominal),0) AS total FROM tagihan
             WHERE status='lunas' AND bulan=$bulan AND tahun=$tahun"
        );
        $pemasukanTagihan = (float)$r->fetch_assoc()['total'];

        $r = $this->db->query(
            "SELECT COALESCE(SUM(nominal),0) AS total FROM transaksi
             WHERE jenis='pemasukan' AND MONTH(tanggal)=$bulan AND YEAR(tanggal)=$tahun"
        );
        $pemasukanLain = (float)$r->fetch_assoc()['total'];
        $data['pemasukan_bulan'] = $pemasukanTagihan + $pemasukanLain;

        $r = $this->db->query(
            "SELECT COALESCE(SUM(nominal),0) AS total FROM transaksi
             WHERE jenis='pengeluaran' AND MONTH(tanggal)=$bulan AND YEAR(tanggal)=$tahun"
        );
        $data['pengeluaran_bulan'] = $r->fetch_assoc()['total'];

        return $data;
    }

    public function getLaporanKeuangan(int $bulan, int $tahun): array {
        $bulan = (int)$bulan;
        $tahun = (int)$tahun;

        $r = $this->db->query(
            "SELECT t.*, u.nama, u.kamar
             FROM tagihan t JOIN users u ON t.user_id=u.id
             WHERE t.status='lunas' AND t.bulan=$bulan AND t.tahun=$tahun
             ORDER BY u.nama"
        );
        $pemasukanTagihan = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];

        $r = $this->db->query(
            "SELECT tr.*, u.nama AS dicatat
             FROM transaksi tr JOIN users u ON tr.dicatat_oleh=u.id
             WHERE MONTH(tr.tanggal)=$bulan AND YEAR(tr.tanggal)=$tahun
             ORDER BY tr.tanggal"
        );
        $transaksiLain = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];

        $totalPemasukanTagihan = array_sum(array_column($pemasukanTagihan, 'nominal'));
        $totalPemasukanLain    = array_sum(array_map(
            fn($t) => $t['jenis'] === 'pemasukan' ? $t['nominal'] : 0, $transaksiLain
        ));
        $totalPengeluaran      = array_sum(array_map(
            fn($t) => $t['jenis'] === 'pengeluaran' ? $t['nominal'] : 0, $transaksiLain
        ));

        return compact(
            'pemasukanTagihan', 'transaksiLain',
            'totalPemasukanTagihan', 'totalPemasukanLain', 'totalPengeluaran'
        );
    }
}

// ============================================================
//  CLASS PENGATURAN — Mengelola Konfigurasi Sistem
// ============================================================
class Pengaturan {
    private Database $db;

    // Properties
    private array $data = [];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // --- Getter & Setter ---
    public function getData(): array { return $this->data; }

    public function get(string $kunci, mixed $default = null): mixed {
        return $this->data[$kunci] ?? $default;
    }

    // --- Methods ---
    public function getAll(): array {
        $result = $this->db->query("SELECT kunci, nilai FROM pengaturan");
        $this->data = [];
        while ($row = $result->fetch_assoc()) {
            $this->data[$row['kunci']] = $row['nilai'];
        }
        return $this->data;
    }

    public function simpan(string $kunci, string $nilai): bool {
        $kunci = $this->db->escape($kunci);
        $nilai = $this->db->escape($nilai);
        return (bool)$this->db->query(
            "UPDATE pengaturan SET nilai='$nilai' WHERE kunci='$kunci'"
        );
    }
}

// ============================================================
//  CLASS HELPER — Fungsi Pembantu Umum (static utility)
// ============================================================
class Helper {

    public static function formatRupiah(float $nominal): string {
        return 'Rp ' . number_format($nominal, 0, ',', '.');
    }

    public static function namaBulan(int $bulan): string {
        $arr = ['','Januari','Februari','Maret','April','Mei','Juni',
                'Juli','Agustus','September','Oktober','November','Desember'];
        return $arr[$bulan] ?? '';
    }

    public static function labelStatus(string $status): string {
        $map = [
            'belum'    => '<span class="badge badge-danger">Belum Bayar</span>',
            'menunggu' => '<span class="badge badge-warning">Menunggu Konfirmasi</span>',
            'lunas'    => '<span class="badge badge-success">Lunas</span>',
            'diterima' => '<span class="badge badge-success">Diterima</span>',
            'ditolak'  => '<span class="badge badge-danger">Ditolak</span>',
        ];
        return $map[$status] ?? $status;
    }

    public static function sanitize(string $str): string {
        return htmlspecialchars(strip_tags(trim($str)));
    }

    public static function redirect(string $url): void {
        header("Location: $url");
        exit;
    }

    public static function flashMessage(string $type, string $msg): void {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    public static function showFlash(): void {
        if (!empty($_SESSION['flash'])) {
            $f   = $_SESSION['flash'];
            $cls = match($f['type']) {
                'success' => 'alert-success',
                'danger'  => 'alert-danger',
                default   => 'alert-warning',
            };
            echo "<div class='alert $cls'>" . htmlspecialchars($f['msg']) . "</div>";
            unset($_SESSION['flash']);
        }
    }

    public static function uploadBukti(array $file): string|false {
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($ext, $allowed)) return false;
        if ($file['size'] > 2 * 1024 * 1024) return false;
        $nama = uniqid('bukti_') . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $nama)) return $nama;
        return false;
    }
}
