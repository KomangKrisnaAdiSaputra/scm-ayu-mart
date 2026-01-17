<head>
    <meta charset="UTF-8">
    <title>Laporan Pengiriman</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        h2 {
            text-align: center;
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
            background: #f2f2f2;
        }

        .center {
            text-align: center;
        }
    </style>
</head>

<body>

    <h2>LAPORAN PENGIRIMAN</h2>
    <p style="text-align:center">
        Dicetak: {{ now()->format('d M Y H:i') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Kode Permintaan</th>
                <th>Cabang</th>
                <th>Tanggal Kirim</th>
                <th>Status</th>
                <th>Kurir</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pengiriman as $item)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $item->permintaan->kode_permintaan ?? '-' }}</td>
                    <td>{{ $item->permintaan->cabang->nama_cabang ?? '-' }}</td>
                    <td class="center">{{ $item->tanggal_kirim }}</td>
                    <td class="center">{{ $item->status_pengiriman }}</td>
                    <td>{{ $item->status_kurir->nama_kurir ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">
                        <strong>Data tidak ditemukan</strong>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
