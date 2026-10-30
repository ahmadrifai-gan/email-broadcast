<?php

namespace App\Controllers;

use App\Models\KampanyeEmailModel;
use App\Models\RiwayatPengirimanEmailModel;

class RiwayatPengirimanController extends BaseController
{
    protected RiwayatPengirimanEmailModel $riwayatModel;
    protected KampanyeEmailModel $kampanyeModel;

    public function __construct()
    {
        $this->riwayatModel  = new RiwayatPengirimanEmailModel();
        $this->kampanyeModel = new KampanyeEmailModel();
    }

    public function index()
    {
        $jenis      = $this->request->getGet('jenis');
        $kampanyeId = (int) $this->request->getGet('kampanye');

        $builder = $this->riwayatModel
            ->select('riwayat_pengiriman_email.*, p.alamat_email_penerima, p.nama_penerima, p.kampanye_email_id, k.nama_kampanye')
            ->join('penerima_kampanye p', 'p.id = riwayat_pengiriman_email.penerima_kampanye_id')
            ->join('kampanye_email k', 'k.id = p.kampanye_email_id');

        if (in_array($jenis, ['terkirim', 'gagal'], true)) {
            $builder->where('riwayat_pengiriman_email.jenis_aktivitas', $jenis);
        }
        if ($kampanyeId > 0) {
            $builder->where('p.kampanye_email_id', $kampanyeId);
        }

        return view('riwayat_pengiriman/index', [
            'riwayat'  => $builder->orderBy('riwayat_pengiriman_email.terjadi_pada', 'DESC')->paginate(20),
            'pager'    => $this->riwayatModel->pager,
            'kampanye' => $this->kampanyeModel->orderBy('id', 'DESC')->findAll(),
            'jenis'    => $jenis,
            'filterId' => $kampanyeId,
        ]);
    }
}