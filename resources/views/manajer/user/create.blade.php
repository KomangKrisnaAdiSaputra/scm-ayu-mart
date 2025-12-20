<h2>Tambah User (Supplier / Cabang / Kurir)</h2>

<form method="POST" action="{{ url('/manajer/user') }}">
    @csrf

    <label>Username</label><br>
    <input name="username"><br><br>

    <label>Password</label><br>
    <input type="password" name="password"><br><br>

    <label>Nama</label><br>
    <input name="nama"><br><br>

    <label>Email</label><br>
    <input name="email"><br><br>

    <label>Role</label><br>
    <select name="role">
        <option value="Supplier">Supplier</option>
        <option value="Cabang">Cabang</option>
        <option value="Kurir">Kurir</option>
    </select><br><br>

    <label>Alamat (Supplier / Cabang)</label><br>
    <input name="alamat"><br><br>

    <label>Kontak (Supplier / Cabang)</label><br>
    <input name="kontak"><br><br>

    <button type="submit">Simpan</button>
</form>
