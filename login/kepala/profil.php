<?php
$page_title = 'Profil Jabatan';
include 'header.php';

$pesan  = isset($_GET['pesan']) ? $_GET['pesan'] : '';
$errors = [];

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_profil'])) {
    $nip          = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $jabatan      = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $pangkat      = mysqli_real_escape_string($koneksi, $_POST['pangkat']);
    $golongan     = mysqli_real_escape_string($koneksi, $_POST['golongan']);
    $mulai        = $_POST['mulai_jabatan'];

    if (!$nama_lengkap) $errors[] = 'Nama lengkap wajib diisi.';

    if (!$errors) {
        mysqli_query($koneksi, "UPDATE kepala SET
            nip='$nip', nama_lengkap='$nama_lengkap', jabatan='$jabatan',
            pangkat='$pangkat', golongan='$golongan', mulai_jabatan='$mulai'
            WHERE user_id={$_SESSION['id_login']}");
        mysqli_query($koneksi, "UPDATE users SET name='$nama_lengkap' WHERE id={$_SESSION['id_login']}");
        $_SESSION['nama'] = $_POST['nama_lengkap'];
        header("location: profil.php?pesan=Profil berhasil diperbarui."); exit;
    }
}

// Proses ganti password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ganti_password'])) {
    $pass_lama  = $_POST['password_lama'];
    $pass_baru  = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi'];

    if (!$pass_lama)               $errors[] = 'Password lama wajib diisi.';
    if (strlen($pass_baru) < 6)    $errors[] = 'Password baru minimal 6 karakter.';
    if ($pass_baru !== $konfirmasi) $errors[] = 'Konfirmasi password tidak cocok.';

    if (!$errors) {
        $user = mysqli_fetch_assoc(mysqli_query($koneksi,
            "SELECT password FROM users WHERE id={$_SESSION['id_login']}"));
        if (!password_verify($pass_lama, $user['password'])) {
            $errors[] = 'Password lama tidak sesuai.';
        } else {
            $hash = password_hash($pass_baru, PASSWORD_BCRYPT);
            mysqli_query($koneksi, "UPDATE users SET password='$hash' WHERE id={$_SESSION['id_login']}");
            header("location: profil.php?tab=password&pesan=Password berhasil diubah."); exit;
        }
    }
}

$tab    = isset($_GET['tab']) ? $_GET['tab'] : 'profil';
$kepala = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT k.*, u.email FROM kepala k JOIN users u ON k.user_id=u.id
     WHERE k.user_id={$_SESSION['id_login']}"));
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if ($errors): ?>
  <div class="alert alert-danger">
    <ul class="mb-0 ps-3"><?php foreach($errors as $e) echo "<li style='font-size:13px'>$e</li>"; ?></ul>
  </div>
<?php endif; ?>

<div class="row g-3">
  <!-- Kartu info -->
  <div class="col-lg-3">
    <div class="card p-4 text-center">
      <div class="mb-3">
        <div style="width:80px;height:80px;background:#e8eef5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:34px;color:#1a2942">
          <i class="bi bi-person-badge"></i>
        </div>
      </div>
      <h6 class="fw-bold mb-1"><?= htmlspecialchars($kepala['nama_lengkap'] ?? $_SESSION['nama']) ?></h6>
      <small class="text-muted"><?= htmlspecialchars($kepala['email'] ?? '') ?></small>
      <hr>
      <div style="font-size:12px;color:#6b7280">
        <div class="mb-1"><strong>NIP:</strong> <?= htmlspecialchars($kepala['nip'] ?? '-') ?></div>
        <div class="mb-1"><strong>Jabatan:</strong><br><?= htmlspecialchars($kepala['jabatan'] ?? '-') ?></div>
        <?php if ($kepala['pangkat']): ?>
          <div><strong>Pangkat:</strong> <?= htmlspecialchars($kepala['pangkat']) ?> / <?= htmlspecialchars($kepala['golongan'] ?? '') ?></div>
        <?php endif; ?>
      </div>
      <hr>
      <div class="alert alert-info py-2 mb-0" style="font-size:11px;text-align:left">
        <i class="bi bi-info-circle me-1"></i>
        Data ini muncul otomatis di semua laporan yang dicetak.
      </div>
    </div>
  </div>

  <!-- Form -->
  <div class="col-lg-9">
    <ul class="nav nav-tabs mb-0">
      <li class="nav-item">
        <a class="nav-link <?= $tab==='profil'?'active':'' ?>" href="profil.php?tab=profil">
          <i class="bi bi-person-badge me-1"></i> Data Jabatan
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $tab==='password'?'active':'' ?>" href="profil.php?tab=password">
          <i class="bi bi-shield-lock me-1"></i> Ganti Password
        </a>
      </li>
    </ul>

    <div class="card" style="border-radius:0 0 12px 12px">
      <div class="card-body p-4">

        <?php if ($tab === 'profil'): ?>
        <form method="POST">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:13px">Nama Lengkap <span class="text-danger">*</span></label>
              <input type="text" name="nama_lengkap" class="form-control"
                     value="<?= htmlspecialchars($kepala['nama_lengkap'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:13px">NIP</label>
              <input type="text" name="nip" class="form-control" maxlength="30"
                     value="<?= htmlspecialchars($kepala['nip'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:13px">Mulai Menjabat</label>
              <input type="date" name="mulai_jabatan" class="form-control"
                     value="<?= $kepala['mulai_jabatan'] ?? '' ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:13px">Jabatan</label>
              <input type="text" name="jabatan" class="form-control"
                     value="<?= htmlspecialchars($kepala['jabatan'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:13px">Pangkat</label>
              <input type="text" name="pangkat" class="form-control"
                     value="<?= htmlspecialchars($kepala['pangkat'] ?? '') ?>"
                     placeholder="cth: Pembina">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" style="font-size:13px">Golongan</label>
              <input type="text" name="golongan" class="form-control"
                     value="<?= htmlspecialchars($kepala['golongan'] ?? '') ?>"
                     placeholder="cth: IV/a">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:13px">Email</label>
              <input type="email" class="form-control bg-light"
                     value="<?= htmlspecialchars($kepala['email'] ?? '') ?>" disabled>
              <small class="text-muted">Email tidak dapat diubah.</small>
            </div>
            <div class="col-12">
              <button type="submit" name="simpan_profil" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
              </button>
            </div>
          </div>
        </form>

        <?php else: ?>
        <form method="POST">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:13px">Password Lama <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" name="password_lama" id="pass_lama" class="form-control" required>
                <button type="button" class="btn btn-outline-secondary toggle-pass" data-target="pass_lama"><i class="bi bi-eye"></i></button>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:13px">Password Baru <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" name="password_baru" id="pass_baru" class="form-control"
                       placeholder="Minimal 6 karakter" required>
                <button type="button" class="btn btn-outline-secondary toggle-pass" data-target="pass_baru"><i class="bi bi-eye"></i></button>
              </div>
              <div id="strength" class="mt-1" style="font-size:11px"></div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold" style="font-size:13px">Konfirmasi Password <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" name="konfirmasi" id="pass_konfirm" class="form-control" required>
                <button type="button" class="btn btn-outline-secondary toggle-pass" data-target="pass_konfirm"><i class="bi bi-eye"></i></button>
              </div>
              <div id="match" class="mt-1" style="font-size:11px"></div>
            </div>
            <div class="col-12">
              <button type="submit" name="ganti_password" class="btn btn-primary px-4">
                <i class="bi bi-shield-check me-1"></i> Simpan Password Baru
              </button>
            </div>
          </div>
        </form>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.toggle-pass').forEach(btn => {
  btn.addEventListener('click', function() {
    const input = document.getElementById(this.dataset.target);
    const icon  = this.querySelector('i');
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
  });
});
const passBaru = document.getElementById('pass_baru');
if (passBaru) {
  passBaru.addEventListener('input', function() {
    const v = this.value;
    let skor = 0;
    if (v.length >= 6) skor++; if (v.length >= 10) skor++;
    if (/\d/.test(v)) skor++; if (/[A-Z]/.test(v)) skor++;
    const label=['','Lemah','Cukup','Sedang','Kuat'];
    const color=['','#dc3545','#fd7e14','#ffc107','#28a745'];
    document.getElementById('strength').innerHTML = skor>0?`<span style="color:${color[skor]}">Kekuatan: ${label[skor]}</span>`:'';
    cekMatch();
  });
  document.getElementById('pass_konfirm').addEventListener('input', cekMatch);
  function cekMatch() {
    const p=passBaru.value, k=document.getElementById('pass_konfirm').value;
    const el=document.getElementById('match');
    if (!k){el.textContent='';return;}
    el.innerHTML=p===k?'<span style="color:#198754">✓ Password cocok</span>':'<span style="color:#dc3545">✗ Tidak cocok</span>';
  }
}
</script>

<?php include 'footer.php'; ?>
