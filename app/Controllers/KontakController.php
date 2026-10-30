<?php

namespace App\Controllers;

use App\Models\KategoriKontakModel;
use App\Models\KontakModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class KontakController extends BaseController
{
    protected KontakModel $model;
    protected KategoriKontakModel $kategoriModel;

        /** Pemetaan nama header (sudah dinormalisasi) ke nama kolom database. */
    private const PETA_HEADER = [
        'kategori'            => 'kategori',
        'nama'                => 'nama',
        'namakontak'          => 'nama',
        'namaperusahaan'      => 'nama_perusahaan',
        'perusahaan'          => 'nama_perusahaan',
        'nohp'                => 'no_hp',
        'nohandphone'         => 'no_hp',
        'telepon'             => 'no_hp',
        'telp'                => 'no_hp',
        'alamatemail'         => 'email',
        'email'               => 'email',
        'website'             => 'website',
        'kotakabupaten'       => 'kota_kabupaten',
        'kota'                => 'kota_kabupaten',
        'kabupaten'           => 'kota_kabupaten',
        'domisili'            => 'kota_kabupaten',
        'provinsi'            => 'provinsi',
        'produkperusahaan'    => 'produk_perusahaan',
        'produk'              => 'produk_perusahaan',
        'sumberdata'          => 'sumber_data',
        'catatan'             => 'catatan',
        'jumlahtidakterkirim' => 'jumlah_tidak_terkirim',
    ];

    /** Ubah nilai sel menjadi string rapi (angka besar tidak jadi notasi ilmiah). */
    private function teks($nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        if (is_int($nilai) || is_float($nilai)) {
            $nilai = ((float) $nilai === floor((float) $nilai))
                ? number_format((float) $nilai, 0, '', '')
                : (string) $nilai;
        }

        $nilai = trim((string) $nilai);

        return $nilai === '' ? null : $nilai;
    }

    /** Bersihkan nomor HP; tambahkan 0 di depan jika Excel membuangnya. */
    private function rapikanHp(?string $hp): ?string
    {
        if ($hp === null) {
            return null;
        }

        $hp = preg_replace('/[^0-9+]/', '', $hp);
        if ($hp === '' || $hp === null) {
            return null;
        }

        if ($hp[0] === '8') {
            $hp = '0' . $hp;
        }

        return mb_substr($hp, 0, 20);
    }

    public function __construct()
    {
        $this->model         = new KontakModel();
        $this->kategoriModel = new KategoriKontakModel();
    }

    private function aturan(?int $id = null): array
    {
        $unikEmail = $id
            ? "is_unique[kontak_email.email,id,{$id}]"
            : 'is_unique[kontak_email.email]';

        return [
            'kategori_id' => [
                'label'  => 'Kategori',
                'rules'  => 'permit_empty|is_natural_no_zero|is_not_unique[kategori.id]',
                'errors' => ['is_not_unique' => 'Kategori tidak valid.'],
            ],
            'nama' => [
                'label' => 'Nama',
                'rules' => 'permit_empty|max_length[150]',
            ],
            'nama_perusahaan' => [
                'label'  => 'Nama perusahaan',
                'rules'  => 'required|max_length[150]',
                'errors' => ['required' => 'Nama perusahaan wajib diisi.'],
            ],
            'no_hp' => [
                'label' => 'No HP',
                'rules' => 'permit_empty|max_length[20]',
            ],
            'email' => [
                'label'  => 'Email',
                'rules'  => "required|valid_email|max_length[254]|{$unikEmail}",
                'errors' => [
                    'required'    => 'Email wajib diisi.',
                    'valid_email' => 'Format email tidak valid.',
                    'is_unique'   => 'Email sudah terdaftar.',
                ],
            ],
            'website'        => ['label' => 'Website', 'rules' => 'permit_empty|max_length[255]'],
            'kota_kabupaten' => ['label' => 'Kota/Kabupaten', 'rules' => 'permit_empty|max_length[100]'],
            'provinsi'       => ['label' => 'Provinsi', 'rules' => 'permit_empty|max_length[100]'],
            'sumber_data'    => ['label' => 'Sumber data', 'rules' => 'permit_empty|max_length[150]'],
            'jumlah_tidak_terkirim' => [
                'label' => 'Jumlah tidak terkirim',
                'rules' => 'permit_empty|is_natural',
            ],
        ];
    }

    /** Ambil data dari form; string kosong diubah menjadi NULL. */
    private function dataForm(): array
    {
        $kosongJadiNull = static function ($nilai) {
            $nilai = trim((string) $nilai);
            return $nilai === '' ? null : $nilai;
        };

        $post = $this->request->getPost();

        return [
            'kategori_id'           => $kosongJadiNull($post['kategori_id'] ?? null),
            'nama'                  => $kosongJadiNull($post['nama'] ?? null),
            'nama_perusahaan'       => trim((string) ($post['nama_perusahaan'] ?? '')),
            'no_hp'                 => $kosongJadiNull($post['no_hp'] ?? null),
            'email'                 => strtolower(trim((string) ($post['email'] ?? ''))),
            'website'               => $kosongJadiNull($post['website'] ?? null),
            'kota_kabupaten'        => $kosongJadiNull($post['kota_kabupaten'] ?? null),
            'provinsi'              => $kosongJadiNull($post['provinsi'] ?? null),
            'produk_perusahaan'     => $kosongJadiNull($post['produk_perusahaan'] ?? null),
            'sumber_data'           => $kosongJadiNull($post['sumber_data'] ?? null),
            'catatan'               => $kosongJadiNull($post['catatan'] ?? null),
            'jumlah_tidak_terkirim' => (int) ($post['jumlah_tidak_terkirim'] ?? 0),
        ];
    }

    // READ: daftar + pencarian + filter kategori + pagination
    public function index()
    {
        $cari     = trim((string) $this->request->getGet('cari'));
        $kategori = trim((string) $this->request->getGet('kategori'));

        $this->model
            ->select('kontak_email.*, kategori.nama AS kategori_nama')
            ->join('kategori', 'kategori.id = kontak_email.kategori_id', 'left')
            ->orderBy('kontak_email.id', 'DESC');

        if ($cari !== '') {
            $this->model->groupStart()
                ->like('kontak_email.nama', $cari)
                ->orLike('kontak_email.nama_perusahaan', $cari)
                ->orLike('kontak_email.email', $cari)
                ->orLike('kontak_email.no_hp', $cari)
                ->orLike('kontak_email.kota_kabupaten', $cari)
                ->orLike('kontak_email.provinsi', $cari)
                ->groupEnd();
        }

        if ($kategori !== '') {
            $this->model->where('kontak_email.kategori_id', (int) $kategori);
        }

        return view('kontak_email/index', [
            'judul'           => 'Kontak',
            'kontak'          => $this->model->paginate(10),
            'pager'           => $this->model->pager,
            'cari'            => $cari,
            'kategoriDipilih' => $kategori,
            'daftarKategori'  => $this->kategoriModel->orderBy('nama', 'ASC')->findAll(),
        ]);
    }

    // CREATE
    public function simpan()
    {
        if (! $this->validate($this->aturan())) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('modal_aksi', site_url('kontak/simpan'));
        }

        $this->model->insert($this->dataForm());

        return redirect()->to('/kontak')->with('sukses', 'Kontak berhasil ditambahkan.');
    }

    // UPDATE
    public function perbarui(int $id)
    {
        if (! $this->model->find($id)) {
            return redirect()->to('/kontak')->with('gagal', 'Kontak tidak ditemukan.');
        }

        if (! $this->validate($this->aturan($id))) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('modal_aksi', site_url('kontak/perbarui/' . $id));
        }

        $this->model->update($id, $this->dataForm());

        return redirect()->to('/kontak')->with('sukses', 'Kontak berhasil diperbarui.');
    }

    // DELETE
    public function hapus(int $id)
    {
        if (! $this->model->find($id)) {
            return redirect()->to('/kontak')->with('gagal', 'Kontak tidak ditemukan.');
        }

        $this->model->delete($id);

        return redirect()->to('/kontak')->with('sukses', 'Kontak berhasil dihapus.');
    }
        public function import()
    {
        $file = $this->request->getFile('file_excel');

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to('/kontak')->with('gagal', 'File gagal diunggah. Cek ukuran file dan coba lagi.');
        }

        if (! in_array(strtolower($file->getClientExtension()), ['xlsx', 'xls', 'csv'], true)) {
            return redirect()->to('/kontak')->with('gagal', 'Format file harus .xlsx, .xls, atau .csv.');
        }

        $perbarui = (bool) $this->request->getPost('perbarui');

        set_time_limit(300);
        ini_set('memory_limit', '512M');

        // 1. Baca file
        try {
            $baris = IOFactory::load($file->getTempName())
                ->getActiveSheet()
                ->toArray(null, true, false, false);
        } catch (\Throwable $e) {
            log_message('error', 'Import gagal membaca file: ' . $e->getMessage());

            return redirect()->to('/kontak')->with('gagal', 'File tidak bisa dibaca. Pastikan formatnya benar.');
        }

        if (count($baris) < 2) {
            return redirect()->to('/kontak')->with('gagal', 'File kosong atau hanya berisi header.');
        }

        // 2. Petakan header -> indeks kolom
        $header = array_shift($baris);
        $indeks = [];
        foreach ($header as $i => $judul) {
            $kunci = preg_replace('/[^a-z0-9]/', '', strtolower(trim((string) $judul)));
            if (isset(self::PETA_HEADER[$kunci])) {
                $indeks[self::PETA_HEADER[$kunci]] = $i;
            }
        }

        if (! isset($indeks['nama_perusahaan']) && ! isset($indeks['nama'])) {
            return redirect()->to('/kontak')
                ->with('gagal', 'Kolom "NAMA PERUSAHAAN" atau "NAMA" tidak ditemukan di baris pertama file.');
        }

        // 3. Data pembanding (email dan kombinasi perusahaan + nama)
        $emailAda  = [];
        $usahaAda  = [];
        $kunciUsaha = static fn (?string $usaha, ?string $nama) =>
            mb_strtolower(trim((string) $usaha) . '|' . trim((string) $nama));

        foreach ($this->model->select('id, email, nama, nama_perusahaan')->findAll() as $r) {
            if (! empty($r['email'])) {
                $emailAda[strtolower((string) $r['email'])] = (int) $r['id'];
            }
            $usahaAda[$kunciUsaha($r['nama_perusahaan'], $r['nama'])] = (int) $r['id'];
        }

        $kategoriAda = [];
        foreach ($this->kategoriModel->findAll() as $r) {
            $kategoriAda[mb_strtolower($r['nama'])] = (int) $r['id'];
        }

        $ambil = static function (array $row, array $indeks, string $kolom) {
            return isset($indeks[$kolom]) ? ($row[$indeks[$kolom]] ?? null) : null;
        };

        $db        = db_connect();
        $antrean   = [];
        $jmlTambah = 0;
        $jmlUbah   = 0;
        $jmlLewati = 0;
        $galat     = [];

        $db->transStart();

        foreach ($baris as $n => $row) {
            $noBaris = $n + 2;

            // Lewati baris kosong
            if (! array_filter($row, static fn ($v) => $v !== null && trim((string) $v) !== '')) {
                continue;
            }

            $nama  = $this->teks($ambil($row, $indeks, 'nama'));
            $usaha = $this->teks($ambil($row, $indeks, 'nama_perusahaan')) ?? $nama;
            $email = strtolower((string) $this->teks($ambil($row, $indeks, 'email')));

            if ($usaha === null) {
                $jmlLewati++;
                $galat[] = "Baris {$noBaris}: nama perusahaan dan nama kosong.";
                continue;
            }

            // Email opsional, tapi kalau diisi harus valid
            if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $jmlLewati++;
                $galat[] = "Baris {$noBaris}: format email tidak valid ({$email}).";
                continue;
            }
            $email = $email === '' ? null : mb_substr($email, 0, 254);

            // Kategori: cari berdasarkan nama, buat jika belum ada
            $kategoriId   = null;
            $namaKategori = $this->teks($ambil($row, $indeks, 'kategori'));
            if ($namaKategori !== null) {
                $namaKategori = mb_substr($namaKategori, 0, 100);
                $kunciKat     = mb_strtolower($namaKategori);

                if (! isset($kategoriAda[$kunciKat])) {
                    $this->kategoriModel->insert(['nama' => $namaKategori]);
                    $kategoriAda[$kunciKat] = (int) $this->kategoriModel->getInsertID();
                }
                $kategoriId = $kategoriAda[$kunciKat];
            }

            $data = [
                'kategori_id'           => $kategoriId,
                'nama'                  => $nama !== null ? mb_substr($nama, 0, 150) : null,
                'nama_perusahaan'       => mb_substr($usaha, 0, 150),
                'no_hp'                 => $this->rapikanHp($this->teks($ambil($row, $indeks, 'no_hp'))),
                'email'                 => $email,
                'website'               => mb_substr((string) $this->teks($ambil($row, $indeks, 'website')), 0, 255) ?: null,
                'kota_kabupaten'        => mb_substr((string) $this->teks($ambil($row, $indeks, 'kota_kabupaten')), 0, 100) ?: null,
                'provinsi'              => mb_substr((string) $this->teks($ambil($row, $indeks, 'provinsi')), 0, 100) ?: null,
                'produk_perusahaan'     => $this->teks($ambil($row, $indeks, 'produk_perusahaan')),
                'sumber_data'           => mb_substr((string) $this->teks($ambil($row, $indeks, 'sumber_data')), 0, 150) ?: null,
                'catatan'               => $this->teks($ambil($row, $indeks, 'catatan')),
                'jumlah_tidak_terkirim' => max(0, (int) $this->teks($ambil($row, $indeks, 'jumlah_tidak_terkirim'))),
            ];

            // Cari data yang sudah ada: lewat email jika ada, kalau tidak lewat perusahaan + nama
            $kunci = $kunciUsaha($data['nama_perusahaan'], $data['nama']);
            $idAda = $email !== null ? ($emailAda[$email] ?? null) : ($usahaAda[$kunci] ?? null);

            if ($idAda !== null) {
                if ($perbarui && $idAda > 0) {
                    if ($email === null) {
                        unset($data['email']); // jangan menimpa email lama dengan kosong
                    }
                    $this->model->update($idAda, $data);
                    $jmlUbah++;
                } else {
                    $jmlLewati++;
                    $galat[] = "Baris {$noBaris}: data sudah ada (" . ($email ?? $data['nama_perusahaan']) . ').';
                }
                continue;
            }

            // Tandai sudah dipakai di file ini (0 = belum punya ID, tidak bisa diperbarui)
            if ($email !== null) {
                $emailAda[$email] = 0;
            }
            $usahaAda[$kunci] = 0;

            $antrean[] = $data;
            $jmlTambah++;

            if (count($antrean) >= 200) {
                $this->model->insertBatch($antrean);
                $antrean = [];
            }
        }

        if ($antrean !== []) {
            $this->model->insertBatch($antrean);
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('/kontak')
                ->with('gagal', 'Import dibatalkan karena terjadi kesalahan database. Tidak ada data yang tersimpan.');
        }

        return redirect()->to('/kontak')
            ->with('sukses', "Import selesai: {$jmlTambah} ditambahkan, {$jmlUbah} diperbarui, {$jmlLewati} dilewati.")
            ->with('detail_import', array_slice($galat, 0, 30));
    }
    public function template()
{
    $spreadsheet = new Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Kontak');

    $header = [
        'NAMA', 'NAMA PERUSAHAAN', 'KATEGORI', 'KOTA KABUPATEN', 'PROVINSI',
        'PRODUK PERUSAHAAN', 'SUMBER DATA',
    ];
    $sheet->fromArray($header, null, 'A1');
    $sheet->fromArray([
        'Budi Santoso', 'PT Contoh Makmur', 'Distributor', 'Bandung', 'Jawa Barat',
        'Alat tulis kantor', 'Google Maps',
    ], null, 'A2');

    $sheet->getStyle('A1:G1')->getFont()->setBold(true);
    foreach (range('A', 'G') as $kol) {
        $sheet->getColumnDimension($kol)->setAutoSize(true);
    }

    ob_start();
    (new Xlsx($spreadsheet))->save('php://output');
    $isi = ob_get_clean();

    return $this->response->download('template-import-kontak.xlsx', $isi);
}
}

