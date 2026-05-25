import os
import sys

# Add the project root to Python path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from detector.predict import detect_webshell

def test_inference():
    # Pick a sample black (webshell) file and a white (benign) file from the dataset
    black_file_path = "files_1/WhiteBlackPHP/black_002bc49a3e30f74b197fd9dd3167f8bc.php"
    white_file_path = "files_1/WhiteBlackPHP/white_001815a293a50649bef40145d7b5b5d5.php"

    print("=== Testing Malicious File ===")
    if os.path.exists(black_file_path):
        with open(black_file_path, "r", encoding="utf-8", errors="ignore") as f:
            content = f.read()
        res = detect_webshell(content)
        print(f"File: {black_file_path}")
        print(f"Prediction: {res}")
    else:
        print(f"File not found: {black_file_path}")

    print("\n=== Testing Benign File ===")
    if os.path.exists(white_file_path):
        with open(white_file_path, "r", encoding="utf-8", errors="ignore") as f:
            content = f.read()
        res = detect_webshell(content)
        print(f"File: {white_file_path}")
        print(f"Prediction: {res}")
    else:
        print(f"File not found: {white_file_path}")

if __name__ == "__main__":
    test_inference()
