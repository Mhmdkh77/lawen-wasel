<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="icon" href="{{ Vite::asset('resources/images/logo.png') }}" type="image/png">
   <title>Dashboard</title>
   @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-300 flex w-screen h-screen">

   <nav class="w-1/6 bg-gray-50 flex  flex-col items-center ">
      <div class="">
         <img src="{{ Vite::asset('resources/images/logo.png') }}" class="w-32" alt="Logo">
      </div>
      <ul class="space-y-2 w-full">
         <li><a href="#" class="block hover:bg-gray-700 hover:text-white p-2 ">Dashboard</a></li>
         <li><a href="#" class="block hover:bg-gray-700 hover:text-white p-2 ">Profile</a></li>
         <li><a href="#" class="block hover:bg-gray-700 hover:text-white p-2 ">Settings</a></li>
      </ul>
   </nav>
   <main class="w-5/6">

   </main>

</body>

</html>