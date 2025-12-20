<h2>Buat Purchase Order</h2>

<form method="POST" action="{{ url('/gudang/po') }}">
    @csrf

    <label>Supplier ID</label><br>
    <input type="number" name="supplier_id"><br><br>

    <h4>Produk</h4>

    @foreach($produk as $p)
        <input type="checkbox" name="produk[{{ $p->produk_id }}][produk_id]" value="{{ $p->produk_id }}">
        {{ $p->nama_produk }}
        Qty:
        <input type="number" name="produk[{{ $p->produk_id }}][qty]">
        Harga:
        <input type="number" name="produk[{{ $p->produk_id }}][harga]">
        <br>
    @endforeach

    <br>
    <button type="submit">Kirim PO</button>
</form>
