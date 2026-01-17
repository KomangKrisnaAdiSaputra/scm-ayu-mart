<head>
    <style>
        body {
            font-size: 11px;
            font-family: sans-serif;
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

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>

    <h3 align="center">Laporan Stok Gudang</h3>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Stok Total</th>
                <th>Stok Minimum</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stok as $i => $item)
                @php
                    $p = $produk[$item->produk_id] ?? null;
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $p->kode_produk ?? '-' }}</td>
                    <td>{{ $p->nama_produk ?? '-' }}</td>
                    <td class="text-center">{{ $item->stok_total }}</td>
                    <td class="text-center">{{ $item->stok_minimum }}</td>
                    <td class="text-center">
                        {{ $item->stok_total <= $item->stok_minimum ? 'Menipis' : 'Aman' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">
                        Data tidak ditemukan
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
