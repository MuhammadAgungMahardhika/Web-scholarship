import joblib
import pandas as pd
import os
import json
from flask import Flask, request, jsonify

# --- SETUP APLIKASI FLASK ---
app = Flask(__name__)

# --- MUAT MODEL SATU KALI SAAT SERVER DIMULAI ---
# Path ke direktori tempat script ini berada
script_dir = os.path.dirname(os.path.abspath(__file__))
# Naik satu tingkat untuk mendapatkan direktori utama proyek
project_root = os.path.dirname(script_dir)

# Sekarang path ke model sudah benar
model_path = os.path.join(project_root, 'storage', 'app', 'ml', 'scholarship_model.joblib')

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
        input_data = request.get_json(force=True)
        
        # Pastikan kolom yang dibutuhkan ada di input
        required_columns = ['gpa', 'parent_income', 'final_score']
        if not all(col in input_data for col in required_columns):
            return jsonify({'error': 'Missing required columns in input data.'}), 400

        df = pd.DataFrame([input_data], columns=required_columns)
        
        probability = model.predict_proba(df)[0]
        
        return jsonify({
            'prediction': int(probability.argmax()),
            'probability_approved': float(probability[1])
        })
    except Exception as e:
        # Menangkap error lain saat prediksi, misal tipe data salah
        return jsonify({'error': f'Prediction error: {str(e)}'}), 400


# --- JALANKAN SERVER ---
if __name__ == '__main__':
    app.run(port=5000, debug=True)