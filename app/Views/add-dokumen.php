<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Dokumen - Tambah Dokumen</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 600px;
        }

        .form-section {
            padding: 40px;
            background: white;
        }

        .illustration-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .form-title {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .form-subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-input:focus {
            outline: none;
            border-color: #e53e3e;
            background: white;
            box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
        }

        .form-input::placeholder {
            color: #a0aec0;
        }

        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            background: #f8fafc;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: #e53e3e;
            background: white;
            box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-input {
            display: none;
        }

        .file-label {
            display: block;
            padding: 12px 16px;
            border: 2px dashed #cbd5e0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .file-label:hover {
            border-color: #e53e3e;
            background: #fff5f5;
        }

        .file-text {
            color: #718096;
            font-size: 14px;
        }

        .file-formats {
            color: #e53e3e;
            font-size: 12px;
            margin-top: 4px;
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(229, 62, 62, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* File Upload Styles - Simplified */
        .upload-area {
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #f8fafc;
        }

        .upload-area:hover {
            border-color: #e53e3e;
            background: #fff5f5;
        }

        .upload-area.dragover {
            border-color: #e53e3e;
            background: #fff5f5;
            transform: scale(1.02);
        }

        .upload-icon {
            width: 32px;
            height: 32px;
            margin: 0 auto 12px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>') no-repeat center;
            background-size: contain;
            opacity: 0.5;
        }

        .upload-text {
            margin-bottom: 8px;
            color: #718096;
            font-size: 14px;
        }

        .choose-file-btn {
            background-color: #e53e3e;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-right: 8px;
        }

        .choose-file-btn:hover {
            background-color: #c53030;
        }

        .no-file-text {
            color: #a0aec0;
            font-size: 14px;
        }

        .file-info {
            margin-top: 12px;
            padding: 12px;
            background-color: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            display: none;
        }

        .file-info.show {
            display: block;
        }

        .file-details {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .file-icon {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }

        .file-icon.pdf {
            background-color: #dc3545;
        }

        .file-icon.image {
            background-color: #28a745;
        }

        .file-text-info {
            flex: 1;
        }

        .file-name {
            font-weight: 500;
            color: #2d3748;
            margin-bottom: 2px;
            font-size: 14px;
        }

        .file-size {
            font-size: 12px;
            color: #718096;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #e53e3e;
            cursor: pointer;
            font-size: 16px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .remove-btn:hover {
            background-color: #fed7d7;
        }

        .file-requirements {
            margin-top: 12px;
            padding: 8px 12px;
            background-color: #e3f2fd;
            border-radius: 6px;
            border-left: 4px solid #2196f3;
        }

        .requirements-title {
            font-size: 12px;
            font-weight: 600;
            color: #1976d2;
            margin-bottom: 4px;
        }

        .requirements-text {
            font-size: 11px;
            color: #1976d2;
            line-height: 1.4;
        }

        .error-message {
            margin-top: 8px;
            padding: 8px 12px;
            background-color: #fed7d7;
            border: 1px solid #feb2b2;
            border-radius: 6px;
            color: #c53030;
            font-size: 12px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .success-message {
            margin-top: 8px;
            padding: 8px 12px;
            background-color: #c6f6d5;
            border: 1px solid #9ae6b4;
            border-radius: 6px;
            color: #2f855a;
            font-size: 12px;
            display: none;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-section">
            <h1 class="form-title">Kelola Dokumen</h1>
            <p class="form-subtitle">Tambah Dokumen</p>
            
            <form id="addDocumentForm">
                <div class="form-group">
                    <label class="form-label" for="fakultas-direktorat">Fakultas/Direktorat</label>
                    <input type="text" id="fakultas-direktorat" name="fakultas-direktorat" class="form-input" placeholder="Tulis Fakultas disini..." required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="bagian">Bagian/Unit/Program Studi</label>
                    <input type="text" id="bagian" name="bagian" class="form-input" placeholder="Tulis Bagian disini..." required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nama-dokumen">Nama Dokumen</label>
                    <input type="text" id="nama-dokumen" name="nama-dokumen" class="form-input" placeholder="Tulis Nama Dokumen disini..." required>
                </div>
                    
                <div class="form-group">
                    <label class="form-label" for="revisi-dokumen">Revisi Dokumen</label>
                    <input type="text" id="revisi-dokumen" name="revisi-dokumen" class="form-input" placeholder="Tulis Revisi Dokumen disini..." required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="jenis-dokumen">Jenis Dokumen</label>
                    <input type="text" id="jenis-dokumen" name="jenis-dokumen" class="form-input" placeholder="Tulis Jenis Dokumen disini..." required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="keterangan">Keterangan</label>
                    <input type="text" id="keterangan" name="keterangan" class="form-input" placeholder="Tulis Keterangan disini..." required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="file-upload">Unggah Berkas</label>
                    
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon"></div>
                        <div class="upload-text">
                            <button type="button" class="choose-file-btn" id="chooseFileBtn">Choose File</button>
                            <span class="no-file-text" id="noFileText">No file chosen</span>
                        </div>
                        <p style="font-size: 12px; color: #a0aec0; margin-top: 6px;">
                            atau seret dan lepas file di sini
                        </p>
                    </div>

                    <input type="file" id="fileInput" class="file-input" accept=".jpg,.jpeg,.png,.pdf">

                    <div class="file-info" id="fileInfo">
                        <div class="file-details">
                            <div class="file-icon" id="fileIcon"></div>
                            <div class="file-text-info">
                                <div class="file-name" id="fileName"></div>
                                <div class="file-size" id="fileSize"></div>
                            </div>
                            <button type="button" class="remove-btn" id="removeBtn" title="Hapus file">×</button>
                        </div>
                    </div>

                    <div class="error-message" id="errorMessage"></div>
                    <div class="success-message" id="successMessage"></div>

                    <div class="file-requirements">
                        <div class="requirements-title"></div>
                        <div class="requirements-text">
                            File Upload .doc, .xslx, .pdf<br>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Submit</button>
            </form>
        </div>

        <div class="illustration-section">
            <img src="<?= base_url('assets/images/profil/profil.jpg') ?>" alt="User Illustration" style="max-width: 80%; max-height: 80%; border-radius: 16px;">
        </div>
    </div>
</body>
</html>
<?= $this->endSection() ?>