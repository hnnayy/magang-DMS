<form method="post" action="/kelola-dokumen/approve-pengajuan">
    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
    <input type="hidden" name="document_id" id="approveDocumentId">
    <input type="hidden" name="approved_by" value="<?= session('user_id') ?>">
    <input type="hidden" name="approval_date" value="<?= date('Y-m-d') ?>">

    <textarea name="remarks"></textarea>

    <div class="modal-footer border-top-0 pt-0">
        <div class="row w-100">
            <div class="col-6 pe-1">
                <button type="submit" name="action" value="disapprove" class="btn w-100 text-white" style="background-color: #b41616;">
                    Not Approve
                </button>
            </div>
            <div class="col-6 ps-1">
                <button type="submit" name="action" value="approve" class="btn btn-success w-100">
                    <i class="bi bi-check-lg me-2"></i>Approve
                </button>
            </div>
        </div>
    </div>
</form>
