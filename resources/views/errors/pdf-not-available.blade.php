<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Not Available</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full text-center">
        <div class="text-6xl text-red-500 mb-4">📄</div>
        <h1 class="text-2xl font-bold text-gray-800 mb-4">PDF Preview Not Available</h1>
        <p class="text-gray-600 leading-relaxed mb-6">
            {{ $message ?? 'The PDF document could not be loaded at this time.' }}
        </p>
        
        @if(isset($doc))
        <div class="bg-gray-50 rounded-md p-4 mb-6 text-left">
            <h3 class="font-semibold text-gray-800 mb-2">Document Details:</h3>
            <div class="text-sm text-gray-600 space-y-1">
                <div><span class="font-medium">FETS ID:</span> {{ $doc->created_at->format('Ymd') }}-{{ $doc->id }}</div>
                <div><span class="font-medium">Submitted by:</span> {{ $doc->submitter->fullname ?? 'Unknown' }}</div>
                <div><span class="font-medium">Status:</span> {{ ucfirst($doc->status) }}</div>
            </div>
        </div>
        @endif
        
        <p class="text-xs text-gray-500">
            Please contact the document owner or system administrator if this issue persists.
        </p>
        
        <div class="mt-6">
            <button onclick="window.history.back()" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                Go Back
            </button>
        </div>
    </div>
</body>
</html>