@php($faviconVersion = file_exists(public_path('img/logo.png')) ? filemtime(public_path('img/logo.png')) : time())
<link rel="shortcut icon" type="image/png" href="{{ asset('img/logo.png') }}?v={{ $faviconVersion }}">
<link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}?v={{ $faviconVersion }}">
<link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}?v={{ $faviconVersion }}">
