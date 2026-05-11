<div data-auth-page="vendor-login"
    class="flex min-h-screen relative overflow-hidden bg-slate-50 dark:bg-zinc-950 font-khmer selection:bg-emerald-100 selection:text-emerald-900">
    <!-- Ambient Mobile/Global Background -->
    <div class="absolute inset-0 z-0 pointer-events-none">
        <div
            class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-emerald-500/10 dark:bg-emerald-500/5 blur-[100px]">
        </div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-blue-500/5 blur-[100px]"></div>
    </div>

    <!-- Left Side: Branding & Image (Hidden on mobile, visible on lg) -->
    <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 relative z-10 overflow-hidden shadow-2xl">
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-[2000ms] ease-out hover:scale-110"
            style="background-image: url('https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80')">
        </div>

        <!-- Modern Gradient Overlay -->
        <div
            class="absolute inset-0 bg-gradient-to-b from-emerald-900/90 via-emerald-900/40 to-emerald-950/90 mix-blend-multiply">
        </div>
        <div class="absolute inset-0 bg-gradient-to-tr from-emerald-950/40 via-transparent to-transparent opacity-60">
        </div>

        <!-- Content on Image -->
        <div class="relative z-20 flex flex-col justify-between h-full p-12 xl:p-20 text-white w-full">
            <div class="flex items-center gap-3 animate-fade-in-down">
                <div
                    class="w-14 h-14 bg-white/10 backdrop-blur-xl rounded-2xl flex items-center justify-center border border-white/20 shadow-2xl">
                    <svg class="w-8 h-8 text-emerald-300" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11.722 19.028c0 .503-.408.911-.91.911-.503 0-.911-.408-.911-.91V11.23c0-.503.408-.911.91-.911.503 0 .911.408.911.91v7.798zM7.157 18.035c-.416.282-.975.172-1.257-.244-.282-.416-.172-.975.244-1.257l6.452-4.381c.416-.282.975-.172 1.257.244.282.416.172.975-.244 1.257l-6.452 4.381zM11.722 13.756c0 .503-.408.911-.91.911-.503 0-.911-.408-.911-.91v-2.526c0-.503.408-.911.91-.911.503 0 .911.408.911.91v2.526z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9zM12 21c-4.97 0-9-4.03-9-9s4.03-9 9-9 9 4.03 9 9-4.03 9-9 9z" />
                    </svg>
                </div>
                <span class="text-3xl font-bold tracking-tight text-white drop-shadow-sm">Fresh<span
                        class="text-emerald-400">Leaf Organics</span></span>
            </div>

            <div class="my-auto animate-fade-in-up">
                <div
                    class="inline-flex items-center gap-2 py-1.5 px-4 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-sm font-bold tracking-wide mb-8">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    VENDOR PORTAL
                </div>
                <h1 class="text-5xl xl:text-6xl font-extrabold leading-[1.1] mb-8 text-white tracking-tight">
                    Empowering <br />
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-300 to-green-500 drop-shadow-sm">Organic
                        Farmers</span>
                </h1>
                <p class="text-xl text-emerald-50/70 max-w-md leading-relaxed font-khmer font-medium">
                    គ្រប់គ្រងហាងរបស់អ្នក តាមដានសារពើភ័ណ្ឌ និងពង្រីកអាជីវកម្មសរីរាង្គរបស់អ្នកដោយងាយស្រួលបំផុត។
                </p>
            </div>

            <div class="flex items-center justify-between border-t border-white/10 pt-10 mt-auto opacity-60">
                <div class="text-sm font-medium tracking-wide">
                    &copy; {{ date('Y') }} FreshLeaf Organics.
                </div>
                <div class="flex gap-6 text-sm font-semibold">
                    <a href="#" class="hover:text-emerald-400 transition-colors">Privacy</a>
                    <a href="#" class="hover:text-emerald-400 transition-colors">Terms</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div
        class="flex-1 flex flex-col items-center justify-center p-6 sm:p-12 lg:p-16 xl:p-24 z-10 min-h-screen overflow-y-auto">
        <div class="w-full max-w-[440px] mx-auto">

            <!-- Branding for Mobile -->
            <div class="lg:hidden mb-12 text-center animate-fade-in-down">
                <div
                    class="inline-flex w-16 h-16 bg-white dark:bg-zinc-900 rounded-[1.25rem] items-center justify-center shadow-xl shadow-emerald-500/10 mb-5 border border-zinc-100 dark:border-zinc-800">
                    <svg class="w-9 h-9 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11.722 19.028c0 .503-.408.911-.91.911-.503 0-.911-.408-.911-.91V11.23c0-.503.408-.911.91-.911.503 0 .911.408.911.91v7.798zM7.157 18.035c-.416.282-.975.172-1.257-.244-.282-.416-.172-.975.244-1.257l6.452-4.381c.416-.282.975-.172 1.257.244.282.416.172.975-.244 1.257l-6.452 4.381zM11.722 13.756c0 .503-.408.911-.91.911-.503 0-.911-.408-.911-.91v-2.526c0-.503.408-.911.91-.911.503 0 .911.408.911.91v2.526z" />
                    </svg>
                </div>
                <h2 class="text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight">FreshLeaf Organics<span
                        class="text-emerald-600 dark:text-emerald-400">Vendor</span></h2>
            </div>

            <div class="mb-10 text-center lg:text-left animate-fade-in-up">
                <h2 class="text-center text-4xl font-extrabold text-zinc-900 dark:text-white tracking-tight mb-3">
                    {{ $this->getHeading() }}
                </h2>
                <p class="text-center text-zinc-500 dark:text-zinc-400 font-khmer text-lg leading-relaxed opacity-80">
                    {{ $this->getSubheading() }}
                </p>
            </div>

            <!-- Form Card with Glassmorphism -->
            <div class="bg-white/70 dark:bg-zinc-900/60 backdrop-blur-2xl p-8 sm:p-10 rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.05)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-white dark:border-white/5 relative overflow-hidden group animate-fade-in-up"
                style="animation-delay: 100ms;">

                <!-- Interaction Line -->
                <div
                    class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-emerald-400 via-emerald-600 to-green-400 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                </div>

                <form wire:submit="authenticate" class="space-y-8 relative z-10">
                    <div class="modern-form-wrapper">
                        {{ $this->form }}
                    </div>

                    <div class="pt-2">
                        <x-filament::button type="submit" size="lg"
                            class="w-full fi-btn-modern !py-4 !text-lg !font-bold !rounded-2xl shadow-xl shadow-emerald-500/20"
                            color="primary">
                            <span class="flex items-center justify-center gap-3">
                                {{ __('admin.auth.login.submit') }}
                                <svg class="w-5 h-5 transition-transform duration-300 group-hover/btn:translate-x-1"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </span>
                        </x-filament::button>
                    </div>
                </form>
            </div>

            <div
                class="mt-12 text-center text-sm font-medium text-zinc-400 dark:text-zinc-600 font-khmer opacity-60 lg:hidden">
                &copy; {{ date('Y') }} FreshLeaf Organics. <br> Powered by Organic Innovation.
            </div>
        </div>
    </div>
    </>

    <style>
        /* Premium UI Refinements */
        [data-auth-page="vendor-login"] .modern-form-wrapper .fi-fo-field-wrp {
            @apply transition-all duration-300;
        }

        /* Target input styling via global classes for Filament compatibility */
        [data-auth-page="vendor-login"] .fi-input-wrp {
            @apply !rounded-2xl !transition-all !duration-300 !border-zinc-200/60 dark: !border-white/10 !bg-zinc-50/50 dark: !bg-white/5 !shadow-none !ring-0 !outline-none !overflow-hidden;
        }

        [data-auth-page="vendor-login"] .fi-input-wrp:focus-within {
            @apply !border-emerald-500/50 !bg-white dark: !bg-zinc-900 !shadow-lg !shadow-emerald-500/5;
            transform: translateY(-1px);
        }

        [data-auth-page="vendor-login"] .fi-input-wrp input {
            @apply !py-4 !px-5 !text-base !font-medium;
        }

        /* Button "Modern" Style */
        .fi-btn-modern {
            @apply transition-all duration-500 relative overflow-hidden;
        }

        .fi-btn-modern::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.8s ease;
        }

        .fi-btn-modern:hover::before {
            transform: translateX(100%);
        }

        .fi-btn-modern:hover {
            @apply shadow-2xl shadow-emerald-500/40;
            transform: translateY(-2px) scale(1.01);
        }

        .fi-btn-modern:active {
            transform: translateY(1px);
        }

        /* Typography & Layout */
        .font-khmer {
            font-family: 'Noto Sans Khmer', sans-serif;
        }

        /* Smooth Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 1s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .animate-fade-in-down {
            animation: fadeInDown 1s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        /* Custom scrollbar for glass card */
        .min-h-screen::-webkit-scrollbar {
            width: 4px;
        }

        .min-h-screen::-webkit-scrollbar-thumb {
            @apply bg-emerald-500/20 rounded-full;
        }
    </style>
