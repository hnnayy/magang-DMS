<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Dokumen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.5;
        }

        .container {
            max-width: 1000px;
            margin: 5 auto;
            padding: 2rem;
        }

        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .filter-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #2d3748;
        }

        .filter-controls {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0rem;
        }

        .filter-label {
            font-size: 0.7rem;
            font-weight: 500;
            color: #4a5568;
        }

        .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.7rem;
            background: white;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .filter-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .filter-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }

        .filter-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .main-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .section-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f7fafc;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #2d3748;
        }

        .section-subtitle {
            font-size: 0.7rem;
            color: #718096;
            margin-top: 0.25rem;
        }

        .section-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 50%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .table-header {
            background: #f7fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .table-header th {
            padding: 2rem;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            white-space: nowrap;
        }

        .table-row {
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.2s;
        }

        .table-row:hover {
            background-color: #f8fafc;
        }

        .table-cell {
            padding: 0.75rem;
            vertical-align: middle;
            color: #2d3748;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0rem;
            padding: 0rem 0.5rem;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .revision-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .actions {
            display: flex;
            gap: 0.25rem;
        }

        .action-btn {
            padding: 0rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn:hover {
            transform: scale(1.05);
        }

        .edit-btn {
            background: #dbeafe;
            color: #1e40af;
        }

        .edit-btn:hover {
            background: #bfdbfe;
        }

        .delete-btn {
            background: #fee2e2;
            color: #dc2626;
        }

        .delete-btn:hover {
            background: #fecaca;
        }

        .table-header {
            background: #f7fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .document-type {
            display: flex;
            align-items: center;
            gap: 0rem;
            font-weight: 500;
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0rem;
            }

            .filter-controls {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .section-controls {
                justify-content: space-between;
            }

            .search-box {
                width: 100%;
            }
        }
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            border-top: 1px solid #e2e8f0;
            background: #fafbfc;
        }
        .pagination {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.75rem;
            text-decoration: none;
            border: 1px solid #e2e8f0;
            color: #4a5568;
            background-color: #fff;
            border-radius: 6px;
            transition: all 0.2s ease;
            font-size: 0.7rem;
            font-weight: 500;
            min-width: 36px;
            height: 36px;
        }

        .pagination a:hover {
            background-color: #f8f9fa;
            border-color: #cbd5e0;
            color: #2d3748;
            text-decoration: none;
        }

        .pagination .current {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border-color: #dc3545;
            font-weight: 600;
        }

        .pagination .disabled {
            color: #a0aec0;
            background-color: #f7fafc;
            border-color: #e2e8f0;
            cursor: not-allowed;
        }
        .pagination-container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
        }
        .pagination-container {
                padding: 1rem;
        }
    </style>
</head>
<body>
<div class="container">
        <div class="filter-section">
            <h2 class="filter-title">Filter Data</h2>
            <div class="filter-controls">
                <div class="filter-group">
                    <label class="filter-label">Standar</label>
                    <select class="filter-select">
                        <option>Semua Standar</option>
                        <option>8.6</option>
                        <option>9.1</option>
                        <option>7.5</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Klausul</label>
                    <select class="filter-select">
                        <option>Semua Klausul</option>
                        <option>Prosedur</option>
                        <option>Instruksi Kerja</option>
                        <option>Form</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Pemilik Dokumen</label>
                    <select class="filter-select">
                        <option>Semua pemilik doc</option>
                        <option>Prosedur Metrologi</option>
                        <option>Quality Assurance</option>
                        <option>Technical</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Jenis Dokumen</label>
                    <select class="filter-select">
                        <option>Semua jenis doc</option>
                        <option>Prosedur</option>
                        <option>Instruksi</option>
                        <option>Form</option>
                    </select>
                </div>
                <button class="filter-button">
                    Filter
                </button>
            </div>
        </div>

        <div class="main-section">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Daftar Dokumen</h2>
                    <p class="section-subtitle">Online Database of Unit Test DALCE</p>
                </div>
                <div class="section-controls">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <button class="btn btn-success">
                            <img src="https://img.icons8.com/color/16/microsoft-excel-2019.png"/> Export to Excel
                        </button>
                    </div>
                    <div class="search-box">
                        <input type="text" class="search-input" placeholder="Search..." />
                    </div>
                </div>
            </div>

 <div class="table-container">
                <table class="data-table">
                    <thead class="table-header">
                        <tr>
                            <th class="col-no">No</th>
                            <th class="col-standar">Standar</th>
                            <th class="col-klausul">Klausul</th>
                            <th class="col-jenis">Jenis Dokumen</th>
                            <th class="col-nama">Nama Dokumen</th>
                            <th class="col-pemilik">Pemilik Dokumen</th>
                            <th class="col-file">File Dokumen</th>
                            <th class="col-revisi">Revisi</th>
                            <th class="col-tanggal">Tanggal Efektif</th>
                            <th class="col-update">Last Update</th>
                            <th class="col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-row">
                            <td class="table-cell">1</td>
                            <td class="table-cell">8.6</td>
                            <td class="table-cell">Prosedur</td>
                            <td class="table-cell">
                                <div class="document-type">
                                    <span>Prosedur Metrologi</span>
                                </div>
                            </td>
                            <td class="table-cell">Prosedur Metrologi</td>
                            <td class="table-cell">Layanan CELOE</td>
                            <td class="table-cell">
                                <div class="actions">
                                    <button class="action-btn edit-btn" title="Edit">
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-download"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                                    </button>
                                </div>
                            </td>
                            <td class="table-cell">
                                <span class="revision-badge">00</span>
                            </td>
                            <td class="table-cell">12 Jun 2024</td>
                            <td class="table-cell">
                                <div>12 Aug 2024</div>
                                <div style="font-size: 0.75rem; color: #718096;">10:30 AM</div>
                            </td>
                            <td class="table-cell">
                                <div class="actions">
                                    <button class="action-btn edit-btn" title="Edit">
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-edit"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                    </button>
                                    <button class="action-btn delete-btn" title="Delete">
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="table-row">
                            <td class="table-cell">2</td>
                            <td class="table-cell">9.1</td>
                            <td class="table-cell">Instruksi</td>
                            <td class="table-cell">
                                <div class="document-type">
                                    <span>Instruksi Kalibrasi</span>
                                </div>
                            </td>
                            <td class="table-cell">Instruksi Kalibrasi</td>
                            <td class="table-cell">Quality Assurance</td>
                            <td class="table-cell">
                                <div class="actions">
                                    <button class="action-btn edit-btn" title="Edit">
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-download"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                                    </button>
                                </div>
                            </td>
                            <td class="table-cell">
                                <span class="revision-badge">01</span>
                            </td>
                            <td class="table-cell">15 Jun 2024</td>
                            <td class="table-cell">
                                <div>20 Aug 2024</div>
                                <div style="font-size: 0.75rem; color: #718096;">02:15 PM</div>
                            </td>
                            <td class="table-cell">
                                <div class="actions">
                                    <button class="action-btn edit-btn" title="Edit">
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-edit"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                    </button>
                                    <button class="action-btn delete-btn" title="Delete">
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination Section -->
            <div class="pagination-container">
                <div class="showing-info">
                    Show 1 to 7 from 30 Entries
                </div>
                
                <div class="pagination">
                    <span class="disabled">Previous</span>
                    <span class="current">1</span>
                    <a href="#">2</a>
                    <a href="#">3</a>
                    <a href="#">4</a>
                    <a href="#">5</a>
                    <a href="#">Next</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?= $this->endSection() ?>