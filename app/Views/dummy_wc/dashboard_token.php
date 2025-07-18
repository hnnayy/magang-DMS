<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generated Token</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-4">
<div class="container shadow rounded bg-white">
    <!-- Header -->
    <div class="bg-primary text-white p-3 d-flex align-items-center gap-2 rounded-top">
        <div class="badge bg-white text-primary rounded-circle fw-bold">✓</div>
        <h5 class="mb-0">Generated Token</h5>
    </div>

    <!-- User Info -->
    <div class="d-flex justify-content-between align-items-center p-3 border-bottom flex-wrap">
        <div class="d-flex align-items-center gap-2 mb-2 mb-md-0">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <?= strtoupper(substr($username, 0, 1)) ?>
            </div>
            <span class="fw-medium">@<?= esc($username) ?></span>
        </div>
        <div class="d-flex gap-3">
            <span class="badge bg-warning text-dark">Expires in <span id="timeLeft"><?= esc($time_left) ?></span></span>
            <span id="tokenStatus" class="badge bg-success"><?= $is_valid ? 'Valid Token' : 'Expired Token' ?></span>
        </div>
    </div>

    <div class="p-4">
        <!-- Token Structure -->
        <h6>Token Structure</h6>
        <p class="text-muted mb-2">Complete JWT token breakdown with header, payload, and signature</p>

        <!-- JSON Payload (Accordion) -->
        <div class="accordion mb-4" id="jsonAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="jsonHeading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#jsonCollapse">
                        📄 JSON Structure & Field Descriptions
                    </button>
                </h2>
                <div id="jsonCollapse" class="accordion-collapse collapse" data-bs-parent="#jsonAccordion">
                    <div class="accordion-body">
                        <pre class="bg-light p-3 rounded small text-break">
{
  "iss": "<?= esc($decoded_payload->iss) ?>",
  "sub": "<?= esc($decoded_payload->sub) ?>",
  "iat": <?= $decoded_payload->iat ?>,
  "exp": <?= $decoded_payload->exp ?>,
  "role_id": "<?= esc($decoded_payload->role_id) ?>"
}
                        </pre>
                        <ul class="small text-muted">
                            <li><strong>iss</strong>: Token issuer (dummy-login)</li>
                            <li><strong>sub</strong>: Subject/Username</li>
                            <li><strong>iat</strong>: Issued at timestamp</li>
                            <li><strong>exp</strong>: Expiration timestamp</li>
                            <li><strong>role_id</strong>: User role identifier</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- JWT Token -->
        <h6>JWT Token</h6>
        <div class="position-relative bg-light p-3 rounded mb-4 border text-break">
            <code class="d-block small text-break"><?= esc($token) ?></code>
            <button class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-2" onclick="copyToClipboard(`<?= esc($token) ?>`)">📋 Copy</button>
        </div>

        <!-- Access URL -->
        <h6>Access URL</h6>
        <div class="bg-light p-3 rounded mb-2 border small text-break">
            <?= base_url() ?>/parse-token?token=<?= esc($token) ?>
        </div>
        <div class="d-flex gap-2 mb-4">
            <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('<?= base_url() ?>/parse-token?token=<?= esc($token) ?>')">📋 Copy</button>
            <a href="<?= base_url() ?>/parse-token?token=<?= esc($token) ?>&redirect=dashboard" class="btn btn-sm btn-outline-primary">🔗 Open</a>
        </div>

        <!-- Token Info -->
        <h6>Token Information</h6>
        <div class="bg-light p-3 rounded border">
            <div class="row row-cols-1 row-cols-md-2 small g-3">
                <div>
                    <strong>Username:</strong> <?= esc($username) ?><br>
                    <strong>Full Name:</strong> <?= esc($fullname) ?><br>
                    <strong>Role ID:</strong> <?= esc($role_id) ?>
                </div>
                <div>
                    <strong>Expires:</strong> <?= esc($expiry_time) ?><br>
                    <strong>Time Left:</strong> <span id="infoTimeLeft"><?= esc($time_left) ?></span><br>
                    <strong>Status:</strong> <?= $is_valid ? 'Valid' : 'Expired' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Copy Function + Time Left -->
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            const btn = event.target;
            const original = btn.textContent;
            btn.textContent = '✓ Copied';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success', 'text-white');

            setTimeout(() => {
                btn.textContent = original;
                btn.classList.remove('btn-success', 'text-white');
                btn.classList.add('btn-outline-secondary');
            }, 2000);
        });
    }

    const exp = <?= $decoded_payload->exp ?>;
    setInterval(() => {
        const now = Math.floor(Date.now() / 1000);
        const left = exp - now;
        const display = left > 0 ? `${left}s` : 'Expired';

        document.getElementById('timeLeft').textContent = display;
        document.getElementById('infoTimeLeft').textContent = display;

        if (left <= 0) {
            document.getElementById('tokenStatus').textContent = 'Expired Token';
            document.getElementById('tokenStatus').className = 'badge bg-danger';
        }
    }, 1000);
</script>
</body>
</html>
