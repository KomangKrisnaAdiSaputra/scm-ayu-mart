<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        h2,
        h3 {
            margin: 10px 0 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 6px;
        }

        th {
            background: #f2f2f2;
            text-align: center;
        }

        td {
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .section {
            page-break-inside: avoid;
        }

        .footer-total {
            font-weight: bold;
            background: #eee;
        }
    </style>
</head>

<body>

    <h2 class="text-center">LAPORAN PURCHASE ORDER</h2>
    <p class="text-center">
        Periode:
        {{ request('from') ?? '-' }}
        s/d
        {{ request('to') ?? '-' }}
    </p>

    {{-- ================= LAPORAN PO ================= --}}
    <div class="section">
        <h3>1. Laporan Purchase Order</h3>

        <table>
            <thead>
                <tr>
                    <th width="4%">#</th>
                    <th width="15%">Kode PO</th>
                    <th width="12%">Tanggal</th>
                    <th>Supplier</th>
                    <th width="15%">Status</th>
                    <th width="15%">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($laporanPO as $i => $po)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $po->kode_po }}</td>
                        <td>{{ $po->tanggal_po }}</td>
                        <td>{{ $po->supplier->nama_supplier ?? '-' }}</td>
                        <td class="text-center">{{ $po->status_po }}</td>
                        <td class="text-right">
                            Rp {{ number_format($po->total_po, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Data tidak tersedia</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="footer-total">
                    <td colspan="5">Total Pengeluaran</td>
                    <td class="text-right">
                        Rp {{ number_format($totalNilaiPO, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- ================= PO SUPPLIER ================= --}}
    <div class="section">
        <h3>2. Purchase Order per Supplier</h3>

        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>Nama Supplier</th>
                    <th width="20%">Jumlah PO</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($laporanPOSupplier as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $row->nama_supplier }}</td>
                        <td class="text-right">{{ $row->total_po }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ================= RETUR ================= --}}
    <div class="section">
        <h3>3. Retur per Supplier</h3>

        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>Supplier</th>
                    <th width="25%">Jumlah Retur</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($laporanReturSupplier as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $row->nama_supplier }}</td>
                        <td class="text-right">{{ $row->total_retur }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ================= PENGIRIMAN ================= --}}
    <div class="section">
        <h3>4. Status Pengiriman</h3>

        <table>
            <thead>
                <tr>
                    <th>Status Pengiriman</th>
                    <th width="25%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($laporanPengiriman as $row)
                    <tr>
                        <td>{{ $row->status_pengiriman }}</td>
                        <td class="text-right">{{ $row->total }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="footer-total">
                    <td>Total</td>
                    <td class="text-right">
                        {{ $laporanPengiriman->sum('total') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- ================= PRODUK ================= --}}
    <div class="section">
        <h3>5. Produk</h3>

        <table>
            <thead>
                <tr>
                    <th>Jenis Produk</th>
                    <th width="25%">Total Produk</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($laporanProduk as $row)
                    <tr>
                        <td>{{ $row->jenis->nama_jenis ?? '-' }}</td>
                        <td class="text-right">{{ $row->total_produk }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ================= PERMINTAAN CABANG ================= --}}
    <div class="section">
        <h3>6. Permintaan Cabang</h3>

        <table>
            <thead>
                <tr>
                    <th>Cabang</th>
                    <th width="25%">Jumlah Permintaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($laporanPermintaanCabang as $row)
                    <tr>
                        <td>{{ $row->cabang_id }}</td>
                        <td class="text-right">{{ $row->total_permintaan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
