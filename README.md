# Website to Detect Webshells using 100% Python and Django

A web application built entirely with **Python** and **Django** that detects malicious webshells using a trained machine learning model. Upload your files and the app scans them to identify potential webshells (PHP, ASP, JSP, and more).

## Features

-  **Webshell Detection** — Scans uploaded files and flags potential webshells using a trained ML model.
-  **Machine Learning Powered** — Uses a custom-trained model to classify files as malicious or benign.
-  **Web Interface** — Clean Django-based front end for uploading and scanning files.
-  **File Counting & Reporting** — Includes utilities to count and process datasets.
-  **Deploy-Ready** — Configured for Vercel deployment with WhiteNoise for static files.

## Tech Stack

- **Backend:** Python, Django
- **ML:** Custom-trained classification model
- **Static Files:** WhiteNoise
- **Deployment:** Vercel

## Project Structure

```
.
├── detector/           # Core detection logic
├── files_1/            # File samples / working directory
├── scanner/            # Scanning module & static file config
├── static/css/         # Stylesheets
├── templates/          # HTML templates
├── count_files.py      # Utility to count dataset files
├── manage.py           # Django management entry point
├── requirements.txt    # Python dependencies
├── test_predict.py     # Test predictions on the model
├── train_and_save.py   # Train and save the ML model
├── vercel.json         # Vercel deployment config
└── vercel_wsgi.py      # WSGI entry point for Vercel
```

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Azzouz-billel/website-to-detect-webshells-using-100-python-and-django.git
   cd website-to-detect-webshells-using-100-python-and-django
   ```

2. **Create and activate a virtual environment**
   ```bash
   python -m venv venv
   source venv/bin/activate    # On Windows: venv\Scripts\activate
   ```

3. **Install dependencies**
   ```bash
   pip install -r requirements.txt
   ```

4. **Apply migrations**
   ```bash
   python manage.py migrate
   ```

5. **Run the development server**
   ```bash
   python manage.py runserver
   ```

   Open `http://127.0.0.1:8000/` in your browser.

## Training the Model

Use the provided script to train and save your model:

```bash
python train_and_save.py
```

To test predictions:

```bash
python test_predict.py
```

## 📩 Dataset / Training Data

The dataset used to train the model is **not included** in this repository.

**For the data to train the model, please contact me.**

## Deployment

This project is configured for **Vercel**. The `vercel.json` and `vercel_wsgi.py` files handle the deployment setup, and static files are served via WhiteNoise.

## Contributing

Contributions, issues, and feature requests are welcome. Feel free to open an issue or submit a pull request.

## License

This project is open source. Add your preferred license here.

---

Made with 100% Python and Django.
