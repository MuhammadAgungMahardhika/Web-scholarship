# storage/app/ml/train_model.py
import pandas as pd
from sklearn.ensemble import RandomForestClassifier
import joblib

print("Memulai training model...")

# 1. Muat data dari CSV
data = pd.read_csv('storage/app/ml/training_data.csv')

# 2. Pisahkan fitur (X) dan target (y)
features = ['gpa', 'parent_income', 'final_score']
X = data[features]
y = data['status']

# 3. Latih model
# RandomForestClassifier adalah pilihan yang solid dan kuat
model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X, y)

# 4. Simpan model yang sudah dilatih ke dalam file
joblib.dump(model, 'storage/app/ml/scholarship_model.joblib')

print("Model berhasil dilatih dan disimpan sebagai scholarship_model.joblib")