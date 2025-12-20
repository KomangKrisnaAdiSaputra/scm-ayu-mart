<h2>Data Produk</h2>

<form method="POST">
    @csrf
    <input name="kode_produk" placeholder="Kode Produk">
    <input name="nama_produk" placeholder="Nama Produk">
    <input name="kategori" placeholder="Kategori">
    <input name="satuan" placeholder="Satuan">
    <input name="harga_beli" placeholder="Harga Beli">
    <input name="harga_jual" placeholder="Harga Jual">
    <select name="status_produk">
        <option value="aktif">Aktif</option>
        <option value="nonaktif">Nonaktif</option>
    </select>
    <button>Simpan</button>
</form>

<table border="1">
@foreach($produk as $p)
<tr>
    <td>{{ $p->nama_produk }}</td>
    <td>{{ $p->kategori }}</td>
</tr>
@endforeach
</table>
