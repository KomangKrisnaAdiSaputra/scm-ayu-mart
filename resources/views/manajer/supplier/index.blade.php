<h2>Data Supplier</h2>

<table border="1">
@foreach($supplier as $s)
<tr>
    <td>{{ $s->nama_supplier }}</td>
    <td>{{ $s->kontak }}</td>
</tr>
@endforeach
</table>
