from django.urls import path
from . import views

app_name = 'detector'

urlpatterns = [
    path('', views.scan_file_view, name='scan'),
]
