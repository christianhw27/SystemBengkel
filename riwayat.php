<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Konsultasi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        tr:hover { background-color: #e2e6ea; }
        .btn-detail {
            background-color: #28a745; 
            color: white; 
            padding: 5px 10px; 
            text-decoration: none; 
            border-radius: 4px; 
            font-size: 14px;
        }
        .btn-detail:hover { background-color: #218838; }
    </style>
</head>
<body>
    <h1>Riwayat Konsultasi</h1>
    <div class="card" style="max-width: 1000px;">
        <p><a href="index.php" class="btn btn-reset" style="text-decoration:none;">&laquo; Kembali ke Home</a></p>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama</th>
                    <th>Motor</th>
                    <th>Diagnosa Utama</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'koneksi.php';
                $query = "SELECT * FROM log_konsultasi ORDER BY tanggal DESC";
                $result = mysqli_query($conn, $query);
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $no++ . "</td>";
                    echo "<td>" . $row['tanggal'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['nama_user']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['tipe_motor']) . "</td>";
                    echo "<td>" . $row['diagnosa_utama'] . "</td>";
                    // Tombol Link ke detail.php membawa ID
                    echo "<td style='text-align: center;'>
                            <a href='detail.php?id=" . $row['id'] . "' class='btn-detail'>Lihat Detail</a>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>