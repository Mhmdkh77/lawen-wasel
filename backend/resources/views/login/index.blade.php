<x-html>

   <body class="flex w-screen h-screen">
      <div class="flex-4 flex justify-center items-center">
         <img src="{{ Vite::asset('resources/images/logo.png') }}" class="h-screen" alt="Logo">
      </div>
      <div class="flex-3 bg-black flex flex-col justify-center items-center text-white">
         <h4 class="text-2xl font-semibold mb-8">Login</h4>
         <form method="POST" action="{{ route("login.attempt") }}" class="space-y-12 w-1/2 p-5">
            @csrf
            <div>
               <input type="email" name="email" id="email" class="bg-white rounded-md w-full p-3 text-black"
                  placeholder="Email" value="{{ old('email') }}">
               @error("email")
               <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
            @enderror
            </div>
            <div>
               <input type="password" name="password" id="password" class="bg-white rounded-md w-full p-3 text-black"
                  placeholder="Password">
            </div>
            <div class="flex justify-end">
               <button
                  class="bg-yellow rounded-md py-2 px-4 hover:bg-yellow-400 text-black font-semibold">Login</button>
            </div>
         </form>
      </div>

   </body>

</x-html>