import os

folder_path = "files_1/WhiteBlackPHP"
if not os.path.exists(folder_path):
    print("Folder does not exist!")
else:
    white = 0
    black = 0
    other = 0
    for filename in os.listdir(folder_path):
        if filename.endswith('.php'):
            name_lower = filename.lower()
            if 'white' in name_lower or 'benign' in name_lower:
                white += 1
            elif 'black' in name_lower or 'webshell' in name_lower or 'malicious' in name_lower:
                black += 1
            else:
                other += 1
    print(f"White: {white}, Black: {black}, Other: {other}, Total: {white + black + other}")
