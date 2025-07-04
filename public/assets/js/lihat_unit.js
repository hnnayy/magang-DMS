function openEditModal(id, parentName, unitName) {
    const form = document.getElementById('editUnitForm');
    form.action = `/data-master/unit/${id}/update`;
    document.getElementById('editUnitId').value = id;
    document.getElementById('editParentName').value = parentName;
    document.getElementById('editUnitName').value = unitName;
}

function SwalConfirmDelete(elem) {
    event.preventDefault();
    Swal.fire({
        title: 'Hapus unit ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            elem.closest('form').submit();
        }
    });
}

// DataTables init
$(function () {
    const dt = $('#documentTable').DataTable({
        dom: 'lrtip',
        pageLength: 10,
        order: [],
        columnDefs: [{ orderable:false, targets: 3 }],
        buttons: [
            {
                extend: 'copyHtml5',
                text: 'Copy',
                className: "btn btn-purple border me-2"
            },
            {
                extend: 'csvHtml5',
                text: 'CSV',
                className: "btn btn-purple border me-2"
            },
            {
                extend: 'excelHtml5',
                text: 'Excel',
                className: "btn btn-purple border me-2"
            },
            {
                extend: 'pdfHtml5',
                text: 'PDF',
                className: "btn btn-purple border me-2"
            },
            {
                extend: 'print',
                text: 'Print',
                className: "btn btn-purple border me-2"
            },
        ]
    });

    dt.buttons().container().appendTo('.export-buttons');

    $('#searchInput').on('keyup', function () {
        dt.search(this.value).draw();
    });

    $('#searchBtn').on('click', function () {
        dt.search($('#searchInput').val()).draw();
    });
});
