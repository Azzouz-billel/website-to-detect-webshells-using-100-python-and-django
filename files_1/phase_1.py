import os
import re
import math
import random
from collections import defaultdict, Counter

# Tokenization & vocabulary management

def tokenize(text):
    # Extract alphanumeric tokens from a PHP script
    return re.findall(r'\b\w+\b', text.lower())

def get_vocabulary(tokenized_scripts, max_features=256):
    # Return top N most frequent tokens
    global_counter = Counter()
    for tokens in tokenized_scripts:
        global_counter.update(tokens)
    most_common = global_counter.most_common(max_features)
    vocab = [word for word, count in most_common]
    return sorted(vocab)  # alphabetical order for consistent indexing

def get_vocab_index(vocab):
    # Map each vocabulary word to a unique integer index
    return {word: i for i, word in enumerate(vocab)}

# Vectorization functions

def one_hot_encoding(tokenized_scripts, vocab):
    # One-hot encode presence of tokens
    vocab_index = get_vocab_index(vocab)
    vector_dim = len(vocab)
    vectors = []
    for tokens in tokenized_scripts:
        vector = [0] * vector_dim
        for word in set(tokens):
            if word in vocab_index:
                vector[vocab_index[word]] = 1
        vectors.append(vector)
    return vectors

def bag_of_words(tokenized_scripts, vocab):
    # Compute bag-of-words token frequencies
    vocab_index = get_vocab_index(vocab)
    vector_dim = len(vocab)
    vectors = []
    for tokens in tokenized_scripts:
        vector = [0] * vector_dim
        counts = Counter(tokens)
        for word, count in counts.items():
            if word in vocab_index:
                vector[vocab_index[word]] = count
        vectors.append(vector)
    return vectors

def tf_idf(tokenized_scripts, vocab):
    # Compute TF-IDF vectors
    vocab_index = get_vocab_index(vocab)
    vector_dim = len(vocab)
    vectors = []
    num_docs = len(tokenized_scripts)
    # Document frequency
    df = defaultdict(int)
    for tokens in tokenized_scripts:
        for token in set(tokens):
            if token in vocab_index:
                df[token] += 1
    # Smoothed IDF
    idf = {}
    for word in vocab:
        idf[word] = math.log((1 + num_docs) / (1 + df[word])) + 1
    # TF-IDF calculation
    for tokens in tokenized_scripts:
        vector = [0.0] * vector_dim
        counts = Counter(tokens)
        total_tokens = len(tokens)
        for word, count in counts.items():
            if word in vocab_index:
                tf = count / total_tokens if total_tokens > 0 else 0
                vector[vocab_index[word]] = tf * idf[word]
        vectors.append(vector)
    return vectors

# Data loading and balancing

def load_and_balance_dataset(folder_path):
    # Load PHP files and create a balanced 50/50 dataset
    white_paths = []
    black_paths = []
    if not os.path.isdir(folder_path):
        raise FileNotFoundError(f"Directory '{folder_path}' not found.")
    print("Reading script files from target directory…")
    for filename in os.listdir(folder_path):
        if filename.endswith('.php'):
            file_path = os.path.join(folder_path, filename)
            name_lower = filename.lower()
            if 'white' in name_lower or 'benign' in name_lower:
                white_paths.append(file_path)
            elif 'black' in name_lower or 'webshell' in name_lower or 'malicious' in name_lower:
                black_paths.append(file_path)
    print(f"Discovered balance -> White: {len(white_paths)} files, Black: {len(black_paths)} files")
    min_samples = min(len(white_paths), len(black_paths))
    if min_samples == 0:
        raise ValueError("One class has no files; cannot create a balanced set.")
    random.seed(42)
    random.shuffle(white_paths)
    random.shuffle(black_paths)
    final_paths = white_paths[:min_samples] + black_paths[:min_samples]
    final_labels = [0] * min_samples + [1] * min_samples
    print(f"Balanced dataset created: {len(final_paths)} instances ({min_samples} each class)")
    return list(zip(final_paths, final_labels))

# Standalone execution pipeline
if __name__ == "__main__":
    FOLDER_PATH = "WhiteBlackPHP"
    MAX_FEATURES = 256
    balanced_data = load_and_balance_dataset(FOLDER_PATH)
    raw_scripts = []
    for path, label in balanced_data:
        with open(path, "r", encoding="latin-1", errors="ignore") as f:
            raw_scripts.append(f.read())
    print("\nTokenizing balanced dataset…")
    tokenized_corpus = [tokenize(script) for script in raw_scripts]
    print(f"Extracting {MAX_FEATURES} global features…")
    vocabulary = get_vocabulary(tokenized_corpus, max_features=MAX_FEATURES)
    print(f"Vocabulary size: {len(vocabulary)}")
    print("=== Processing vector representations ===")
    matrix_one_hot = one_hot_encoding(tokenized_corpus, vocabulary)
    print(f"One-hot matrix: {len(matrix_one_hot)} x {len(matrix_one_hot[0])}")
    matrix_bow = bag_of_words(tokenized_corpus, vocabulary)
    print(f"Bag-of-words matrix: {len(matrix_bow)} x {len(matrix_bow[0])}")
    matrix_tfidf = tf_idf(tokenized_corpus, vocabulary)
    print(f"TF-IDF matrix: {len(matrix_tfidf)} x {len(matrix_tfidf[0])}")
    print("\n=== Pipeline check successful ===")
    print(f"Vector dimension {len(matrix_tfidf[0])} matches target ({MAX_FEATURES})")
    print("Ready for Phase 2 neural network input.")
