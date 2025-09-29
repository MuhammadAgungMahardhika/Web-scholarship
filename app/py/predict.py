# storage/app/ml/predict.py
import joblib
import pandas as pd
import sys
import json

# Muat model yang sudah ada
try:
    model = joblib.load('storage/app/ml/scholarship_model.joblib')
    
    # Terima data dari argumen command line dalam format JSON
    input_data = json.loads(sys.argv[1])
    
    # Buat DataFrame dari input tunggal
    df = pd.DataFrame([input_data], columns=['gpa', 'parent_income', 'final_score'])
    
    # Lakukan prediksi probabilitas
    # Hasilnya -> [probabilitas_ditolak, probabilitas_disetujui]
    probability = model.predict_proba(df)[0]
    
    # Kembalikan hasil sebagai JSON ke PHP
    print(json.dumps({
        'prediction': int(probability.argmax()), 
        'probability_approved': float(probability[1]) # Probabilitas untuk kelas '1' (approved)
    }))

except Exception as e:
    print(json.dumps({'error': str(e)}))