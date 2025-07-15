<?php
namespace App\Controllers\MasterData;

use App\Controllers\BaseController;
use App\Models\UnitParentModel;
use App\Models\UnitModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Dompdf\Dompdf;

class UnitController extends BaseController
{
    protected $parentModel;
    protected $unitModel;

    public function __construct()
    {
        $this->parentModel = new UnitParentModel();
        $this->unitModel   = new UnitModel();
        helper(['form']);
    }



    /* ───────── LIST ───────── */
    public function index()
    {
        $data['units'] = $this->unitModel->getWithParent();   // join siap pakai
        return view('DataMaster/daftar-unit', $data);
    }

    public function list()
    {
        return $this->index();        // cukup panggil index()
    }

    public function create()
    {
        return view('DataMaster/unit-create');   // file view sudah ada
    }

    /* ---------- SIMPAN TAMBAH UNIT ---------- */
    public function store()
    {
        $rules = [
            'parent_name' => [
                'label'  => 'Fakultas/Direktorat',
                'rules'  => 'required|alpha_space|max_length[40]',
                'errors' => [
                    'required'    => '{field} wajib diisi.',
                    'alpha_space' => '{field} hanya boleh huruf dan spasi.',
                    'max_length'  => '{field} maksimal 40 karakter.',
                ],
            ],
            'unit_name' => [
                'label'  => 'Unit',
                'rules'  => 'required|alpha_space|max_length[40]',
                'errors' => [
                    'required'    => '{field} wajib diisi.',
                    'alpha_space' => '{field} hanya boleh huruf dan spasi.',
                    'max_length'  => '{field} maksimal 40 karakter.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('swal', [
                'icon'  => 'error',
                'title' => 'Oops...',
                'text'  => implode("\n", $this->validator->getErrors()),
            ]);
        }

        // ── cari / buat parent ──
        $parentName = trim($this->request->getPost('parent_name'));
        $parent     = $this->parentModel->where('name', $parentName)->first();

        $parentId = $parent
            ? $parent['id']
            : $this->parentModel->insert([
                'type'   => 2,           // 2 = Fakultas
                'name'   => $parentName,
                'status' => 1,
            ]);

        // ── simpan unit baru ──
        $this->unitModel->insert([
            'parent_id' => $parentId,
            'name'      => $this->request->getPost('unit_name'),
            'status'    => 1,
        ]);

        return redirect()->to('data-master')->with('swal', [
            'icon'  => 'success',
            'title' => 'Berhasil!',
            'text'  => 'Unit berhasil ditambahkan.',
        ]);
    }
    /* ───────── EDIT FORM ───────── */
    public function edit($id)
    {
        $unit = $this->unitModel->find($id);
        if (! $unit) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data['unit']   = $unit;
        $data['parent'] = $this->parentModel->find($unit['parent_id']);

        return view('DataMaster/unit-edit', $data);
    }

    /* ───────── UPDATE ───────── */
    public function update($id)
    {
        $rules = [
            'parent_name' => [
                'label'  => 'Fakultas/Direktorat',
                'rules'  => 'required|alpha_space|max_length[40]',
                'errors' => [
                    'required'    => '{field} wajib diisi.',
                    'alpha_space' => '{field} hanya boleh huruf dan spasi.',
                    'max_length'  => '{field} maksimal 40 karakter.',
                ],
            ],
            'unit_name' => [
                'label'  => 'Unit',
                'rules'  => 'required|alpha_space|max_length[40]',
                'errors' => [
                    'required'    => '{field} wajib diisi.',
                    'alpha_space' => '{field} hanya boleh huruf dan spasi.',
                    'max_length'  => '{field} maksimal 40 karakter.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('swal', [
                'icon'  => 'error',
                'title' => 'Oops...',
                'text'  => implode("\n", $this->validator->getErrors()),
            ]);
        }

        // cari / buat parent (jika diganti)
        $parentName = trim($this->request->getPost('parent_name'));
        $parent     = $this->parentModel->where('name', $parentName)->first();

        $parentId = $parent
            ? $parent['id']
            : $this->parentModel->insert([
                'type'   => 2,            // 2 = Fakultas, sesuaikan bila perlu
                'name'   => $parentName,
                'status' => 1,
            ]);

        // update unit
        $this->unitModel->update($id, [
            'parent_id' => $parentId,
            'name'      => $this->request->getPost('unit_name'),
        ]);

        return redirect()->to('data-master')->with('swal', [
            'icon'  => 'success',
            'title' => 'Berhasil!',
            'text'  => 'Unit diperbarui.',
        ]);
    }

    /* ───────── DELETE ───────── */
    public function delete($id)
    {
        $this->unitModel->delete($id);

        return redirect()->to('data-master')->with('swal', [
            'icon'  => 'success',
            'title' => 'Terhapus!',
            'text'  => 'Unit berhasil dihapus.',
        ]);
    }

    /* ───────── APPROVE ───────── */
    // public function approve($id)
    // {
    //     $this->unitModel->update($id, ['status' => 1]);

    //     return redirect()->to('data-master')->with('swal', [
    //         'icon'  => 'success',
    //         'title' => 'Diaktifkan!',
    //         'text'  => 'Unit sudah aktif.',
    //     ]);
    // }

    
    /* ───────── CSV ───────── */
    public function exportCsv()
    {
        $units = $this->unitModel->getWithParent();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->fromArray([['No','Fakultas/Direktorat','Unit']], null, 'A1');

        foreach ($units as $i => $u) {
            $sheet->fromArray([$i+1, $u['parent_name'], $u['name']], null, 'A'.($i+2));
        }

        $writer = new Csv($spreadsheet);
        $filename = 'unit_'.date('Ymd_His').'.csv';

        // Output CSV secara langsung
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    }


    /* ───────── Excel ───────── */
    public function exportExcel()
    {
        $units = $this->unitModel->getWithParent();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->fromArray([['No','Fakultas/Direktorat','Unit']], null, 'A1');

        foreach ($units as $i => $u) {
            $sheet->fromArray([$i+1, $u['parent_name'], $u['name']], null, 'A'.($i+2));
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'unit_'.date('Ymd_His').'.xlsx';

        ob_start();
        $writer->save('php://output');
        $excelData = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment;filename="'.$filename.'"')
            ->setBody($excelData);
    }

    /* ───────── PDF ───────── */
    public function exportPdf()
    {
        $units = $this->unitModel->getWithParent();

        $html = view('DataMaster/export-pdf', ['units' => $units]); // bikin view mini
        $dompdf = new Dompdf(['isHtml5ParserEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'unit_'.date('Ymd_His').'.pdf';
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment;filename="'.$filename.'"')
            ->setBody($dompdf->output());
    }

    /* ───────── PRINT ───────── */
    public function exportPrint()
    {
        $units = $this->unitModel->getWithParent();
        return view('DataMaster/export-print', ['units' => $units]); // HTML tanpa sidebar
    }
}
