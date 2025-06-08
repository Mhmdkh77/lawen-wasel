<!DOCTYPE html>
<html lang="en">

   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="icon" href="{{ Vite::asset('resources/images/logo.png') }}" type="image/png">
      <title>Dashboard</title>
      @vite(['resources/css/app.css', 'resources/js/app.js'])
      @livewireStyles
   </head>
   {{ $slot }}

</html>