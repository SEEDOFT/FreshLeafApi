<div data-auth-page="auth-portal" class="fl-auth-container">

    <!-- Full-screen Background Image with Linear Fade -->
    <div class="fl-auth-bg-wrapper">
        <!-- Background Image -->
        <div class="fl-auth-bg-img" style="background-image: url('https://images.unsplash.com/photo-1500937386664-56d1dfef3854?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80')"></div>

        <!-- Darker tint to make white text pop on the left -->
        <div class="fl-auth-bg-tint"></div>

        <!-- Light Mode Fade -->
        <div class="fl-auth-bg-fade-light"></div>

        <!-- Dark Mode Fade -->
        <div class="fl-auth-bg-fade-dark"></div>
    </div>

    <!-- Left Side: Branding & Info (Hidden on mobile, visible on lg) -->
    <div class="fl-auth-content-left xl:w-4/12">
        <div class="fl-auth-logo-wrapper">
            <div class="fl-auth-logo-box">
                <img src="{{ Storage::url('images/fresh_leaf.png') }}" class="w-16 h-16 object-contain drop-shadow-sm" alt="FreshLeaf Logo" />
            </div>
            <span class="fl-auth-logo-text">Fresh<span class="fl-auth-logo-highlight">Leaf Organics</span></span>
        </div>

        <div class="fl-auth-hero-section">
            <div class="fl-auth-badge">
                <span class="fl-auth-badge-dot"></span>
                VENDOR REGISTRATION
            </div>
            <h1 class="fl-auth-title">
                Start Your <br />
                <span class="fl-auth-title-highlight">Sustainable Journey</span>
            </h1>
            <p class="fl-auth-subtitle">
                ចូលរួមជាមួយបណ្តាញកសិករសរីរាង្គដ៏ធំបំផុត និងចាប់ផ្តើមលក់ផលិតផលរបស់អ្នកទៅកាន់អតិថិជនរាប់ពាន់នាក់។
            </p>
        </div>

        <div class="fl-auth-footer-left">
            <div class="fl-auth-copyright">
                &copy; {{ date('Y') }} FreshLeaf Organics.
            </div>
        </div>
    </div>

    <!-- Right Side: Registration Wizard -->
    <div class="fl-auth-content-right p-6 sm:p-10 lg:p-12 xl:p-12 relative">
        <!-- Language Switcher -->
        <div class="absolute top-4 right-4 flex gap-2 z-50">
            <button type="button" wire:click="switchLanguage('en')" class="px-3 py-1 rounded-full text-xs font-bold transition-all {{ app()->getLocale() === 'en' ? 'bg-primary-500 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-700' }}">EN</button>
            <button type="button" wire:click="switchLanguage('km')" class="px-3 py-1 rounded-full text-xs font-bold transition-all {{ app()->getLocale() === 'km' ? 'bg-primary-500 text-white shadow-md' : 'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-700' }}">KM</button>
        </div>

        <div class="fl-auth-form-wrapper !max-w-[1100px]">

            <!-- Branding for Mobile -->
            <div class="fl-auth-mobile-header">
                <div class="fl-auth-mobile-logo">
                    <img src="{{ Storage::url('images/fresh_leaf.png') }}" class="w-20 h-20 object-contain drop-shadow-sm" alt="FreshLeaf Logo" />
                </div>
                <h2 class="fl-auth-mobile-title">FreshLeaf <span class="fl-auth-mobile-highlight">Vendor</span></h2>
            </div>

            <div class="fl-auth-header">
                <h2 class="fl-auth-heading text-4xl">
                    {{ $this->getHeading() }}
                </h2>
                <p class="fl-auth-subheading">
                    {{ $this->getSubheading() }}
                </p>
            </div>

            <!-- Form Card with Glassmorphism -->
            <div class="fl-auth-card group p-6 sm:p-10" style="animation-delay: 100ms;">

                <!-- Interaction Line -->
                <div class="fl-auth-card-interaction"></div>

                <form wire:submit.prevent="" class="fl-auth-form-body">
                    <div class="modern-form-wrapper">
                        {{ $this->form }}
                    </div>
                </form>
            </div>

            <div class="fl-auth-mobile-footer">
                &copy; {{ date('Y') }} FreshLeaf Organics. <br> Powered by Organic Innovation.
            </div>
        </div>
    </div>

</div>
