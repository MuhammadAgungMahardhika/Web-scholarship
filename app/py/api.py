import joblib
import pandas as pd
import os
import json
from flask import Flask, request, jsonify

# --- SETUP APLIKASI FLASK ---
app = Flask(__name__)

# --- MUAT MODEL SATU KALI SAAT SERVER DIMULAI ---
# Ini jauh lebih efisien daripada memuatnya setiap kali ada request
base_path = os.path.dirname(os.path.abspath(__file__))
model_path = os.path.join(base_path, 'scholarship_model.joblib')
model = None

try:
    model = joblib.load(model_path)
    print(">>> Model 'scholarship_model.joblib' berhasil dimuat.")
except FileNotFoundError:
    print(f">>> ERROR: File model tidak ditemukan di {model_path}")
except Exception as e:
    print(f">>> ERROR: Gagal memuat model. {e}")


# --- DEFINISIKAN ROUTE UNTUK PREDIKSI ---
@app.route('/predict', methods=['POST'])
def predict():
    global model
    if model is None:
        return jsonify({'error': 'Model is not loaded.'}), 500

    try:
        # Ambil data JSON yang dikirim oleh Laravel
        input_data = request.get_json()
        
        # Buat DataFrame dari input
        df = pd.DataFrame([input_data], columns=['gpa', 'parent_income', 'final_score'])
        
        # Lakukan prediksi probabilitas
        probability = model.predict_proba(df)[0]
        
        # Kembalikan hasil sebagai JSON
        return jsonify({
            'prediction': int(probability.argmax()),
            'probability_approved': float(probability[1])
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 400


# --- JALANKAN SERVER ---
if __name__ == '__main__':
    # Jalankan di port 5000
    app.run(port=5000, debug=True)