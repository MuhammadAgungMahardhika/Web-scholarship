import sys
import json

# Script ini akan menerima argumen dari baris perintah.
# sys.argv[0] adalah nama script itu sendiri ('add.py')
# sys.argv[1] adalah argumen pertama yang kita kirim (angka pertama)
# sys.argv[2] adalah argumen kedua (angka kedua)

try:
    # 1. Ambil angka dari argumen dan ubah menjadi tipe number (float)
    num1 = float(sys.argv[1])
    num2 = float(sys.argv[2])

    # 2. Lakukan operasi sederhana (penjumlahan)
    result = num1 + num2

    # 3. Siapkan output dalam format dictionary
    output = {'result': result, 'error': None}

except Exception as e:
    # Jika ada error (misal, input bukan angka), siapkan pesan error
    output = {'result': None, 'error': str(e)}

# 4. Cetak output sebagai string JSON. Ini adalah cara terbaik agar
#    PHP bisa membacanya dengan mudah dan konsisten.
print(json.dumps(output))