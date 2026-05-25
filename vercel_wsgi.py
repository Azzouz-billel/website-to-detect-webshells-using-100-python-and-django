import os
from django.core.wsgi import get_wsgi_application

# Replace 'detector' with your core folder name if it's different
os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'scanner.settings')

application = get_wsgi_application()
app = application
