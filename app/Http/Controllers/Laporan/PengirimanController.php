<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Pengiriman;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PengirimanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengiriman::with([
            'permintaan.cabang',
            'status_kurir'
        ]);

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('permintaan', function ($q) use ($search) {
                $q->where('kode_permintaan', 'like', "%$search%")
                    ->orWhereHas(
                        'cabang',
                        fn($c) =>
                        $c->where('nama_cabang', 'like', "%$search%")
                    );
            });
        }

        // 📅 FILTER TANGGAL
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_kirim', '>=', $request->tanggal_awal);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_kirim', '<=', $request->tanggal_akhir);
        }

        // 🚚 STATUS
        if ($request->filled('status_pengiriman')) {
            $query->where('status_pengiriman', $request->status_pengiriman);
        }

        $pengiriman = $query->latest()->paginate(10)->withQueryString();

        return view('laporan.pengiriman.index', compact('pengiriman'));
    }

    public function exportPdf(Request $request)
    {
        $query = Pengiriman::with([
            'permintaan.cabang',
            'status_kurir'
        ]);

        // FILTER SAMA DENGAN INDEX
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('permintaan', function ($q) use ($search) {
                $q->where('kode_permintaan', 'like', "%$search%")
                    ->orWhereHas(
                        'cabang',
                        fn($c) =>
                        $c->where('nama_cabang', 'like', "%$search%")
                    );
            });
        }

        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_kirim', '>=', $request->tanggal_awal);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_kirim', '<=', $request->tanggal_akhir);
        }

        if ($request->filled('status_pengiriman')) {
            $query->where('status_pengiriman', $request->status_pengiriman);
        }

        $pengiriman = $query->get();

        $pdf = Pdf::loadView('laporan.pengiriman.pdf', compact('pengiriman'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-pengiriman.pdf');
    }
}
