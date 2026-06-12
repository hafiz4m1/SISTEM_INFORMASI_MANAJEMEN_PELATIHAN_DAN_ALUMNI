  </div><!-- end content-area -->

  <!-- Footer dalam dashboard -->
  <footer style="background:#fff;border-top:1px solid #f0f0f0;padding:12px 24px;
    display:flex;align-items:center;justify-content:space-between;font-size:12px;color:#9ca3af">
    <span>&copy; <?= date('Y') ?> BPPMDDTT Banjarmasin · Sistem Informasi Manajemen Pelatihan &amp; Alumni</span>
    <span>Dikembangkan oleh M. Hafiz Nuril Ikhsan</span>
  </footer>

</div><!-- end main -->

<!-- Loading Overlay -->
<div id="loading-overlay">
  <div class="spinner"></div>
  <p>Memproses...</p>
</div>

<!-- Scroll to top -->
<button id="scroll-top" title="Kembali ke atas">
  <i class="bi bi-arrow-up"></i>
</button>

<!-- Modal Konfirmasi Hapus Global -->
<div id="modalKonfirmasiHapus" class="modal fade modal-hapus" tabindex="-1">
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
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <a id="hapus-btn" href="#" class="btn btn-danger btn-sm">
          <i class="bi bi-trash me-1"></i> Hapus
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/global.js"></script>
</body>
</html>
