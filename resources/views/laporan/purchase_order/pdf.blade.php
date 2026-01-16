<head>
    <style>
        body {
            font-family: DejaVu Sans;
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
            background: #eee;
        }
    </style>
</head>

<body>

    <h3 align="center">LAPORAN PURCHASE ORDER</h3>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Kode PO</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchaseOrders as $po)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $po->kode_po }}</td>
                    <td>{{ $po->tanggal_po }}</td>
                    <td>{{ $po->supplier->nama_supplier ?? '-' }}</td>
                    <td align="right">{{ number_format($po->total_po) }}</td>
                    <td>{{ $po->status_po }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
