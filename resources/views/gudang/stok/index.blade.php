<h2>Stok Gudang</h2>

<table border="1">
    <tr>
        <th>Produk</th>
        <th>Stok</th>
        <th>Minimum</th>
    </tr>

    @foreach($stok as $s)
    <tr>
        <td>{{ $s->produk->nama_produk }}</td>
        <td>{{ $s->stok_total }}</td>
        <td>{{ $s->stok_minimum }}</td>
    </tr>
    @endforeach
</table>
