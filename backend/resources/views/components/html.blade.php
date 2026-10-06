@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="en">

   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="icon" href="{{ Vite::asset('resources/images/logo.png') }}" type="image/png">
      <title>{{ $title }}</title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
      @vite(['resources/css/app.css', 'resources/js/app.js'])
      @livewireStyles
   </head>
   {{ $slot }}

</html>
