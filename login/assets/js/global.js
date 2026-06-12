/**
 * global.js - Script global untuk semua halaman
 * Taruh di login/assets/js/global.js
 * Include di semua footer.php:
 * <script src="../assets/js/global.js"></script>
 */

// ===== LOADING OVERLAY =====
function showLoading(pesan = 'Memproses...') {
    let el = document.getElementById('loading-overlay');
    if (!el) {
        el = document.createElement('div');
        el.id = 'loading-overlay';
        el.innerHTML = `<div class="spinner"></div><p>${pesan}</p>`;
        document.body.appendChild(el);
    }
    el.querySelector('p').textContent = pesan;
    el.classList.add('show');
}
function hideLoading() {
    const el = document.getElementById('loading-overlay');
    if (el) el.classList.remove('show');
}

// Tampilkan loading saat form submit
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function () {
            // Jangan tampilkan loading untuk form search/filter
            if (this.method.toLowerCase() === 'get') return;
            showLoading('Menyimpan data...');
        });
    });

    // Tampilkan loading saat klik link aksi (hapus, terima, tolak)
    document.querySelectorAll('a[href*="hapus="], a[href*="aksi=terima"], a[href*="aktifkan="]').forEach(a => {
        a.addEventListener('click', function () {
            showLoading('Memproses...');
        });
    });
});

// ===== TOAST NOTIFIKASI =====
function showToast(pesan, tipe = 'success', durasi = 4000) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const icons = {
        success: 'bi-check-circle-fill text-success',
        danger:  'bi-x-circle-fill text-danger',
        warning: 'bi-exclamation-triangle-fill text-warning',
        info:    'bi-info-circle-fill text-primary',
    };

    const toast = document.createElement('div');
    toast.className = `toast-custom toast-${tipe}`;
    toast.innerHTML = `
        <i class="bi ${icons[tipe] || icons.info} toast-icon"></i>
        <div class="toast-msg">${pesan}</div>
        <i class="bi bi-x toast-close" onclick="this.parentElement.remove()"></i>
    `;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity .3s';
        setTimeout(() => toast.remove(), 300);
    }, durasi);
}

// Auto-dismiss alert Bootstrap
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.alert.alert-success, .alert.alert-info').forEach(alert => {
        setTimeout(() => {
            if (alert && alert.parentNode) {
                alert.style.transition = 'opacity .5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 4000);
    });
});

// ===== MODAL KONFIRMASI HAPUS =====
function konfirmasiHapus(url, nama, callback) {
    // Buat modal jika belum ada
    let modal = document.getElementById('modalKonfirmasiHapus');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'modalKonfirmasiHapus';
        modal.className = 'modal fade modal-hapus';
        modal.setAttribute('tabindex', '-1');
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header justify-content-end">
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <i class="bi bi-trash3-fill icon-hapus"></i>
                        <h6 class="fw-bold mb-2">Hapus Data?</h6>
                        <p id="hapus-nama" style="font-size:13px;color:#6b7280;margin:0"></p>
                        <p style="font-size:12px;color:#dc3545;margin-top:8px">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <a id="hapus-btn" href="#" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Hapus
                        </a>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(modal);
    }

    document.getElementById('hapus-nama').textContent = nama ? `Data "${nama}" akan dihapus permanen.` : 'Data ini akan dihapus permanen.';
    document.getElementById('hapus-btn').href = url;

    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    return false;
}

// ===== RESPONSIVE SIDEBAR (HAMBURGER) =====
document.addEventListener('DOMContentLoaded', function () {
    const sidebar  = document.getElementById('sidebar');
    const hamburger = document.getElementById('hamburger');

    if (!sidebar) return;

    // Buat overlay
    let overlay = document.getElementById('overlay-sidebar');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'overlay-sidebar';
        document.body.appendChild(overlay);
    }

    if (hamburger) {
        hamburger.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        });
    }

    overlay.addEventListener('click', function () {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
    });
});

// ===== SCROLL TO TOP =====
document.addEventListener('DOMContentLoaded', function () {
    let btn = document.getElementById('scroll-top');
    if (!btn) {
        btn = document.createElement('button');
        btn.id = 'scroll-top';
        btn.innerHTML = '<i class="bi bi-arrow-up"></i>';
        btn.title = 'Kembali ke atas';
        document.body.appendChild(btn);
    }

    const main = document.getElementById('main') || window;
    const scrollEl = document.getElementById('main') || document.documentElement;

    scrollEl.addEventListener('scroll', function () {
        btn.classList.toggle('show', scrollEl.scrollTop > 200);
    });

    btn.addEventListener('click', function () {
        scrollEl.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

// ===== BREADCRUMB HELPER =====
function setBreadcrumb(items) {
    let bar = document.querySelector('.breadcrumb-bar');
    if (!bar) {
        bar = document.createElement('div');
        bar.className = 'breadcrumb-bar';
        const topbar = document.getElementById('topbar');
        if (topbar) topbar.after(bar);
    }
    bar.innerHTML = '<i class="bi bi-house me-1"></i>' +
        items.map((item, i) =>
            i < items.length - 1
                ? `<a href="${item.url}">${item.label}</a><span class="sep">/</span>`
                : `<span>${item.label}</span>`
        ).join('');
}

// ===== KONFIRMASI SEBELUM TUTUP TAB (jika ada form yang diisi) =====
document.addEventListener('DOMContentLoaded', function () {
    let formDirty = false;
    document.querySelectorAll('form input, form textarea, form select').forEach(el => {
        el.addEventListener('change', () => { formDirty = true; });
    });
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => { formDirty = false; });
    });
    window.addEventListener('beforeunload', function (e) {
        if (formDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});
