<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expert System Motor</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <h1>Expert System Kerusakan Sepeda Motor</h1>
    <p style="text-align:center;">
        Silakan isi data diri dan pilih gejala yang dialami motor Anda.
    </p>

    <div class="card">
        <form action="proses.php" method="post">
            
            <h2>Data Pengguna</h2>
            <div style="margin-bottom: 20px;">
                <label>Nama Pemilik:</label>
                <input type="text" name="nama" required style="width: 100%; padding: 8px; margin-top:5px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label>Tipe Motor:</label>
                <input type="text" name="motor" placeholder="Contoh: Vario 150, Mio Sporty" required style="width: 100%; padding: 8px; margin-top:5px;">
            </div>

            <hr>

            <h2>Pilih Gejala</h2>
            <div class="gejala-grid">
                <?php 
                include 'koneksi.php';
                foreach ($daftar_gejala as $kode => $gejala) {
                    echo "<label><input type='checkbox' name='gejala[]' value='$kode'> <b>$kode</b>: $gejala</label>";
                }
                ?>
            </div>
            
            <br>
            <button type="submit" class="btn btn-primary">Konsultasi</button>
            <a href="riwayat.php" class="btn btn-reset" style="text-decoration:none;">Lihat Riwayat</a>
        </form>
    </div>
</body>
</html>