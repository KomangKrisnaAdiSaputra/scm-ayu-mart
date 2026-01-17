<head>
    <meta charset="utf-8">
    <title>Laporan Stok Cabang</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
        }

        th {
            background: #f0f0f0;
        }

        h3 {
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <h3>LAPORAN STOK CABANG</h3>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Cabang</th>
                <th>Stok</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stokCabang as $i => $row)
                @php
                    $p = $produk[$row->id_produk] ?? null;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p->kode_produk ?? '-' }}</td>
                    <td>{{ $p->nama_produk ?? '-' }}</td>
                    <td>{{ $row->cabang->nama_cabang ?? '-' }}</td>
                    <td>{{ $row->total_stok }} / {{ $row->stok_minimum }}</td>
                    <td>{{ $row->total_stok <= $row->stok_minimum ? 'Menipis' : 'Aman' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">
                        Data tidak ditemukan
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
