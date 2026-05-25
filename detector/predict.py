import os
import sys
import pickle
import re
import numpy as np
from collections import Counter

# Add the current directory to sys.path to resolve unmodified imports in model files
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from Model import Model
from layer import Dense

# Global model state cache
_MODEL = None
_VOCABULARY = None
_IDF = None

def load_trained_model():
    """
    Loads the trained model state (weights, biases, vocabulary, IDFs) from pickle
    and builds the Model instance. Caches the model in memory.
    """
    global _MODEL, _VOCABULARY, _IDF
    if _MODEL is not None:
        return _MODEL, _VOCABULARY, _IDF

    pickle_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'trained_model.pkl')
    
    if not os.path.exists(pickle_path):
        raise FileNotFoundError(f"Trained model state file not found at {pickle_path}. Run training first.")

    with open(pickle_path, 'rb') as f:
        model_state = pickle.load(f)

    # Reconstruct the model
    hidden_depth = model_state['hidden_depth']
    hidden_width = model_state['hidden_width']
    feature_dimension = model_state['feature_dimension']
    _VOCABULARY = model_state['vocabulary']
    _IDF = model_state['idf']

    model = Model.build_deep_factory(
        input_dim=feature_dimension,
        hidden_depth=hidden_depth,
        hidden_width=hidden_width
    )

    # Override layers weights and biases from saved params
    dense_idx = 0
    for layer in model.network:
        if isinstance(layer, Dense):
            saved_layer = model_state['network_params'][dense_idx]
            layer.weights = saved_layer['weights']
            layer.bias = saved_layer['bias']
            dense_idx += 1

    _MODEL = model
    return _MODEL, _VOCABULARY, _IDF

def extract_suspicious_features(php_code):
    """
    Statically analyzes PHP code to detect presence of typical webshell keywords/patterns.
    Returns a dict with counts of detected indicators.
    """
    features = {
        'eval': len(re.findall(r'\beval\s*\(', php_code, re.IGNORECASE)),
        'base64_decode': len(re.findall(r'\bbase64_decode\b', php_code, re.IGNORECASE)),
        'shell_exec': len(re.findall(r'\bshell_exec\b', php_code, re.IGNORECASE)),
        'system': len(re.findall(r'\bsystem\s*\(', php_code, re.IGNORECASE)),
        'exec': len(re.findall(r'\bexec\s*\(', php_code, re.IGNORECASE)),
        'passthru': len(re.findall(r'\bpassthru\b', php_code, re.IGNORECASE)),
        'assert': len(re.findall(r'\bassert\s*\(', php_code, re.IGNORECASE)),
        'str_rot13': len(re.findall(r'\bstr_rot13\b', php_code, re.IGNORECASE)),
        'create_function': len(re.findall(r'\bcreate_function\b', php_code, re.IGNORECASE)),
        'popen': len(re.findall(r'\bpopen\b', php_code, re.IGNORECASE)),
        'proc_open': len(re.findall(r'\bproc_open\b', php_code, re.IGNORECASE)),
    }

    # Count long base64-like strings: alphanumeric with + and / of length 60+
    long_strings = re.findall(r'["\']([A-Za-z0-9+/]{60,})["\']', php_code)
    features['long_encoded_strings'] = len(long_strings)

    # Superglobals used for input handling
    superglobals = ['_POST', '_GET', '_REQUEST', '_COOKIE', '_SERVER', 'GLOBALS']
    superglobals_count = 0
    for sg in superglobals:
        superglobals_count += len(re.findall(r'\$' + sg + r'\b', php_code))
    features['suspicious_superglobals'] = superglobals_count

    # Variable variables like $$variable_name
    features['variable_variables'] = len(re.findall(r'\$\$\w+', php_code))

    return features

def tokenize_code(text):
    """
    Reuses tokenization pattern from phase_1 to keep feature engineering identical.
    """
    return re.findall(r'\b\w+\b', text.lower())

def compute_tfidf_single(tokens, vocabulary, idf):
    """
    Vectorizes a single document using the vocabulary and IDF weights computed during training.
    """
    vocab_index = {word: i for i, word in enumerate(vocabulary)}
    vector_dim = len(vocabulary)
    vector = [0.0] * vector_dim
    
    counts = Counter(tokens)
    total_tokens = len(tokens)
    
    for word, count in counts.items():
        if word in vocab_index:
            tf = count / total_tokens if total_tokens > 0 else 0
            vector[vocab_index[word]] = tf * idf[word]
            
    return np.array(vector)

def detect_webshell(file_content):
    """
    Analyzes PHP content, processes it, feeds it to the NumPy NN model,
    and returns prediction class, confidence score, and detected features.
    """
    try:
        model, vocabulary, idf = load_trained_model()
    except Exception as e:
        return {
            'error': f"Failed to load model: {str(e)}",
            'is_malicious': False,
            'confidence': 0.0,
            'features': {}
        }

    # 1. Preprocess and vectorize PHP content
    tokens = tokenize_code(file_content)
    x_vector = compute_tfidf_single(tokens, vocabulary, idf)

    # 2. Feed-forward pass to predict
    # Model predict returns shape (batch, 1)
    prediction_raw = model.predict(x_vector)[0][0]
    
    # 3. Process outputs
    is_malicious = bool(prediction_raw >= 0.5)
    
    # If malicious, confidence is the raw sigmoid output; if safe, it is (1 - sigmoid output)
    confidence = float(prediction_raw if is_malicious else (1.0 - prediction_raw))

    # 4. Extract heuristic indicators for display
    heuristic_features = extract_suspicious_features(file_content)

    return {
        'is_malicious': is_malicious,
        'confidence': confidence,
        'features': heuristic_features,
        'tokens_count': len(tokens),
        'raw_sigmoid': float(prediction_raw)
    }
