<x-html title="Admin login | Lawen Wasel">
   <body class="min-h-screen bg-white font-sans text-ink-900 antialiased">
      <main class="min-h-screen">
         <div class="grid min-h-screen w-full bg-white lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
            <section class="relative hidden items-center justify-center overflow-hidden bg-gradient-to-br from-[#fff9e9] via-[#fff5d7] to-[#f3ecd5] p-10 lg:flex xl:p-14" aria-label="Lawen Wasel">
               <div class="pointer-events-none absolute -right-36 -top-40 h-[34rem] w-[34rem] rounded-full border-[5rem] border-white/35" aria-hidden="true"></div>
               <div class="pointer-events-none absolute -bottom-52 -left-44 h-[36rem] w-[36rem] rounded-full border-[5rem] border-white/30" aria-hidden="true"></div>

               <img src="{{ Vite::asset('resources/images/logo.png') }}" class="relative h-48 w-full max-w-[600px] object-cover object-center" alt="Lawen Wasel">
            </section>

            <section class="flex min-h-screen items-center px-6 py-12 sm:px-12 lg:px-14 xl:px-20" aria-labelledby="login-heading">
               <div class="mx-auto w-full max-w-[430px]">
                  <div class="mb-12 lg:hidden">
                     <img src="{{ Vite::asset('resources/images/logo.png') }}" class="h-24 w-72 max-w-full object-cover object-center" alt="Lawen Wasel">
                  </div>

                  <h1 id="login-heading" class="text-3xl font-semibold tracking-tight text-ink-900">Admin login</h1>

                  <form method="POST" action="{{ route('admin.login.attempt') }}" class="mt-8 space-y-6">
                     @csrf
                     <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-ink-900">Email address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" autocomplete="username" required autofocus
                           aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                           @error('email') aria-describedby="email-error" @enderror
                           class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-sm text-ink-900 shadow-sm outline-none transition placeholder:text-gray-400 hover:border-gray-300 focus:border-brand-600 focus:ring-4 focus:ring-brand-100"
                           placeholder="name@example.com">
                        @error('email')
                           <p id="email-error" class="mt-2 text-sm font-medium text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                     </div>

                     <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-ink-900">Password</label>
                        <input type="password" name="password" id="password" autocomplete="current-password" required
                           aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                           @error('password') aria-describedby="password-error" @enderror
                           class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-sm text-ink-900 shadow-sm outline-none transition placeholder:text-gray-400 hover:border-gray-300 focus:border-brand-600 focus:ring-4 focus:ring-brand-100"
                           placeholder="Enter your password">
                        @error('password')
                           <p id="password-error" class="mt-2 text-sm font-medium text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                     </div>

                     <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-brand-500 px-5 py-3.5 text-sm font-semibold text-ink-900 shadow-[0_8px_24px_rgba(224,173,20,0.2)] transition hover:bg-brand-400 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-100 active:translate-y-px">
                        Sign in
                     </button>
                  </form>
               </div>
            </section>
         </div>
      </main>
   </body>
</x-html>
