<h2>Purchase Order Masuk</h2>

<table border="1">
@foreach($po as $p)
<tr>
    <td>PO #{{ $p->po_id }}</td>
    <td>{{ $p->status_po }}</td>
</tr>
@endforeach
</table>
