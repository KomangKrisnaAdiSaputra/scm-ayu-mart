<h2>Permintaan Restok Cabang</h2>

<table border="1">
@foreach($permintaan as $p)
<tr>
    <td>{{ $p->tanggal_permintaan }}</td>
    <td>{{ $p->status_permintaan }}</td>
</tr>
@endforeach
</table>
