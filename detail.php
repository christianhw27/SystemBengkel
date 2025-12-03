<?php
// File: detail.php
include 'koneksi.php';

// 1. Cek apakah ada ID yang dikirim
if (!isset($_GET['id'])) {
    header("Location: riwayat.php");
    exit;
}

$id_konsultasi = $_GET['id'];

// 2. Ambil Data Header (Nama, Motor, Tanggal)
$query_header = "SELECT * FROM log_konsultasi WHERE id = '$id_konsultasi'";
$result_header = mysqli_query($conn, $query_header);
$data_konsultasi = mysqli_fetch_assoc($result_header);

if (!$data_konsultasi) {
    echo "Data tidak ditemukan.";
    exit;
}

// 3. Ambil Detail Gejala yang dipilih user saat itu
$query_gejala = "SELECT kode_gejala FROM detail_gejala WHERE id_konsultasi = '$id_konsultasi'";
$result_gejala = mysqli_query($conn, $query_gejala);

$gejala_user = [];
while ($row = mysqli_fetch_assoc($result_gejala)) {
    $gejala_user[] = $row['kode_gejala'];
}

// 4. Hitung Ulang Diagnosa (Re-run Logic) 
// Kita pakai logika yang sama persis dengan proses.php untuk mendapatkan hasil full
$hasil_diagnosa = [];

foreach ($rules as $rule) {
    $cocok = count(array_intersect($rule['conditions'], $gejala_user)) == count($rule['conditions']);
    if ($cocok) {
        $hasil_diagnosa[] = $rule;
    }
}

// Fallback jika tidak ada yang cocok (seharusnya jarang terjadi jika data valid)
if (empty($hasil_diagnosa)) {
    $hasil_diagnosa[] = [
        'diagnosa' => 'Gejala Belum Teridentifikasi Spesifik',
        'solusi' => 'Disarankan membawa ke bengkel resmi.',
        'conditions' => $gejala_user
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Riwayat Diagnosa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Detail Diagnosa</h1>

    <div class="card">
        <h2>Info Kendaraan</h2>
        <table style="width:100%; margin-bottom: 20px;">
            <tr>
                <td style="width:150px; font-weight:bold;">Nama Pemilik</td>
                <td>: <?php echo htmlspecialchars($data_konsultasi['nama_user']); ?></td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Tipe Motor</td>
                <td>: <?php echo htmlspecialchars($data_konsultasi['tipe_motor']); ?></td>
            </tr>
            <tr>
                <td style="font-weight:bold;">Tanggal Cek</td>
                <td>: <?php echo $data_konsultasi['tanggal']; ?></td>
            </tr>
        </table>

        <hr>

        <h3>Gejala yang dilaporkan:</h3>
        <ul>
            <?php 
            foreach ($gejala_user as $g) {
                // Ambil nama gejala dari array $daftar_gejala di koneksi.php
                $nama_gejala = isset($daftar_gejala[$g]) ? $daftar_gejala[$g] : $g;
                echo "<li><b>$g</b> - $nama_gejala</li>";
            }
            ?>
        </ul>

        <hr>

        <h2>Hasil Analisa</h2>
        <?php foreach ($hasil_diagnosa as $index => $hasil): ?>
            <div style="background: #f9f9f9; padding: 15px; border-left: 5px solid #28a745; margin-bottom: 20px;">
                <h3><?php echo ($index + 1) . ". " . $hasil['diagnosa']; ?></h3>
                <p><b>Solusi:</b> <?php echo $hasil['solusi']; ?></p>
                <p><small><i>Faktor penyebab: <?php echo implode(", ", $hasil['conditions']); ?></i></small></p>
            </div>
        <?php endforeach; ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="riwayat.php"><button class="btn btn-reset">Kembali ke Riwayat</button></a>
            <button onclick="window.print()" class="btn btn-primary">Cetak / PDF</button>
        </div>
    </div>
</body>
</html>