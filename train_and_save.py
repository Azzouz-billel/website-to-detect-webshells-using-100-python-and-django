import os
import sys
import pickle
import numpy as np

# Add files_1 folder to the Python path to import existing model components
sys.path.append(os.path.join(os.path.dirname(os.path.abspath(__file__)), 'files_1'))

from Model import Model
from losses import binary_cross_entropy, binary_cross_entropy_prime
import phase_1

def train_and_save_model():
    folder_path = "files_1/WhiteBlackPHP"
    feature_dimension = 256
    epochs = 300  # Train for 300 epochs for a balance of speed and convergence
    learning_rate = 0.01
    beta = 0.9

    print("[*] Loading dataset...")
    balanced_dataset = phase_1.load_and_balance_dataset(folder_path)

    raw_scripts_tokens = []
    dataset_labels = []
    
    print("[*] Tokenizing scripts...")
    for filepath, label in balanced_dataset:
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            tokens = phase_1.tokenize(f.read())
            raw_scripts_tokens.append(tokens)
            dataset_labels.append(label)

    print("[*] Building vocabulary...")
    vocabulary = phase_1.get_vocabulary(raw_scripts_tokens, max_features=feature_dimension)
    vocab_index = phase_1.get_vocab_index(vocabulary)

    # We need to compute TF-IDF manually to also extract and save the IDF weights
    print("[*] Computing TF-IDF vectors and saving IDF weights...")
    num_docs = len(raw_scripts_tokens)
    from collections import defaultdict, Counter
    import math

    df = defaultdict(int)
    for tokens in raw_scripts_tokens:
        for token in set(tokens):
            if token in vocab_index:
                df[token] += 1

    # Calculate Smoothed IDF
    idf = {}
    for word in vocabulary:
        idf[word] = math.log((1 + num_docs) / (1 + df[word])) + 1

    # Calculate TF-IDF vectors
    X_list = []
    for tokens in raw_scripts_tokens:
        vector = [0.0] * feature_dimension
        counts = Counter(tokens)
        total_tokens = len(tokens)
        for word, count in counts.items():
            if word in vocab_index:
                tf = count / total_tokens if total_tokens > 0 else 0
                vector[vocab_index[word]] = tf * idf[word]
        X_list.append(vector)

    X = np.array(X_list)
    Y = np.array(dataset_labels).reshape(-1, 1)

    print(f"[*] Training Data Shape: {X.shape}")
    print(f"[*] Training Labels Shape: {Y.shape}")

    # Build the model using deep factory
    hidden_layers = 42
    neurons_per_layer = 64
    print(f"[*] Building factory neural network with {hidden_layers} hidden layers...")
    model = Model.build_deep_factory(
        input_dim=feature_dimension,
        hidden_depth=hidden_layers,
        hidden_width=neurons_per_layer
    )

    print(f"[*] Training model for {epochs} epochs...")
    errors = model.fit(
        X, Y,
        binary_cross_entropy,
        binary_cross_entropy_prime,
        epochs=epochs,
        learning_rate=learning_rate,
        verbose=True,
        beta=beta
    )

    # Save model weights, vocabulary, and IDFs
    print("[*] Saving trained model state...")
    
    # Extract weights and biases from the layers
    network_params = []
    from layer import Dense
    for i, layer in enumerate(model.network):
        if isinstance(layer, Dense):
            network_params.append({
                'layer_index': i,
                'weights': layer.weights,
                'bias': layer.bias
            })

    model_state = {
        'hidden_depth': hidden_layers,
        'hidden_width': neurons_per_layer,
        'feature_dimension': feature_dimension,
        'vocabulary': vocabulary,
        'idf': idf,
        'network_params': network_params
    }

    # Save to a file in the directory
    os.makedirs('detector', exist_ok=True)
    weights_path = 'detector/trained_model.pkl'
    with open(weights_path, 'wb') as f:
        pickle.dump(model_state, f)

    print(f"[+] Model state successfully saved to {weights_path}!")
    
    # Calculate final accuracy on training set
    accuracy = model.accuracy(X, Y)
    print(f"[+] Final Training Accuracy: {accuracy * 100:.2f}%")

if __name__ == "__main__":
    train_and_save_model()
