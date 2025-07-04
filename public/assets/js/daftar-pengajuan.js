// Event: Saat tombol Edit diklik, isi form di modal Edit
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('editDocumentId').value = btn.dataset.id;
        document.getElementById('editFakultas').value = btn.dataset.fakultas;
        document.getElementById('editBagian').value = btn.dataset.bagian;
        document.getElementById('editNama').value = btn.dataset.nama;
        document.getElementById('editNomor').value = btn.dataset.nomor;
        document.getElementById('editRevisi').value = btn.dataset.revisi;
        document.getElementById('editJenis').value = btn.dataset.jenis;
        document.getElementById('editKode').value = btn.dataset.kode;
        document.getElementById('editKeterangan').value = btn.dataset.keterangan;
    });
});

// Event: Saat tombol Approve diklik, isi input hidden dengan ID dokumen
document.querySelectorAll('.approve-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('approveDocumentId').value = btn.dataset.id;
    });
});

// Fungsi: Submit form edit dokumen
function handleEditSubmit(e) {
    e.preventDefault();
    alert("Form edit disubmit!");
    // TODO: Tambahkan logika AJAX atau form POST di sini
}

// Fungsi: Submit persetujuan dokumen
function handleApproveSubmit(e) {
    e.preventDefault();
    alert("Dokumen disetujui!");
    // TODO: Tambahkan logika AJAX atau form POST di sini
}

// Fungsi: Konfirmasi dan hapus dokumen
function deleteDocument(id) {
    if (confirm("Yakin ingin menghapus dokumen ini?")) {
        alert("Dokumen dengan ID " + id + " dihapus.");
        // TODO: Tambahkan logika penghapusan (AJAX atau redirect)
    }
}

// Fungsi: Reset filter pencarian
function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterFakultas').value = '';
    document.getElementById('filterJenis').value = '';
}
