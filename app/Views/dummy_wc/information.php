<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= isset($title) ? $title : 'Information Page' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container py-3">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        
        <!-- Header Section -->
        <div class="text-center mb-3">
          <div class="mb-2">
            <i class="bi bi-info-circle text-danger" style="font-size: 3rem;"></i>
          </div>
          <h1 class="display-5 fw-bold text-danger mb-2">Information Page</h1>
          <p class="lead text-muted">Document Management System (DMS) Information Page</p>
        </div>

        <!-- Features Section -->
        <div class="row mb-3">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-gear me-2"></i>System Features</h5>
              </div>
              <div class="card-body">
                <div class="row g-2">
                  <div class="col-md-6">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-check-circle-fill text-success me-2"></i>
                      <span>Digital document management</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-check-circle-fill text-success me-2"></i>
                      <span>File upload and download</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-check-circle-fill text-success me-2"></i>
                      <span>Document categorization system</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-check-circle-fill text-success me-2"></i>
                      <span>User access control</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-check-circle-fill text-success me-2"></i>
                      <span>Document activity tracking</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-check-circle-fill text-success me-2"></i>
                      <span>Data backup and restore</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- System Overview -->
        <div class="row mb-3">
          <div class="col-md-4 mb-2">
            <div class="card h-100 shadow-sm">
              <div class="card-body text-center">
                <i class="bi bi-cloud-upload text-danger mb-2" style="font-size: 2rem;"></i>
                <h6 class="card-title">Document Upload</h6>
                <p class="card-text text-muted">Securely and easily upload various types of documents</p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-2">
            <div class="card h-100 shadow-sm">
              <div class="card-body text-center">
                <i class="bi bi-folder2-open text-success mb-2" style="font-size: 2rem;"></i>
                <h6 class="card-title">File Management</h6>
                <p class="card-text text-muted">Organize documents with a structured folder system</p>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-2">
            <div class="card h-100 shadow-sm">
              <div class="card-body text-center">
                <i class="bi bi-shield-lock text-warning mb-2" style="font-size: 2rem;"></i>
                <h6 class="card-title">Data Security</h6>
                <p class="card-text text-muted">Access control and automatic backups for maximum security</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="row">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-telephone me-2"></i>Contact Information</h5>
              </div>
              <div class="card-body">
                <div class="row align-items-start">
                  <div class="col-md-4 mb-1">
                    <div class="d-flex align-items-start">
                      <i class="bi bi-envelope-fill text-danger me-2 fs-5 mt-1"></i>
                      <div>
                        <strong>Email:</strong>
                        <p class="mb-0 text-muted">infoceloe@telkomuniversity.ac.id</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mb-1">
                    <div class="d-flex align-items-start">
                      <i class="bi bi-phone-fill text-success me-2 fs-5 mt-1"></i>
                      <div>
                        <strong>Phone:</strong>
                        <p class="mb-0 text-muted">+62 821-1666-3563</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mb-1">
                    <div class="d-flex align-items-start">
                      <i class="bi bi-geo-alt-fill text-danger me-2 fs-5 mt-1"></i>
                      <div>
                        <strong>Address:</strong>
                        <p class="mb-0 text-muted">Bangkit Building, Telkom University, Jl. Telekomunikasi, Terusan Buah Batu, Bandung, Indonesia 40257</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>