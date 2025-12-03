<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ai-es-bengkel";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// --- 1. DATA LIST GEJALA (AMBIL DARI DATABASE - TABEL master_gejala) ---
// Ini memenuhi syarat tabel ke-3 (Data Dinamis)
$daftar_gejala = [];
$q_gejala = mysqli_query($conn, "SELECT * FROM master_gejala ORDER BY kode_gejala ASC");

while ($row = mysqli_fetch_assoc($q_gejala)) {
    // Format array kita buat sama: ['G1' => 'Keterangan...', 'G2' => ...]
    $daftar_gejala[$row['kode_gejala']] = $row['nama_gejala'];
}

// --- 2. RULES (LOGIKA PAKAR) ---
// Rules tetap di sini karena logika berfikir (if-then) lebih efisien di code untuk skala kecil
$rules = [
    // === RULES KOMBINASI (Prioritas) ===
    [
        'conditions' => ['G1', 'G5'],
        'diagnosa' => 'Aki lemah',
        'solusi' => 'Periksa tegangan aki, kencangkan terminal, dan charge aki. Jika cepat drop setelah diisi, kemungkinan aki rusak.'
    ],
    [
        'conditions' => ['G1', 'G2', 'G5'],
        'diagnosa' => 'Aki soak / sistem pengisian rusak',
        'solusi' => 'Charge aki lalu pantau apakah dayanya cepat habis. Jika iya, aki soak. Jika aki normal tapi tetap drop, cek kiprok/regulator.'
    ],
    [
        'conditions' => ['G3', 'G5'],
        'diagnosa' => 'Dinamo starter atau relay starter lemah',
        'solusi' => 'Aki dicek dulu. Jika aki kuat tapi starter hanya "ceklek", kemungkinan relay atau motor starter bermasalah.'
    ],
    [
        'conditions' => ['G4', 'G17'],
        'diagnosa' => 'Sekring utama putus atau kabel utama putus',
        'solusi' => 'Periksa sekring utama dan soket kelistrikan dekat aki. Ganti sekring jika putus dan pastikan kabel tidak longgar.'
    ],
    [
        'conditions' => ['G6', 'G7'],
        'diagnosa' => 'Campuran bahan bakar tidak stabil',
        'solusi' => 'Bersihkan karburator atau gunakan injector cleaner. Pastikan filter udara bersih.'
    ],
    [
        'conditions' => ['G8', 'G9'],
        'diagnosa' => 'Aliran bensin kurang lancar (filter kotor / spuyer tersumbat)',
        'solusi' => 'Cek selang bensin, bersihkan spuyer karburator atau throttle body.'
    ],
    [
        'conditions' => ['G11', 'G16'],
        'diagnosa' => 'Campuran terlalu kaya (kebanyakan bensin)',
        'solusi' => 'Bersihkan filter udara, kurangi setelan bensin (karbu), atau lakukan throttle body cleaning (FI).'
    ],
    [
        'conditions' => ['G10', 'G9'],
        'diagnosa' => 'Kompresi bocor karena ring piston aus',
        'solusi' => 'Pantau apakah oli cepat berkurang. Jika iya, itu tanda ring piston harus diganti.'
    ],
    [
        'conditions' => ['G7', 'G18'],
        'diagnosa' => 'Setelan udara tidak stabil / karburator kotor',
        'solusi' => 'Setel ulang langsam, bersihkan karburator atau throttle body.'
    ],
    [
        'conditions' => ['G11', 'G12'],
        'diagnosa' => 'Pengapian tidak tepat + campuran kaya',
        'solusi' => 'Gunakan bensin oktan lebih tinggi, bersihkan busi, dan cek setelan udara/bahan bakar.'
    ],
    [
        'conditions' => ['G12', 'G15'],
        'diagnosa' => 'Pengapian maju dan mesin overheat',
        'solusi' => 'Cek oli mesin, gunakan oktan lebih baik, dan hindari beban berat hingga suhu stabil.'
    ],
    [
        'conditions' => ['G18', 'G17'],
        'diagnosa' => 'Kelistrikan drop sehingga idle tidak stabil',
        'solusi' => 'Cek sekring utama, konektor ECU/koil, dan tegangan aki.'
    ],
    [
        'conditions' => ['G13', 'G14'],
        'diagnosa' => 'Kampas rem tidak rata / cakram bengkok',
        'solusi' => 'Bersihkan kampas & cakram. Jika tarikan makin parah, piringan harus diganti.'
    ],
    [
        'conditions' => ['G9', 'G14'],
        'diagnosa' => 'CVT kotor / roller peyang',
        'solusi' => 'Bersihkan CVT, cek kondisi roller dan kampas ganda.'
    ],
    [
        'conditions' => ['G15', 'G9'],
        'diagnosa' => 'Oli terlalu panas dan pelumasan buruk',
        'solusi' => 'Ganti oli baru (viskositas sesuai). Bersihkan kisi radiator.'
    ],
    [
        'conditions' => ['G15', 'G7'],
        'diagnosa' => 'Mesin kepanasan sehingga pembakaran tidak stabil',
        'solusi' => 'Istirahatkan motor 10–15 menit. Cek radiator, coolant, dan kipas.'
    ],

    // === RULES TUNGGAL (Fallback) ===
    [ 'conditions' => ['G1'], 'diagnosa' => 'Kesulitan start saat mesin dingin', 'solusi' => 'Cek busi, aki, dan choke.' ],
    [ 'conditions' => ['G2'], 'diagnosa' => 'Sulit start saat mesin panas', 'solusi' => 'Cek busi (hitam pekat?) dan jangan main gas saat start.' ],
    [ 'conditions' => ['G3'], 'diagnosa' => 'Starter elektrik bermasalah', 'solusi' => 'Cek relay starter dan kabel dinamo.' ],
    [ 'conditions' => ['G4'], 'diagnosa' => 'Gangguan pengapian/bahan bakar', 'solusi' => 'Cek bensin, engine stop, dan kabel busi.' ],
    [ 'conditions' => ['G5'], 'diagnosa' => 'Aki lemah', 'solusi' => 'Charge atau ganti aki.' ],
    [ 'conditions' => ['G6'], 'diagnosa' => 'Tarikan ngempos', 'solusi' => 'Bersihkan filter udara dan cek selang bensin.' ],
    [ 'conditions' => ['G7'], 'diagnosa' => 'Brebet rpm rendah', 'solusi' => 'Bersihkan busi & karbu, setel langsam.' ],
    [ 'conditions' => ['G8'], 'diagnosa' => 'Brebet rpm tinggi', 'solusi' => 'Cek spuyer/injektor dan pompa bensin.' ],
    [ 'conditions' => ['G9'], 'diagnosa' => 'Tarikan awal berat', 'solusi' => 'Cek ban, CVT/V-belt, atau kopling.' ],
    [ 'conditions' => ['G10'], 'diagnosa' => 'Oli ikut terbakar', 'solusi' => 'Cek ring piston dan seal klep.' ],
    [ 'conditions' => ['G11'], 'diagnosa' => 'Campuran kaya / boros', 'solusi' => 'Cek filter udara dan setelan bensin.' ],
    [ 'conditions' => ['G12'], 'diagnosa' => 'Mesin ngelitik', 'solusi' => 'Pakai bensin oktan sesuai, cek oli.' ],
    [ 'conditions' => ['G13'], 'diagnosa' => 'Rem bunyi', 'solusi' => 'Bersihkan kampas rem.' ],
    [ 'conditions' => ['G14'], 'diagnosa' => 'Rem tidak seimbang', 'solusi' => 'Cek tekanan ban dan kondisi kampas.' ],
    [ 'conditions' => ['G15'], 'diagnosa' => 'Overheat', 'solusi' => 'Cek air radiator, kipas, dan oli.' ],
    [ 'conditions' => ['G16'], 'diagnosa' => 'Boros bensin', 'solusi' => 'Tune-up ringan, cek filter udara & busi.' ],
    [ 'conditions' => ['G17'], 'diagnosa' => 'Mati total kelistrikan', 'solusi' => 'Cek sekring utama dan aki.' ],
    [ 'conditions' => ['G18'], 'diagnosa' => 'Langsam mati-mati', 'solusi' => 'Setel langsam dan cek kebocoran intake.' ]
];
?>