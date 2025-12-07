@if (!Auth::check() || (Auth::check() && Auth::user()->role === 'jobber'))
    <header x-data="authModal()" x-init="init()"
            x-on:open-auth-modal.window="
                // Support string or object detail
                (() => {
                    const d = $event.detail;
                    const modal = (typeof d === 'string') ? d : (d?.modal || 'login');
                    if (typeof d === 'object' && d?.role) { registerRole = d.role }
                    openModal(modal);
                })()
            ">
        <div class="bg-transparent navbar">
            <div class="navbar-start"></div>
            <div class="hidden gap-20 px-6 navbar-center lg:flex rounded-3xl" id="navbar">
                <a href="/">
                    <div class="transition-transform duration-200 hover:scale-105">
                        <img src="{{ asset('image\web-image\logo.png') }}" alt="logo" class="w-full h-14">
                    </div>
                </a>
                <ul class="px-1 text-lg font-bold menu menu-horizontal ">
                    <li class="transition-transform duration-200 hover:scale-105"><a
                            href="{{ route('courses.catalog') }}">เรียนรู้ทักษะ</a></li>
                    <li class="transition-transform duration-200 hover:scale-105">
                        <a
                            href="{{ auth()->check() && auth()->user()->role === 'jobber' ? route('jobber.jobs.index') : route('jobs.index') }}">หางาน</a>
                    </li>
                    <li class="transition-transform duration-200 hover:scale-105">
                        <a
                            href="{{ auth()->check() && auth()->user()->role === 'jobber' ? route('jobber.companies.index') : route('companies.index') }}">ผู้ประกอบการ</a>
                    </li>
                    <li class="transition-transform duration-200 hover:scale-105"><a
                            href="https://esp.informatics.buu.ac.th/2025/" target="_blank">ติดต่อเรา</a></li>
                </ul>
                @guest
                    <a class="text-lg font-bold text-white bg-blue-600 rounded-xl btn "
                        @click="openModal('login')">เข้าสู่ระบบ</a>
                @endguest
                {{-- dropdown --}}
                <div class="dropdown dropdown-hover rounded-xl ">
                    <div tabindex="0" role="button"
                        class="m-1 bg-transparent border-gray-300 rounded-xl btn border-1">
                        <div class="flex items-center gap-3">

                            @auth
                                <div class="avatar placeholder">
                                    <div class="flex items-center justify-center w-10 rounded-ful text-neutral-content">
                                        <i class="fa-regular fa-user" style="color: #383839;"></i>
                                    </div>
                                </div>
                                <span class="font-medium text-base-content">
                                    {{ Auth::user()->profile?->up_name ?? 'ไม่มีโปรไฟล์' }}
                                </span>
                            @endauth
                        </div>
                    </div>
                    @auth
                        <ul tabindex="0" class="p-2 shadow-sm dropdown-content menu bg-base-100 rounded-box z-1 w-52">
                            <li>
                                <a href="{{ route('profile-jobber.edit') }}">โปรไฟล์</a>
                            </li>
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                class="p-2 ">
                                ออกจากระบบ
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </ul>
                    @endauth
                </div>
            </div>
            <div class="navbar-end"></div>
        </div>
        <div>
            <!-- Modal Overlay -->
            <div x-show="showModal" x-cloak x-transition.opacity.duration.300ms
                class="fixed inset-0 z-50 flex items-center justify-center">

                <!-- Modal Content -->
                <div x-transition.scale.origin.center.duration.300ms
                    class="w-full max-w-sm shadow-2xl card bg-base-100 shrink-0">

                    <!-- Close Button -->
                    <button @click="closeModal()"
                        class="absolute p-1 text-gray-400 transition-colors rounded-full top-4 right-4 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>

                    <!-- Modal Body -->
                    <div class="card-body">
                        <!-- Login Form -->
                        <template x-if="currentModal === 'login'">
                            <fieldset class="fieldset">
                                <h2 class="mb-2 text-2xl font-bold text-center text-base-content">
                                    เข้าสู่ระบบ
                                </h2>
                                <h3 class="mb-6 text-xl font-bold text-center text-base-content/60">
                                    เข้าสู่ระบบด้วยบัญชีของคุณ</h3>

                                <!-- Success message สำหรับ password reset -->
                                @if (session('status') === 'password-updated')
                                    <div
                                        class="p-4 mb-4 text-sm text-green-700 bg-green-100 border border-green-300 rounded-lg">
                                        รหัสผ่านของคุณได้รับการเปลี่ยนแปลงเรียบร้อยแล้ว!
                                        คุณสามารถเข้าสู่ระบบด้วยรหัสผ่านใหม่ได้ทันที
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="form_type" value="login">
                                    <div>
                                        <x-input-label for="email" :value="__('อีเมล')" />
                                        <x-text-input id="email" name="email" type="email"
                                            class="block w-full mt-1 rounded-lg border-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'login' && session('errors')->has('email') ? 'border-red-500' : '' }}"
                                            :value="old('email')" required autofocus placeholder="กรอกอีเมลของคุณ" />
                                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="password" :value="__('รหัสผ่าน')" />
                                        <x-text-input id="password" name="password" type="password"
                                            class="block w-full mt-1 rounded-lg border-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'login' && session('errors')->has('password') ? 'border-red-500' : '' }}"
                                            required placeholder="กรอกรหัสผ่านของคุณ" />
                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    </div>

                                    <div class="flex items-end justify-between">
                                        <button type="button" @click="switchModal('forgot')" class="link link-hover">
                                            ลืมรหัสผ่าน?
                                        </button>
                                    </div>

                                    <x-primary-button class="justify-center w-full py-3">
                                        {{ __('เข้าสู่ระบบ') }}
                                    </x-primary-button>
                                </form>

                                <div class="mt-6 text-center">
                                    <p class="text-sm text-gray-600">
                                        ยังไม่มีบัญชี?
                                        <a @click="switchModal('register')"
                                            class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline link link-hover">
                                            สร้างบัญชี
                                        </a>
                                    </p>
                                </div>
                            </fieldset>
                        </template>

                        <!-- Register Form -->
                        <template x-if="currentModal === 'register'">
                            <div>
                                <h2 class="mb-2 text-2xl font-bold text-center text-base-content"
                                    x-text="registerRole === 'jobber' ? 'สมัครบัญชีของคุณ' : (registerRole === 'provider' ? 'สมัครเป็นผู้ประกอบการ' : 'สมัครเป็นบุคลากรสถานศึกษา')">
                                </h2>
                                <h3 class="mb-6 text-xl font-bold text-center text-base-content/60">
                                </h3>

                                <!-- General Error Message for Register -->
                                @if ($errors->any() && request()->routeIs('register'))
                                    <div
                                        class="p-4 mb-4 text-sm text-red-700 bg-red-100 border border-red-300 rounded-lg">
                                        <ul class="list-disc list-inside">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="form_type" value="register">
                                    <input type="hidden" name="role" :value="registerRole">

                                    <div>
                                        <x-input-label for="name"
                                            x-text="registerRole === 'jobber' ? 'ชื่อ-นามสกุล' : (registerRole === 'provider' ? 'ชื่อบริษัท/องค์กรของคุณ' : 'ชื่อสถานศึกษาของคุณ')">
                                        </x-input-label>
                                        <x-text-input id="name" name="name" type="text"
                                            placeholder="ใส่ชื่อของคุณ"
                                            class="block w-full mt-1 rounded-lg border-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'register' && session('errors')->has('name') ? 'border-red-500' : '' }}"
                                            :value="old('name')" required />
                                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="email" :value="__('อีเมล')" />
                                        <x-text-input id="email" name="email" type="email"
                                            placeholder="กรอกอีเมลของคุณ"
                                            class="block w-full mt-1 rounded-lg border-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'register' && session('errors')->has('email') ? 'border-red-500' : '' }}"
                                            :value="old('email')" required />
                                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="password" :value="__('รหัสผ่าน')" />
                                        <x-text-input id="password" name="password" type="password"
                                            placeholder="กรอกรหัสผ่านของคุณ"
                                            class="block w-full mt-1 rounded-lg border-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'register' && session('errors')->has('password') ? 'border-red-500' : '' }}"
                                            required />
                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="password_confirmation" :value="__('ยืนยันรหัสผ่าน')" />
                                        <x-text-input id="password_confirmation" name="password_confirmation"
                                            type="password" placeholder="กรอกรหัสผ่านของคุณอีกครั้ง"
                                            class="block w-full mt-1 rounded-lg border-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'register' && session('errors')->has('password_confirmation') ? 'border-red-500' : '' }}"
                                            required />
                                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                    </div>

                                    <x-primary-button class="justify-center w-full py-3">
                                        {{ __('สร้างบัญชี') }}
                                    </x-primary-button>
                                </form>

                                <div class="mt-6 text-center">
                                    <p class="text-sm text-gray-600">
                                        มีบัญชีอยู่แล้ว?
                                        <button @click="switchModal('login')"
                                            class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline link link-hover">
                                            เข้าสู่ระบบ
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </template>

                        <!-- Forgot Password Form -->
                        <template x-if="currentModal === 'forgot'">
                            <div>
                                <h2 class="mb-2 text-2xl font-bold text-center text-base-content">
                                    กู้คืนรหัสผ่านของคุณ</h2>
                                <h3 class="mb-6 text-xl font-bold text-center text-base-content/60">
                                    กรอกอีเมลที่ลงทะเบียนไว้เพื่อรับการกู้คืนรหัสผ่าน</h3>
                                @if (session('status'))
                                    <div
                                        class="p-4 mb-4 text-sm text-green-700 bg-green-100 border border-green-300 rounded-lg">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                <!-- General Error Message for Forgot Password -->
                                @if ($errors->any() && request()->routeIs('password.email'))
                                    <div
                                        class="p-4 mb-4 text-sm text-red-700 bg-red-100 border border-red-300 rounded-lg">
                                        <ul class="list-disc list-inside">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="form_type" value="forgot">
                                    <div>
                                        <x-input-label for="email" :value="__('อีเมล')" />
                                        <x-text-input id="email" name="email" type="email"
                                            placeholder="กรอกอีเมลของคุณ"
                                            class="block w-full mt-1 rounded-lg border-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'password.email' && session('errors')->has('email') ? 'border-red-500' : '' }}"
                                            :value="old('email')" required />
                                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                    </div>

                                    <x-primary-button class="justify-center w-full py-3">
                                        {{ __('ส่งข้อมูลการกู้คืน') }}
                                    </x-primary-button>
                                </form>

                                <div class="mt-6 text-center">
                                    <p class="text-sm text-gray-600">
                                        จำรหัสผ่านของคุณใช่ไหม?
                                        <button @click="switchModal('login')"
                                            class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline link link-hover ">
                                            เข้าสู่ระบบ
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </template>

                        <!-- Email Verification -->
                        <template x-if="currentModal === 'verify'">
                            <div>
                                <h2 class="mb-6 text-2xl font-bold text-center text-indigo-700">
                                    ยืนยันอีเมลของคุณ
                                </h2>

                                @if (session('status') === 'verification-link-sent')
                                    <div
                                        class="p-4 mb-4 text-sm text-green-700 bg-green-100 border border-green-300 rounded-lg">
                                        ลิงก์สำหรับยืนยันอีเมลฉบับใหม่ถูกส่งไปยังอีเมลของคุณแล้ว
                                    </div>
                                @endif

                                <!-- General Error Message for Verification -->
                                @if ($errors->any() && request()->routeIs('verification.send'))
                                    <div
                                        class="p-4 mb-4 text-sm text-red-700 bg-red-100 border border-red-300 rounded-lg">
                                        <ul class="list-disc list-inside">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="text-center">
                                    <div class="mb-6">
                                        <svg class="w-16 h-16 mx-auto text-indigo-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <p class="mb-6 text-gray-600">
                                        กรุณาตรวจสอบอีเมลของคุณเพื่อคลิกลิงก์ยืนยัน
                                        หากคุณยังไม่ได้รับอีเมล เราสามารถส่งให้คุณอีกครั้งได้
                                    </p>

                                    <form method="POST" action="{{ route('verification.send') }}"
                                        class="space-y-4">
                                        @csrf
                                        <input type="hidden" name="form_type" value="verify">
                                        <x-primary-button class="justify-center w-full py-3" placeholder="Email">
                                            ส่งอีเมลยืนยันอีกครั้ง
                                        </x-primary-button>
                                    </form>

                                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                                        @csrf
                                        <button type="submit"
                                            class="text-sm text-gray-500 hover:text-gray-700 focus:outline-none focus:underline">
                                            ออกจากระบบ
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </template>

                        <!-- Reset Password Form -->
                        <template x-if="currentModal === 'reset'">
                            <div>
                                <h2 class="mb-2 text-2xl font-bold text-center text-base-content">
                                    รีเซ็ตรหัสผ่านใหม่</h2>
                                <h3 class="mb-6 text-xl font-bold text-center text-base-content/60">
                                    สร้างรหัสผ่านใหม่สำหรับบัญชีของคุณ</h3>

                                <!-- General Error Message for Reset Password -->
                                @if ($errors->any() && request()->routeIs('password.store'))
                                    <div
                                        class="p-4 mb-4 text-sm text-red-700 bg-red-100 border border-red-300 rounded-lg">
                                        <ul class="list-disc list-inside">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="form_type" value="reset">

                                    <!-- ใช้ token จาก URL parameter หรือ session -->
                                    @if (request('token'))
                                        <input type="hidden" name="token" value="{{ request('token') }}" />
                                    @else
                                        <input type="hidden" name="token"
                                            value="{{ session('passwordResetToken') }}" />
                                    @endif

                                    <div>
                                        <x-input-label for="email" :value="__('อีเมล')" />
                                        <x-text-input id="email" name="email" type="email"
                                            placeholder="Email"
                                            class="block w-full mt-1 rounded-lg border-2 bg-gray-50 border-gray-300 {{ session('errors') && old('form_type') === 'password.store' && session('errors')->has('email') ? 'border-red-500' : '' }}"
                                            :value="request('email') ?: session('passwordResetEmail')" readonly />
                                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="password" :value="__('รหัสผ่านใหม่')" />
                                        <x-text-input id="password" name="password" type="password"
                                            placeholder="กรอกรหัสผ่านใหม่ของคุณ"
                                            class="block w-full mt-1 rounded-lg border-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'password.store' && session('errors')->has('password') ? 'border-red-500' : '' }}"
                                            required autofocus />
                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="password_confirmation" :value="__('ยืนยันรหัสผ่านใหม่')" />
                                        <x-text-input id="password_confirmation" name="password_confirmation"
                                            type="password" placeholder="กรอกรหัสผ่านของคุณอีกครั้ง"
                                            class="block w-full mt-1 rounded-lg border-2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'password.store' && session('errors')->has('password_confirmation') ? 'border-red-500' : '' }}"
                                            required />
                                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                    </div>

                                    <x-primary-button class="justify-center w-full py-3">
                                        {{ __('รีเซ็ตรหัสผ่าน') }}
                                    </x-primary-button>
                                </form>

                                <div class="mt-6 text-center">
                                    <p class="text-sm text-gray-600">
                                        กลับไปหน้า
                                        <button @click="switchModal('login')"
                                            class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline ">
                                            เข้าสู่ระบบ
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

            <script>
                function authModal() {
                    return {
                        showModal: false,
                        currentModal: 'login',
                        registerRole: 'jobber', // default

                        openRegister(role) {
                            this.registerRole = role;
                            this.currentModal = 'register';
                            this.showModal = true;
                            document.body.style.overflow = 'hidden';
                        },
                        init() {
                            console.log('authModal initialized');

                            // ตรวจสอบ validation errors และกำหนด modal type
                            let hasErrors = @json($errors->any());
                            let routeName = @json(request()->route() ? request()->route()->getName() : null);
                            let formType = @json(old('form_type', null));

                            console.log('Has errors:', hasErrors);
                            console.log('Route name:', routeName);
                            console.log('Form type:', formType);

                            if (hasErrors) {
                                this.showModal = true;

                                // ใช้ form_type จาก old input ก่อน แล้วค่อย fallback ไป route name
                                if (formType) {
                                    switch (formType) {
                                        case 'login':
                                            this.currentModal = 'login';
                                            break;
                                        case 'register':
                                            this.currentModal = 'register';
                                            break;
                                        case 'forgot':
                                            this.currentModal = 'forgot';
                                            break;
                                        case 'reset':
                                            this.currentModal = 'reset';
                                            break;
                                        case 'verify':
                                            this.currentModal = 'verify';
                                            break;
                                        default:
                                            this.currentModal = 'login';
                                    }
                                } else {
                                    // Fallback ไป route name
                                    switch (routeName) {
                                        case 'login':
                                            this.currentModal = 'login';
                                            break;
                                        case 'register':
                                            this.currentModal = 'register';
                                            break;
                                        case 'password.email':
                                            this.currentModal = 'forgot';
                                            break;
                                        case 'password.reset':
                                            this.currentModal = 'reset';
                                            break;
                                        case 'verification.send':
                                            this.currentModal = 'verify';
                                            break;
                                        default:
                                            this.currentModal = 'login';
                                    }
                                }

                                console.log('Opening modal due to errors:', this.currentModal);
                                document.body.style.overflow = 'hidden';
                            } else {
                                // ใช้ค่าจาก session หากไม่มี errors
                                this.showModal = @json(session('showAuthModal', false));
                                this.currentModal = @json(session('authForm', 'login'));
                            }

                            // ตรวจสอบ URL parameters สำหรับ reset password
                            const urlParams = new URLSearchParams(window.location.search);
                            const token = urlParams.get('token');

                            if (window.location.pathname.includes('/reset-password/') && token) {
                                console.log('Reset password URL detected with token');
                                this.openModal('reset');
                            }
                            if (routeName === 'password.reset') {
                                this.showModal = true;
                                this.currentModal = 'reset';
                                document.body.style.overflow = 'hidden';
                                return;
                            }
                            console.log('Final modal state - showModal:', this.showModal, 'currentModal:', this.currentModal);
                        },

                        openModal(modalType) {
                            console.log('Opening modal:', modalType);
                            this.currentModal = modalType;
                            this.showModal = true;
                            document.body.style.overflow = 'hidden';
                        },

                        closeModal() {
                            console.log('Closing modal');
                            this.showModal = false;
                            document.body.style.overflow = 'auto';

                            // ลบ parameters จาก URL
                            if (window.location.pathname.includes('/reset-password/')) {
                                window.history.replaceState({}, '', '/');
                            }
                        },

                        switchModal(modalType) {
                            console.log('Switching to modal:', modalType);
                            this.currentModal = modalType;
                        }
                    }
                }
            </script>
    </header>
@else
    <header class="flex items-center justify-between p-4 mx-6 border-r border-gray-400 shadow bg-base-200 rounded-2xl">
        <!-- ซ้าย: แสดงชื่อหน้า -->
        <div class="text-4xl font-bold text-base-content">
            @yield('title', 'หน้าหลัก')
        </div>

        <!-- ขวา: Avatar + ชื่อผู้ใช้งาน -->
        <div class="dropdown dropdown-hover">
            <div tabindex="0" role="button" class="m-1 btn">
                <div class="flex items-center gap-3 border-gray-300 border-1">
                    @auth
                        <div class="avatar placeholder">
                            <div class="flex items-center justify-center w-10 rounded-ful text-neutral-content">
                                <i class="fa-regular fa-user" style="color: #383839;"></i>
                            </div>
                        </div>
                        @php
                            $user = Auth::user();
                            $displayName = match ($user->role) {
                                'jobber', 'admin' => $user->profile?->up_name ?? 'ไม่มีโปรไฟล์',
                                'provider' => $user->companyProfile?->co_name ?? 'ไม่มีโปรไฟล์บริษัท',
                                'education' => $user->educationProfile?->e_name ?? 'ไม่มีโปรไฟล์สถานศึกษา',
                                default => $user->name ?? 'ไม่มีโปรไฟล์',
                            };
                        @endphp

                        <span class="font-medium text-base-content">
                            {{ $displayName }}
                        </span>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-lg font-bold text-white bg-blue-600 rounded-xl btn">เข้าสู่ระบบ</a>
                    @endauth
                </div>
            </div>
            <ul tabindex="0" class="p-2 shadow-sm dropdown-content menu bg-base-100 rounded-box z-1 w-52">
                <li>
                    @php
                        $isAdmin = Auth::user()->role === 'admin';

                        $profileRoute = match (Auth::user()->role) {
                            'provider' => route('provider.profile.edit'),
                            'education' => route('profile-education.edit.self'),
                            default => route('profile-jobber.edit'),
                        };
                    @endphp

                    @if ($isAdmin)
                        <span>ESP BUU</span>
                    @else
                        <a href="{{ $profileRoute }}">โปรไฟล์</a>
                    @endif
                </li>
            </ul>
        </div>
    </header>
@endif
