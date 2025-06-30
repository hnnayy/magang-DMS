<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - Telkom University</title>
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

        /* Illustration styles */
        .illustration {
            position: relative;
            width: 300px;
            height: 300px;
        }

        .user-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #667eea;
        }

        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .floating-card {
            position: absolute;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: float 3s ease-in-out infinite;
        }

        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                margin: 10px;
            }
            
            .illustration-section {
                order: -1;
                min-height: 200px;
            }
            
            .form-section {
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-section">
            <h1 class="form-title">Create User</h1>
            <p class="form-subtitle">Tambah User</p>
            
            <form id="createUserForm">
                <div class="form-group">
                    <label class="form-label" for="type">Type</label>
                    <select id="type" name="type" class="form-select" required>
                        <option value="" disabled selected hidden>Please Select User Type</option>
                        <option value="type1">Type1</option>
                        <option value="type2">Type2</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-input" placeholder="Tulis Username disini..." required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Tulis Password disini..." required>
                </div>
                    
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" placeholder="Tulis Email disini..." required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="jabatan">Jabatan</label>
                    <input type="text" id="jabatan" name="jabatan" class="form-input" placeholder="Tulis jabatan disini..." required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="institusi">Institusi</label>
                    <input type="text" id="institusi" name="institusi" class="form-input" placeholder="Tulis institusi disini..." required>
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