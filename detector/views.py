from django.shortcuts import render
from .forms import PHPFileUploadForm
from .predict import detect_webshell

def scan_file_view(request):
    """
    Main view to upload, validate, scan a PHP file, and display ML results.
    """
    form = PHPFileUploadForm()
    result = None
    filename = None
    file_content_preview = None
    suspicious_indicators = []

    if request.method == 'POST':
        form = PHPFileUploadForm(request.POST, request.FILES)
        if form.is_valid():
            uploaded_file = request.FILES['php_file']
            filename = uploaded_file.name
            
            try:
                # Read file content safely, handling encoding errors gracefully
                raw_data = uploaded_file.read()
                try:
                    file_content = raw_data.decode('utf-8')
                except UnicodeDecodeError:
                    # Fallback to latin-1 to handle binary data or obfuscated PHP code
                    file_content = raw_data.decode('latin-1', errors='ignore')

                # Run webshell detection pipeline
                result = detect_webshell(file_content)

                if 'error' not in result:
                    # Extract list of features that were actually found (count > 0)
                    for key, val in result['features'].items():
                        if val > 0:
                            # Reformat feature name for nice presentation
                            nice_name = key.replace('_', ' ').title()
                            suspicious_indicators.append({
                                'name': nice_name,
                                'count': val,
                                'raw_key': key
                            })

                    # Prepare code snippet preview (limit to first 1500 chars)
                    if len(file_content) > 1500:
                        file_content_preview = file_content[:1500] + "\n\n... [File truncated for preview] ..."
                    else:
                        file_content_preview = file_content
                
            except Exception as e:
                form.add_error('php_file', f"An error occurred while reading the file: {str(e)}")

    context = {
        'form': form,
        'result': result,
        'filename': filename,
        'file_content_preview': file_content_preview,
        'suspicious_indicators': suspicious_indicators,
    }
    return render(request, 'detector/index.html', context)
