<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dummy WC Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-sm p-4" style="max-width: 500px; width: 100%; border-radius: 1rem;">
      <h4 class="mb-3 text-center">Dummy WC Login</h4>
      <p class="text-center text-muted mb-4">Masukkan username Anda untuk login ke DMS</p>

      <!-- Tampilkan Flash Message -->
      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
          <?= session()->getFlashdata('error') ?>
        </div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-warning">
          <?= session()->getFlashdata('message') ?>
        </div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
          <?= session()->getFlashdata('success') ?>
        </div>
      <?php endif; ?>

      <!-- Form Login -->
      <form method="POST" action="<?= base_url('wc-dummy/login') ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
          <input 
            type="text" 
            class="form-control" 
            name="username" 
            id="username" 
            placeholder="Masukkan username anda..." 
            required 
          >
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary">
            Login DMS
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
