/**
 * global.js - BPPMDDTT Banjarmasin
 * Include di semua footer.php: <script src="../assets/js/global.js"></script>
 */

document.addEventListener('DOMContentLoaded', function () {

  // ===== BACKGROUND SLIDESHOW =====
  const bgWrap = document.createElement('div');
  bgWrap.id = 'bg-slideshow';
  bgWrap.innerHTML = '<div class="bg-slide aktif"></div><div class="bg-slide"></div><div class="bg-slide"></div>';
  document.body.prepend(bgWrap);

  const bgOverlay = document.createElement('div');
  bgOverlay.id = 'bg-overlay';
  document.body.prepend(bgOverlay);

  const slides = bgWrap.querySelectorAll('.bg-slide');
  let bgIdx = 0;
  setInterval(function () {
    slides[bgIdx].classList.remove('aktif');
    bgIdx = (bgIdx + 1) % slides.length;
    slides[bgIdx].classList.add('aktif');
  }, 5000);

  // ===== LOADING OVERLAY =====
  const loadingEl = document.createElement('div');
  loadingEl.id = 'loading-overlay';
  loadingEl.innerHTML = '<div class="spinner"></div><p>Memproses...</p>';
  document.body.appendChild(loadingEl);

  // Tampilkan loading saat form POST submit
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
      if (this.method.toLowerCase() !== 'get') {
        loadingEl.classList.add('show');
      }
    });
  });

  // ===== SCROLL TO TOP =====
  const scrollBtn = document.createElement('button');
  scrollBtn.id = 'scroll-top';
  scrollBtn.title = 'Kembali ke atas';
  scrollBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
  document.body.appendChild(scrollBtn);

  const mainEl = document.getElementById('main') || document.documentElement;
  mainEl.addEventListener('scroll', function () {
    scrollBtn.classList.toggle('show', mainEl.scrollTop > 200);
  });
  scrollBtn.addEventListener('click', function () {
    mainEl.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // ===== HAMBURGER SIDEBAR MOBILE =====
  const sidebar = document.getElementById('sidebar');
  const hamburger = document.getElementById('hamburger');
  if (sidebar && hamburger) {
    var overlayEl = document.createElement('div');
    overlayEl.id = 'overlay-sidebar';
    document.body.appendChild(overlayEl);

    hamburger.addEventListener('click', function () {
      sidebar.classList.toggle('open');
      overlayEl.classList.toggle('show');
    });
    overlayEl.addEventListener('click', function () {
      sidebar.classList.remove('open');
      overlayEl.classList.remove('show');
    });
  }

  // ===== AUTO DISMISS ALERT SUCCESS =====
  document.querySelectorAll('.alert-success, .alert-info').forEach(function (el) {
    setTimeout(function () {
      if (el && el.parentNode) {
        el.style.transition = 'opacity .5s';
        el.style.opacity = '0';
        setTimeout(function () { if (el.parentNode) el.remove(); }, 500);
      }
    }, 4000);
  });

  // ===== TOOLTIP Bootstrap =====
  if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
      new bootstrap.Tooltip(el);
    });
  }

  // ===== AVATAR INISIAL di tabel (ganti <img> yang error dengan inisial) =====
  document.querySelectorAll('img.user-avatar').forEach(function (img) {
    img.addEventListener('error', function () {
      var name = this.dataset.name || '?';
      var initials = name.split(' ').map(function (w) { return w[0]; }).slice(0, 2).join('').toUpperCase();
      var div = document.createElement('div');
      div.className = 'avatar-init';
      div.textContent = initials;
      this.parentNode.replaceChild(div, this);
    });
  });

});

// ===== TOAST NOTIFIKASI =====
function showToast(pesan, tipe, durasi) {
  tipe = tipe || 'success';
  durasi = durasi || 4000;
  var container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  var icons = { success: 'bi-check-circle-fill text-success', danger: 'bi-x-circle-fill text-danger', warning: 'bi-exclamation-triangle-fill text-warning', info: 'bi-info-circle-fill text-primary' };
  var toast = document.createElement('div');
  toast.className = 'toast-custom toast-' + tipe;
  toast.innerHTML = '<i class="bi ' + (icons[tipe] || icons.info) + ' toast-icon"></i><div class="toast-msg">' + pesan + '</div><i class="bi bi-x toast-close" onclick="this.parentElement.remove()"></i>';
  container.appendChild(toast);
  setTimeout(function () {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity .3s';
    setTimeout(function () { if (toast.parentNode) toast.remove(); }, 300);
  }, durasi);
}

// ===== KONFIRMASI HAPUS =====
function konfirmasiHapus(url, nama) {
  var modal = document.getElementById('modalKonfirmasiHapus');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'modalKonfirmasiHapus';
    modal.className = 'modal fade';
    modal.setAttribute('tabindex', '-1');
    modal.innerHTML = '<div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content"><div class="modal-header justify-content-end border-0 pb-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body text-center px-4 pb-2"><i class="bi bi-trash3-fill modal-hapus icon-hapus"></i><h6 class="fw-bold mb-1">Hapus Data?</h6><p id="hapus-nama" style="font-size:13px;color:#6b7280;margin:0"></p><p style="font-size:12px;color:#ef4444;margin-top:6px">Tindakan ini tidak dapat dibatalkan.</p></div><div class="modal-footer justify-content-center gap-2"><button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button><a id="hapus-btn" href="#" class="btn btn-danger btn-sm"><i class="bi bi-trash me-1"></i>Hapus</a></div></div></div>';
    document.body.appendChild(modal);
  }
  var namaEl = document.getElementById('hapus-nama');
  if (namaEl) namaEl.textContent = nama ? '"' + nama + '" akan dihapus permanen.' : 'Data ini akan dihapus permanen.';
  var btnEl = document.getElementById('hapus-btn');
  if (btnEl) btnEl.href = url;
  if (typeof bootstrap !== 'undefined') {
    new bootstrap.Modal(modal).show();
  }
  return false;
}

// ===== LOADING HELPER =====
function showLoading(pesan) {
  var el = document.getElementById('loading-overlay');
  if (el) {
    var p = el.querySelector('p');
    if (p) p.textContent = pesan || 'Memproses...';
    el.classList.add('show');
  }
}
function hideLoading() {
  var el = document.getElementById('loading-overlay');
  if (el) el.classList.remove('show');
}
