<?php
// File: proses.php
include 'koneksi.php';

// 1. Tangkap Input User
$nama = $_POST['nama'];
$motor = $_POST['motor'];
$gejala_user = isset($_POST['gejala']) ? $_POST['gejala'] : [];

if (empty($gejala_user)) {
    echo "<script>alert('Pilih setidaknya satu gejala!'); window.history.back();</script>";
    exit;
}

// 2. Logika Diagnosa (Expert System Engine)
$hasil_diagnosa = [];

// Cek Rule Kombinasi
foreach ($rules as $rule) {
    // Cek apakah semua kondisi di rule ada di input user
    // array_intersect membandingkan input user dg syarat rule
    $cocok = count(array_intersect($rule['conditions'], $gejala_user)) == count($rule['conditions']);
    
    if ($cocok) {
        $hasil_diagnosa[] = $rule;
    }
}

// Jika tidak ada rule kombinasi yg cocok, cari rule tunggal (sederhana)
if (empty($hasil_diagnosa)) {
    $hasil_diagnosa[] = [
        'diagnosa' => 'Gejala Belum Teridentifikasi Spesifik',
        'solusi' => 'Disarankan membawa ke bengkel resmi untuk pengecekan manual.',
        'conditions' => $gejala_user
    ];
}

// Ambil diagnosa pertama sebagai diagnosa utama untuk disimpan di DB
$diagnosa_db = $hasil_diagnosa[0]['diagnosa'];
$solusi_db   = $hasil_diagnosa[0]['solusi'];


// 3. Simpan ke Database
// A. Simpan Header (Log Konsultasi)
$query_header = "INSERT INTO log_konsultasi (nama_user, tipe_motor, diagnosa_utama, solusi_utama) 
                 VALUES ('$nama', '$motor', '$diagnosa_db', '$solusi_db')";
mysqli_query($conn, $query_header);
$id_konsultasi = mysqli_insert_id($conn); // Ambil ID yg baru dibuat

// B. Simpan Detail Gejala (Looping)
foreach ($gejala_user as $g) {
    mysqli_query($conn, "INSERT INTO detail_gejala (id_konsultasi, kode_gejala) VALUES ('$id_konsultasi', '$g')");
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Diagnosa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Hasil Diagnosa</h1>
    <div class="card">
        <h2>Data Motor</h2>
        <p><b>Pemilik:</b> <?php echo htmlspecialchars($nama); ?></p>
        <p><b>Motor:</b> <?php echo htmlspecialchars($motor); ?></p>

        <hr>

        <h2>Hasil Analisa Sistem</h2>
        <?php foreach ($hasil_diagnosa as $index => $hasil): ?>
            <div style="background: #f9f9f9; padding: 15px; border-left: 5px solid #007bff; margin-bottom: 20px;">
                <h3><?php echo ($index + 1) . ". " . $hasil['diagnosa']; ?></h3>
                <p><b>Solusi:</b> <?php echo $hasil['solusi']; ?></p>
                <p><small><i>Berdasarkan gejala: <?php echo implode(", ", $hasil['conditions']); ?></i></small></p>
            </div>
        <?php endforeach; ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php"><button class="btn btn-primary">Diagnosa Lagi</button></a>
            <a href="riwayat.php"><button class="btn btn-reset">Lihat Riwayat</button></a>
        </div>
    </div>
</body>
</html>