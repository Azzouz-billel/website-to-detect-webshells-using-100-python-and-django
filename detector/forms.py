from django import forms
from django.core.exceptions import ValidationError

class PHPFileUploadForm(forms.Form):
    php_file = forms.FileField(
        label="Select PHP File",
        help_text="Upload a .php file to scan for webshell indicators.",
        widget=forms.ClearableFileInput(attrs={
            'class': 'form-control',
            'accept': '.php',
            'id': 'php_file_input'
        })
    )

    def clean_php_file(self):
        file = self.cleaned_data.get('php_file')
        if not file:
            raise ValidationError("Please upload a file.")

        # 1. Verify file extension
        filename = file.name
        if not filename.lower().endswith('.php'):
            raise ValidationError("Invalid file type. Only files ending with '.php' are allowed.")

        # 2. Verify file size (limit to 2MB)
        max_size_mb = 2.0
        if file.size > max_size_mb * 1024 * 1024:
            raise ValidationError(f"File size exceeds the limit of {max_size_mb} MB.")

        # 3. Verify file is not empty
        if file.size == 0:
            raise ValidationError("The uploaded file is empty.")

        return file
