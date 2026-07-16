<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? 'Boardly' }}</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
