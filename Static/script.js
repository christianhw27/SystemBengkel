document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('diagnosaForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Ambil semua checkbox yang dicentang
            const checkedBoxes = document.querySelectorAll('input[name="gejala[]"]:checked');
            
            // Jika tidak ada yang dicentang, cegah submit dan beri peringatan
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('Mohon pilih setidaknya satu gejala kerusakan motor Anda.');
            }
        });
    }
});