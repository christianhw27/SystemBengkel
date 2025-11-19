from flask import Flask, render_template, request

app = Flask(__name__)

# --- DATA GEJALA ---
DAFTAR_GEJALA = {
    'G1': 'Mesin susah dihidupkan saat pagi / pertama kali',
    'G2': 'Mesin susah dihidupkan setelah mesin panas',
    'G3': 'Starter elektrik tidak berfungsi, kick starter masih bisa',
    'G4': 'Starter elektrik dan kick starter sama-sama tidak bisa',
    'G5': 'Lampu utama dan klakson sangat redup',
    'G6': 'Lampu normal, tapi motor ngempos saat digas',
    'G7': 'Mesin brebet di rpm rendah',
    'G8': 'Mesin brebet di rpm tinggi',
    'G9': 'Tarikan awal terasa berat / loyo',
    'G10': 'Keluar asap putih pekat dari knalpot',
    'G11': 'Keluar asap hitam pekat dari knalpot',
    'G12': 'Terdengar suara ketukan kasar / ngelitik dari mesin',
    'G13': 'Rem mengeluarkan suara berdecit',
    'G14': 'Motor terasa narik ke satu sisi saat direm',
    'G15': 'Mesin cepat panas / indikator suhu tinggi',
    'G16': 'Konsumsi bensin terasa jauh lebih boros',
    'G17': 'Speedometer dan indikator mati semua',
    'G18': 'Mesin sering mati sendiri saat langsam / idle',
}

# --- RULES KOMBINASI ---
RULES = [
    {'conditions': ['G1', 'G5'], 'diagnosa': 'Aki lemah', 'solusi': 'Periksa tegangan aki, kencangkan terminal, dan charge aki. Jika cepat drop setelah diisi, kemungkinan aki rusak.'},
    {'conditions': ['G1', 'G2', 'G5'], 'diagnosa': 'Aki soak / sistem pengisian rusak', 'solusi': 'Charge aki lalu pantau apakah dayanya cepat habis. Jika iya, aki soak. Jika aki normal tapi tetap drop, cek kiprok/regulator.'},
    {'conditions': ['G3', 'G5'], 'diagnosa': 'Dinamo starter atau relay starter lemah', 'solusi': 'Aki dicek dulu. Jika aki kuat tapi starter hanya “ceklek”, kemungkinan relay atau motor starter bermasalah.'},
    {'conditions': ['G4', 'G17'], 'diagnosa': 'Sekring utama putus atau kabel utama putus', 'solusi': 'Periksa sekring utama dan soket kelistrikan dekat aki. Ganti sekring jika putus dan pastikan kabel tidak longgar.'},
    {'conditions': ['G6', 'G7'], 'diagnosa': 'Campuran bahan bakar tidak stabil', 'solusi': 'Bersihkan karburator atau gunakan injector cleaner. Pastikan filter udara bersih.'},
    {'conditions': ['G8', 'G9'], 'diagnosa': 'Aliran bensin kurang lancar (filter kotor / spuyer tersumbat)', 'solusi': 'Cek selang bensin, bersihkan spuyer karburator atau throttle body.'},
    {'conditions': ['G11', 'G16'], 'diagnosa': 'Campuran terlalu kaya (kebanyakan bensin)', 'solusi': 'Bersihkan filter udara, kurangi setelan bensin (karbu), atau lakukan throttle body cleaning (FI).'},
    {'conditions': ['G10', 'G9'], 'diagnosa': 'Kompresi bocor karena ring piston aus', 'solusi': 'Pantau apakah oli cepat berkurang. Jika iya, itu tanda ring piston harus diganti.'},
    {'conditions': ['G7', 'G18'], 'diagnosa': 'Setelan udara tidak stabil / karburator kotor', 'solusi': 'Setel ulang langsam, bersihkan karburator atau throttle body.'},
    {'conditions': ['G11', 'G12'], 'diagnosa': 'Pengapian tidak tepat + campuran kaya', 'solusi': 'Gunakan bensin oktan lebih tinggi, bersihkan busi, dan cek setelan udara/bahan bakar.'},
    {'conditions': ['G12', 'G15'], 'diagnosa': 'Pengapian maju dan mesin overheat', 'solusi': 'Cek oli mesin, gunakan oktan lebih baik, dan hindari beban berat hingga suhu stabil.'},
    {'conditions': ['G18', 'G17'], 'diagnosa': 'Kelistrikan drop sehingga idle tidak stabil', 'solusi': 'Cek sekring utama, konektor ECU/koil, dan tegangan aki.'},
    {'conditions': ['G13', 'G14'], 'diagnosa': 'Kampas rem tidak rata / cakram bengkok', 'solusi': 'Bersihkan kampas & cakram. Jika tarikan makin parah, piringan harus diganti.'},
    {'conditions': ['G9', 'G14'], 'diagnosa': 'CVT kotor / roller peyang', 'solusi': 'Bersihkan CVT, cek kondisi roller dan kampas ganda.'},
    {'conditions': ['G15', 'G9'], 'diagnosa': 'Oli terlalu panas dan pelumasan buruk', 'solusi': 'Ganti oli baru (viskositas sesuai). Bersihkan kisi radiator.'},
    {'conditions': ['G15', 'G7'], 'diagnosa': 'Mesin kepanasan sehingga pembakaran tidak stabil', 'solusi': 'Istirahatkan motor 10–15 menit. Cek radiator, coolant, dan kipas.'},
]

# --- RULES TUNGGAL (FALLBACK) ---
SINGLE_RULES = {
    'G1': {'diagnosa': 'Kesulitan start saat mesin dingin', 'solusi': 'Cek busi, aki, dan choke.'},
    'G2': {'diagnosa': 'Sulit start saat mesin panas', 'solusi': 'Cek busi (hitam pekat?) dan jangan main gas saat start.'},
    'G3': {'diagnosa': 'Starter elektrik bermasalah', 'solusi': 'Cek relay starter dan kabel dinamo.'},
    'G4': {'diagnosa': 'Gangguan pengapian/bahan bakar', 'solusi': 'Cek bensin, engine stop, dan kabel busi.'},
    'G5': {'diagnosa': 'Aki lemah', 'solusi': 'Charge atau ganti aki.'},
    'G6': {'diagnosa': 'Tarikan ngempos', 'solusi': 'Bersihkan filter udara dan cek selang bensin.'},
    'G7': {'diagnosa': 'Brebet rpm rendah', 'solusi': 'Bersihkan busi & karbu, setel langsam.'},
    'G8': {'diagnosa': 'Brebet rpm tinggi', 'solusi': 'Cek spuyer/injektor dan pompa bensin.'},
    'G9': {'diagnosa': 'Tarikan awal berat', 'solusi': 'Cek ban, CVT/V-belt, atau kopling.'},
    'G10': {'diagnosa': 'Oli ikut terbakar', 'solusi': 'Cek ring piston dan seal klep.'},
    'G11': {'diagnosa': 'Campuran kaya / boros', 'solusi': 'Cek filter udara dan setelan bensin.'},
    'G12': {'diagnosa': 'Mesin ngelitik', 'solusi': 'Pakai bensin oktan sesuai, cek oli.'},
    'G13': {'diagnosa': 'Rem bunyi', 'solusi': 'Bersihkan kampas rem.'},
    'G14': {'diagnosa': 'Rem tidak seimbang', 'solusi': 'Cek tekanan ban dan kondisi kampas.'},
    'G15': {'diagnosa': 'Overheat', 'solusi': 'Cek air radiator, kipas, dan oli.'},
    'G16': {'diagnosa': 'Boros bensin', 'solusi': 'Tune-up ringan, cek filter udara & busi.'},
    'G17': {'diagnosa': 'Mati total kelistrikan', 'solusi': 'Cek sekring utama dan aki.'},
    'G18': {'diagnosa': 'Langsam mati-mati', 'solusi': 'Setel langsam dan cek kebocoran intake.'},
}

def perform_diagnosis(selected_symptoms):
    results = []
    
    # 1. Cek Rule Kombinasi
    for rule in RULES:
        # Cek apakah SEMUA kondisi di rule ada di gejala yang dipilih user
        if all(cond in selected_symptoms for cond in rule['conditions']):
            results.append({
                'diagnosa': rule['diagnosa'],
                'solusi': rule['solusi'],
                'conditions': rule['conditions']
            })

    # 2. Cek Single Rule (hanya jika tidak ada hasil kombinasi)
    if selected_symptoms and not results:
        for sym in selected_symptoms:
            if sym in SINGLE_RULES:
                results.append({
                    'diagnosa': SINGLE_RULES[sym]['diagnosa'],
                    'solusi': SINGLE_RULES[sym]['solusi'],
                    'conditions': [sym]
                })
    
    # 3. Default jika tidak ketemu apapun
    if selected_symptoms and not results:
        results.append({
            'diagnosa': 'Gejala tidak spesifik',
            'solusi': 'Lakukan pengecekan rutin ke bengkel.',
            'conditions': selected_symptoms
        })
        
    return results

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/diagnose', methods=['POST'])
def diagnose():
    gejala_dipilih = request.form.getlist('gejala[]')
    hasil = perform_diagnosis(gejala_dipilih)
    
    return render_template(
        'konsultasi_hasil.html', 
        gejala_dipilih=gejala_dipilih, 
        daftar_gejala=DAFTAR_GEJALA, 
        hasil_diagnosa=hasil
    )

if __name__ == '__main__':
    app.run(debug=True)