<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>File Manager</title>

    {{-- CSS Dependencies (Required for File Manager UI) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">

    <style>
        body { margin: 0; padding: 0; overflow: hidden; background: #f8f9fa; }
        .fm-navbar { border-bottom: 1px solid #dee2e6; }
    </style>
</head>
<body>

    {{-- Full Height Container --}}
    <div id="fm" style="height: 100vh;"></div>

    {{-- JS Dependencies --}}
    <script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>
</body>
</html>
