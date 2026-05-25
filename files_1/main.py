import numpy as np
import matplotlib.pyplot as plt
from sklearn.model_selection import train_test_split

# Seed for reproducibility
np.random.seed(42)

from Model import Model
from losses import binary_cross_entropy, binary_cross_entropy_prime
import phase_1

# Helper functions
def load_and_balance_from_folder(folder_path):
    return phase_1.load_and_balance_dataset(folder_path)

def tokenize(text):
    return phase_1.tokenize(text)

def build_global_vocabulary(tokenized_scripts, max_features=256):
    return phase_1.get_vocabulary(tokenized_scripts, max_features)

def compute_tfidf_vectors(tokenized_scripts, vocab):
    vectors = phase_1.tf_idf(tokenized_scripts, vocab)
    return np.array([np.array(v) for v in vectors])

def one_hot_encode(tokenized_scripts, vocab):
    vectors = phase_1.one_hot_encoding(tokenized_scripts, vocab)
    return np.array([np.array(v) for v in vectors])

def bag_of_words(tokenized_scripts, vocab):
    vectors = phase_1.bag_of_words(tokenized_scripts, vocab)
    return np.array([np.array(v) for v in vectors])

if __name__ == "__main__":
    FOLDER_NAME = "WhiteBlackPHP"
    FEATURE_DIMENSION = 256
    print("[*] Phase 1: Loading data and engineering features...")
    try:
        balanced_dataset = load_and_balance_from_folder(FOLDER_NAME)
        raw_scripts_tokens = []
        dataset_labels = []
        for filepath, label in balanced_dataset:
            with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                raw_scripts_tokens.append(tokenize(f.read()))
                dataset_labels.append(label)
        vocabulary = build_global_vocabulary(raw_scripts_tokens, max_features=FEATURE_DIMENSION)
        X = compute_tfidf_vectors(raw_scripts_tokens, vocabulary)
        Y = np.array(dataset_labels).reshape(-1, 1)
        print("[*] Phase 1: Extracting One-Hot and Bag-of-Words vectors...")
        X_oh = one_hot_encode(raw_scripts_tokens, vocabulary)
        X_bow = bag_of_words(raw_scripts_tokens, vocabulary)
    except Exception as e:
        print(f"[!] Target directory missing or empty: {e}")
        print("[!] Generating synthetic verification dataset for pipeline testing...")
        X = np.random.randn(100, FEATURE_DIMENSION)
        X_oh = np.random.randint(0, 2, size=(100, FEATURE_DIMENSION))
        X_bow = np.random.randint(0, 5, size=(100, FEATURE_DIMENSION))
        Y = np.random.randint(0, 2, size=(100, 1))
    # Train/test split
    X_train, X_test, X_oh_train, X_oh_test, X_bow_train, X_bow_test, Y_train, Y_test = train_test_split(
        X, X_oh, X_bow, Y, test_size=0.30, random_state=42, stratify=Y
    )
    print(f"[*] Training Data Shape:  {X_train.shape}")
    print(f"[*] Training Labels Shape: {Y_train.shape}")
    # Model configuration
    HIDDEN_LAYERS = 42
    NEURONS_PER_LAYER = 64
    print(f"\n[*] Phase 2: Deploying Automated Model Factory ({HIDDEN_LAYERS} hidden layers)...")
    # Vectorization analysis
    print("\n[*] Phase 3: Running Comprehensive Vectorization Analysis...")
    vectorizer_configs = [
        ("One-Hot", X_oh_train,  X_oh_test,  'royalblue'),
        ("BoW",     X_bow_train, X_bow_test, 'darkorange'),
        ("TF-IDF",  X_train,     X_test,      'crimson'),
    ]
    comparison_results = {}
    for vec_name, X_tr, X_te, color in vectorizer_configs:
        print(f"\n[*] Training model with {vec_name} vectorization...")
        vec_model = Model.build_deep_factory(
            input_dim=FEATURE_DIMENSION,
            hidden_depth=HIDDEN_LAYERS,
            hidden_width=NEURONS_PER_LAYER
        )
        vec_errors = vec_model.fit(
            X_tr, Y_train,
            binary_cross_entropy, binary_cross_entropy_prime,
            epochs=1000, learning_rate=0.01, verbose=True, beta=0.9
        )
        vec_accuracy = vec_model.accuracy(X_te, Y_test)
        comparison_results[vec_name] = (vec_errors, vec_accuracy, color)
        print(f"[+] {vec_name} — Final Test Accuracy: {vec_accuracy * 100:.2f}%")
    # Visualization
    if "TF-IDF" in comparison_results:
        tfidf_errors, tfidf_acc, tfidf_color = comparison_results["TF-IDF"]
        plt.figure(figsize=(10, 5))
        plt.plot(tfidf_errors, label=f'TF-IDF ({HIDDEN_LAYERS} layers)', color=tfidf_color, linewidth=2)
        plt.title('Phase 2/3: 40+ Layer Network Loss Convergence (TF-IDF)')
        plt.xlabel('Epochs')
        plt.ylabel('Binary Cross Entropy')
        plt.grid(True, linestyle='--', alpha=0.5)
        plt.legend()
        plt.tight_layout()
        plt.show()
    plt.figure(figsize=(12, 6))
    for vec_name, (vec_errors, vec_accuracy, color) in comparison_results.items():
        plt.plot(vec_errors, label=f'{vec_name} (Accuracy: {vec_accuracy * 100:.1f}%)', color=color, linewidth=2)
    plt.title(f'Phase 3: Comparative Vectorization — {HIDDEN_LAYERS}-Layer Network')
    plt.xlabel('Epochs')
    plt.ylabel('Binary Cross Entropy')
    plt.grid(True, linestyle='--', alpha=0.5)
    plt.legend()
    plt.tight_layout()
    plt.show()
    # Benchmark Summary
    print("\n" + "=" * 60)
    print(" PHASE 3 FINAL BENCHMARK SUMMARY")
    print("=" * 60)
    for vec_name, (_, vec_accuracy, _) in comparison_results.items():
        print(f"  {vec_name:<10} -> Test Accuracy: {vec_accuracy * 100:.2f}%")
    print("=" * 60)