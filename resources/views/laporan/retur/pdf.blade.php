<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            font-size: 11px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 5px;
        }

        table th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            position: fixed;
            bottom: -20px;
            width: 100%;
            text-align: center;
            font-size: 9px;
        }
    </style>
</head>

<body>

    <div class="title">LAPORAN RETUR</div>
    <div class="subtitle">
        Dicetak pada {{ now()->format('d M Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Kode PO</th>
                <th>Supplier</th>
                <th>Tanggal Retur</th>
                <th>Produk</th>
                <th>Qty</th>
                <th>Status</th>
                <th>Alasan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($returs as $retur)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $retur->purchaseOrder->kode_po ?? '-' }}</td>
                    <td>{{ $retur->purchaseOrder->supplier->nama_supplier ?? '-' }}</td>
                    <td class="text-center">{{ $retur->tanggal_retur }}</td>
                    <td>{{ $retur->produk->nama_produk ?? '-' }}</td>
                    <td class="text-center">{{ $retur->qty_retur }}</td>
                    <td class="text-center">{{ $retur->status_retur }}</td>
                    <td>{{ $retur->alasan }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">
                        Tidak ada data retur
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Sistem Informasi Purchase Order & Retur
    </div>

</body>
