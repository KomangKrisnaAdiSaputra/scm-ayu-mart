<h2>Pengiriman Kurir</h2>

<table border="1">
@foreach($pengiriman as $p)
<tr>
    <td>Pengiriman #{{ $p->pengiriman_id }}</td>
    <td>{{ $p->status_pengiriman }}</td>
</tr>
@endforeach
</table>
