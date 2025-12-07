<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>JobNexis</title>
    <link rel="icon" type="image/png" href="{{ asset('image/web-image/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('image/web-image/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/2412bed399.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: 'Kanit', sans-serif;
        }
    </style>
</head>

<body class="font-sans">
    <div x-data="authModal()" x-init="init()">
        <button id="backToTopBtn"
            class="fixed transition-transform duration-200 bg-gray-200 shadow-lg btn btn-circle right-20 bottom-20 z-49 hover:bg-gray-300 hover:scale-105 btn-xl"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })">
            <i class="fa-solid fa-up-long"></i>
        </button>

        <div class="fixed top-0 left-0 z-50 bg-transparent navbar">
            <div class="navbar-start"></div>
            <div class="hidden gap-20 px-6 navbar-center lg:flex rounded-3xl" id="navbar">
                <a href="/">
                    <div class="transition-transform duration-200 hover:scale-105">
                        <img src="{{ asset('image\web-image\logo.png') }}" alt="logo" class="w-full h-14">
                    </div>
                </a>
                <ul class="px-1 text-lg font-bold menu menu-horizontal ">
                    @auth
                        <li class="transition-transform duration-200 hover:scale-105">
                            <a href="{{ route('courses.catalog') }}">เรียนรู้ทักษะ</a>
                        </li>
                        <li class="transition-transform duration-200 hover:scale-105">
                            <a href="{{ auth()->user()->role === 'jobber' ? route('jobber.jobs.index') : route('jobs.index') }}">หางาน</a>
                        </li>
                        <li class="transition-transform duration-200 hover:scale-105">
                            <a href="{{ auth()->user()->role === 'jobber' ? route('jobber.companies.index') : route('companies.index') }}">ผู้ประกอบการ</a>
                        </li>
                        <li class="transition-transform duration-200 hover:scale-105"><a
                                href="https://esp.informatics.buu.ac.th/2025/" target="_blank">ติดต่อเรา</a></li>
                    @else
                        {{-- <a href="javascript:void(0)" @click="openModal('login')">เรียนรู้ทักษะ</a> --}}
                         <li class="transition-transform duration-200 hover:scale-105"><a href="{{ route('courses.catalog') }}">เรียนรู้ทักษะ</a></li>
                    <li class="transition-transform duration-200 hover:scale-105">
                        <a href="{{ auth()->check() && auth()->user()->role === 'jobber' ? route('jobber.jobs.index') : route('jobs.index') }}">หางาน</a>
                    </li>
                    <li class="transition-transform duration-200 hover:scale-105">
                        <a href="{{ auth()->check() && auth()->user()->role === 'jobber' ? route('jobber.companies.index') : route('companies.index') }}">ผู้ประกอบการ</a>
                    </li>
                    <li class="transition-transform duration-200 hover:scale-105"><a href="https://esp.informatics.buu.ac.th/2025/" target="_blank">ติดต่อเรา</a></li>
                    @endauth
                </ul>
                @guest <a href="javascript:void(0);" class="text-lg font-bold bg-blue-600 rounded-xl btn text-base-100"
                        @click="openModal('login')">เข้าสู่ระบบ</a>
                @endguest
                @auth
                    <div class="dropdown dropdown-hover rounded-xl">
                        <div tabindex="0" role="button"
                            class="m-1 bg-transparent border-gray-300 rounded-xl btn border-1">
                            <div class="flex items-center w-full gap-3">

                                <div class="avatar placeholder">
                                    <div class="flex items-end justify-end w-10 h-10 text-neutral-content">
                                        <i class="mt-3 fa-regular fa-user" style="color: #383839;"></i>
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

                                @if (!$isAdmin)
                                    <a href="{{ $profileRoute }}">โปรไฟล์</a>
                                @endif
                            </li>
                            <li>
                                @php
                                    $isAdmin = Auth::user()->role === 'admin';
                                    $isJobber = Auth::user()->role === 'jobber';

                                    $profileRoute = match (Auth::user()->role) {
                                        'admin' => route('admin.userStats'),
                                        'provider' => route('provider.dashboard'),
                                        'education' => route('education.dashboard'),
                                        'jobber' => route('jobber.dashboard'),
                                        default => route('dashboard'),
                                    };
                                @endphp

                                @if (!$isJobber)
                                    <a href="{{ $profileRoute }}">แดชบอร์ด</a>
                                @endif
                            </li>
                            <li>
                                <a href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                    class="p-2 ">
                                    ออกจากระบบ
                                </a>
                            </li>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </ul>
                    </div>
                @endauth
            </div>
            <div class="navbar-end"></div>
        </div>
        <div class=" text-base-content">
            <div
                class="bg-[linear-gradient(to_bottom,_theme('colors.sky.100')_20%,_theme('colors.blue.300')_100%)] p-28 pb-0 flex flex-row justify-between items-center gap-4 text-base-content">
                <div>
                    <div class="flex flex-col items-start justify-start text-base-content gap-14">
                        <h1 class="text-6xl font-bold">
                            "หางานง่าย<br>
                            เรียนรู้ทักษะรอบด้าน<br>
                            เข้าถึงคนที่ใช่<br>
                            ตามที่คุณต้องการ"</h1>
                        <div>
                            <!-- ถ้ายังไม่ login -->
                            @guest
                                <h4 class="text-xl">สมัครสมาชิก เพื่อเริ่มต้นใช้งานระบบของเราได้ทันที</h4>
                                <div class="flex flex-col items-start gap-4 mt-6 ">
                                    <button @click="openRegister('jobber')"
                                        class="transition-transform duration-200 bg-blue-600 btn btn-lg text-base-100 hover:scale-105">หางาน/เรียนรู้ทักษะ
                                        <i class="fa-solid fa-circle-arrow-right"></i></button>
                                    <button @click="openRegister('provider')"
                                        class="text-blue-600 transition-transform duration-200 bg-transparent border-blue-600 btn border-1 hover:bg-blue-600 hover:text-base-100 hover:scale-105">ผู้ประกอบการ
                                        <i class="fa-solid fa-building"></i></button>
                                    <button @click="openRegister('education')"
                                        class="text-blue-600 transition-transform duration-200 bg-transparent border-blue-600 btn border-1 hover:bg-blue-600 hover:text-base-100 hover:scale-105">สถานศึกษา
                                        <i class="fa-solid fa-school"></i></button>
                                </div>
                            @endguest




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

                                                <form method="POST" action="{{ route('login') }}"
                                                    class="space-y-4">
                                                    @csrf
                                                    <input type="hidden" name="form_type" value="login">
                                                    <div>
                                                        <x-input-label for="email" :value="__('อีเมล')" />
                                                        <x-text-input id="email" name="email" type="email"
                                                            class="block w-full mt-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'login' && session('errors')->has('email') ? 'border-red-500' : '' }}"
                                                            :value="old('email')" required autofocus
                                                            placeholder="กรอกอีเมลของคุณ" />
                                                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                                    </div>

                                                    <div>
                                                        <x-input-label for="password" :value="__('รหัสผ่าน')" />
                                                        <x-text-input id="password" name="password" type="password"
                                                            class="block w-full mt-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'login' && session('errors')->has('password') ? 'border-red-500' : '' }}"
                                                            required placeholder="กรอกรหัสผ่านของคุณ" />
                                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                                    </div>

                                                    <div class="flex items-end justify-between">
                                                        <button type="button" @click="switchModal('forgot')"
                                                            class="link link-hover">
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

                                                <form method="POST" action="{{ route('register') }}"
                                                    class="space-y-4">
                                                    @csrf
                                                    <input type="hidden" name="form_type" value="register">
                                                    <input type="hidden" name="role" :value="registerRole">

                                                    <div>
                                                        <x-input-label for="name"
                                                            x-text="registerRole === 'jobber' ? 'ชื่อ-นามสกุล' : (registerRole === 'provider' ? 'ชื่อบริษัท/องค์กรของคุณ' : 'ชื่อสถานศึกษาของคุณ')">
                                                        </x-input-label>
                                                        <x-text-input id="name" name="name" type="text"
                                                            placeholder="ใส่ชื่อของคุณ"
                                                            class="block w-full mt-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'register' && session('errors')->has('name') ? 'border-red-500' : '' }}"
                                                            :value="old('name')" required />
                                                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                                    </div>

                                                    <div>
                                                        <x-input-label for="email" :value="__('อีเมล')" />
                                                        <x-text-input id="email" name="email" type="email"
                                                            placeholder="กรอกอีเมลของคุณ"
                                                            class="block w-full mt-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'register' && session('errors')->has('email') ? 'border-red-500' : '' }}"
                                                            :value="old('email')" required />
                                                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                                    </div>

                                                    <div>
                                                        <x-input-label for="password" :value="__('รหัสผ่าน')" />
                                                        <x-text-input id="password" name="password" type="password"
                                                            placeholder="กรอกรหัสผ่านของคุณ"
                                                            class="block w-full mt-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'register' && session('errors')->has('password') ? 'border-red-500' : '' }}"
                                                            required />
                                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                                    </div>

                                                    <div>
                                                        <x-input-label for="password_confirmation"
                                                            :value="__('ยืนยันรหัสผ่าน')" />
                                                        <x-text-input id="password_confirmation"
                                                            name="password_confirmation" type="password"
                                                            placeholder="กรอกรหัสผ่านของคุณอีกครั้ง"
                                                            class="block w-full mt-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'register' && session('errors')->has('password_confirmation') ? 'border-red-500' : '' }}"
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

                                                <form method="POST" action="{{ route('password.email') }}"
                                                    class="space-y-4">
                                                    @csrf
                                                    <input type="hidden" name="form_type" value="forgot">
                                                    <div>
                                                        <x-input-label for="email" :value="__('อีเมล')" />
                                                        <x-text-input id="email" name="email" type="email"
                                                            placeholder="กรอกอีเมลของคุณ"
                                                            class="block w-full mt-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'password.email' && session('errors')->has('email') ? 'border-red-500' : '' }}"
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
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
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
                                                        <x-primary-button class="justify-center w-full py-3"
                                                            placeholder="Email">
                                                            ส่งอีเมลยืนยันอีกครั้ง
                                                        </x-primary-button>
                                                    </form>

                                                    <form method="POST" action="{{ route('logout') }}"
                                                        class="mt-4">
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

                                                <form method="POST" action="{{ route('password.store') }}"
                                                    class="space-y-4">
                                                    @csrf
                                                    <input type="hidden" name="form_type" value="reset">

                                                    <!-- ใช้ token จาก URL parameter หรือ session -->
                                                    @if (request('token'))
                                                        <input type="hidden" name="token"
                                                            value="{{ request('token') }}" />
                                                    @else
                                                        <input type="hidden" name="token"
                                                            value="{{ session('passwordResetToken') }}" />
                                                    @endif

                                                    <div>
                                                        <x-input-label for="email" :value="__('อีเมล')" />
                                                        <x-text-input id="email" name="email" type="email"
                                                            placeholder="Email"
                                                            class="block w-full mt-1 rounded-lg bg-gray-50 border-gray-300 {{ session('errors') && old('form_type') === 'password.store' && session('errors')->has('email') ? 'border-red-500' : '' }}"
                                                            :value="request('email') ?:
                                                                session('passwordResetEmail')" readonly />
                                                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                                    </div>

                                                    <div>
                                                        <x-input-label for="password" :value="__('รหัสผ่านใหม่')" />
                                                        <x-text-input id="password" name="password" type="password"
                                                            placeholder="กรอกรหัสผ่านใหม่ของคุณ"
                                                            class="block w-full mt-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'password.store' && session('errors')->has('password') ? 'border-red-500' : '' }}"
                                                            required autofocus />
                                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                                    </div>

                                                    <div>
                                                        <x-input-label for="password_confirmation"
                                                            :value="__('ยืนยันรหัสผ่านใหม่')" />
                                                        <x-text-input id="password_confirmation"
                                                            name="password_confirmation" type="password"
                                                            placeholder="กรอกรหัสผ่านของคุณอีกครั้ง"
                                                            class="block w-full mt-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 {{ session('errors') && old('form_type') === 'password.store' && session('errors')->has('password_confirmation') ? 'border-red-500' : '' }}"
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
                        </div>
                    </div>
                </div>
                <div>
                    <img src="{{ asset('image\web-image\smiling-woman.png') }}" alt="women smiling"
                        class="object-cover m-0 p-0 block w-full h-[600px]">
                </div>
            </div>
        {{-- content --}}
        </div>
        <div>
        <div class="flex flex-col gap-10 py-20 px-28 ">
            <div class="flex flex-col items-start justify-start gap-4 ">
                <h2 class="text-4xl font-bold ">พร้อมที่จะจินตนาการอาชีพของคุณใหม่หรือยัง?</h2>
                <p class="text-xl ">รับทักษะและประสบการณ์ในโลกแห่งความเป็นจริงที่นายจ้างต้องการด้วย Career Accelerators
                </p>
            </div>
            <div class="flex flex-row items-center justify-between">
                <div class="card card-sm bg-base-100 w-96 shadow-xl/30">
                    <figure class="px-5 pt-5">
                        <img src="{{ asset('image\web-image\ai-robot.jpg') }}"
                            class="object-cover w-full h-40 rounded-xl" alt="ai-web" />
                    </figure>
                    <div class="card-body">
                        <h2 class="card-title">
                            AI Developer
                        </h2>
                        <div class="justify-start card-actions">
                            <div class="badge badge-outline"><i class="fa-solid fa-star"
                                    style="color: #ffc800;"></i>4.6
                            </div>
                            <div class="badge badge-outline">450K rating</div>
                        </div>
                    </div>
                </div>
                <div class="card card-sm bg-base-100 w-96 shadow-xl/30">
                    <figure class="px-5 pt-5">
                        <img src="{{ asset('image\web-image\working-html.jpg') }}"
                            class="object-cover w-full h-40 rounded-xl" alt="working-html" />
                    </figure>
                    <div class="card-body">
                        <h2 class="card-title">
                            Full Stack Developer
                        </h2>
                        <div class="justify-start card-actions">
                            <div class="badge badge-outline"><i class="fa-solid fa-star"
                                    style="color: #ffc800;"></i>4.7
                            </div>
                            <div class="badge badge-outline">300K rating</div>
                        </div>
                    </div>
                </div>
                <div class="card card-sm bg-base-100 w-96 shadow-xl/30">
                    <figure class="px-5 pt-5">
                        <img src="{{ asset('image\web-image\work-group.jpg') }}"
                            class="object-cover w-full h-40 rounded-xl" alt="work-group" />
                    </figure>
                    <div class="card-body">
                        <h2 class="card-title">
                            Project Management
                        </h2>
                        <div class="justify-start card-actions">
                            <div class="badge badge-outline"><i class="fa-solid fa-star"
                                    style="color: #ffc800;"></i>4.4
                            </div>
                            <div class="badge badge-outline">250K rating</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-between py-10 bg-indigo-100 px-28 ">
            <div class="relative w-[45%] h-[300px]">

                <!-- รูป 1 (บนซ้าย) -->
                <img src="{{ asset('image\web-image\formal-wear.jpg') }}"
                    class="absolute top-0 object-cover w-64 h-48 shadow-md left-20 rounded-xl" alt="Formal wear">

                <!-- รูป 2 (ขวาบน) -->
                <img src="{{ asset('image\web-image\group-asia.jpg') }}"
                    class="absolute object-cover w-40 shadow-md top-25 right-25 h-36 rounded-xl" alt="Group asia">

                <!-- รูป 3 (ล่างซ้าย) -->
                <img src="{{ asset('image\web-image\handshake-up.jpg') }}"
                    class="absolute bottom-0 object-cover h-24 shadow-md left-45 w-36 rounded-xl" alt="Handshake">

            </div>
            <div class="w-[55%] flex flex-col items-start justify-center gap-2">
                <h2 class="text-4xl font-bold ">เริ่มต้นเส้นทางที่นำไปสู่โอกาสงานที่ดีกว่า</h2>
                <p class="text-base ">เข้าร่วมกับผู้คนหลายพันที่เปลี่ยนชีวิตด้วย <b>ทักษะใหม่ สร้างโอกาส งานที่ดีกว่า
                        เพิ่มรายได้ และก้าวสู่อาชีพในฝัน</b></p>
                <button class="mt-3 bg-blue-600 btn text-base-100 " @click="openRegister('jobber')">
                    เข้าร่วมกับเรา
                    <i class="fa-solid fa-circle-arrow-right"></i>
                </button>
            </div>
        </div>
        <div class="py-10 px-44">
            <div class="flex flex-col items-center justify-center gap-4 mb-8">
                <h1 class="text-6xl font-bold ">ทำไมต้อง JobNexis</h1>
                <p class="text-base ">เราให้มากกว่าการหางาน เราช่วยให้คุณเติบโตและพัฒนาตัวเองไปพร้อมกับอาชีพที่คุณรัก
                </p>
            </div>
            <div class="flex flex-row items-center justify-evenly">
                <div class="shadow-sm card bg-base-100 w-72 rounded-3xl">
                    <figure class="px-10 pt-10">
                        <div class="flex items-center justify-center w-20 h-20 bg-blue-600 rounded-full">
                            <i class="fa-solid fa-briefcase" style="color: #ffffff;"></i>
                        </div>
                    </figure>
                    <div class="items-center text-center card-body">
                        <h2 class="card-title">งานคุณภาพ</h2>
                        <p>
                            ค้นหางานจากบริษัทชั้นนำ<br>ที่ผ่านการคัดกรองมาแล้วอย่างดี<br>เพื่อให้คุณได้โอกาสที่ดีที่สุด
                        </p>
                    </div>
                </div>
                <div class="shadow-sm card bg-base-100 w-72 rounded-3xl">
                    <figure class="px-10 pt-10">
                        <div class="flex items-center justify-center w-20 h-20 bg-blue-600 rounded-full">
                            <i class="fa-solid fa-book-open" style="color: #ffffff;"></i>
                        </div>
                    </figure>
                    <div class="items-center text-center card-body">
                        <h2 class="card-title">ฝึกสกิลออนไลน์</h2>
                        <p>
                            เรียนรู้ทักษะใหม่ๆ ที่ตลาดต้องการ<br>ผ่านคอร์สเรียนคุณภาพสูง<br>จากผู้เชี่ยวชาญในแต่ละสาขา
                        </p>
                    </div>
                </div>
                <div class="shadow-sm card bg-base-100 w-72 rounded-3xl">
                    <figure class="px-10 pt-10">
                        <div class="flex items-center justify-center w-20 h-20 bg-blue-600 rounded-full">
                            <i class="fa-solid fa-chart-simple" style="color: #ffffff;"></i>
                        </div>
                    </figure>
                    <div class="items-center text-center card-body">
                        <h2 class="card-title">วิเคราะห์อาชีพ</h2>
                        <p>
                            รับคำแนะนำเส้นทางอาชีพที่เหมาะกับคุณ<br>ด้วยระบบ AI<br>ที่วิเคราะห์จากข้อมูลและทักษะของคุณ
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-around py-10 bg-gray-100 border-gray-200 px-28 border-t-1">
            <div>
                <div>
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <a href="https://maps.app.goo.gl/3zQypKQbXfpekfZTA" target="_blank">
                    <p>คณะวิทยาการสารสนเทศ มหาวิทยาลัยบูรพา<br>169 ถ.ลงหาดบางแสน ต.แสนสุข<br>อ.เมือง จ.ชลบุรี 20131
                        ประเทศไทย</p>
                </a>
                <p class="text-gray-500 ">LOCATION</p>
            </div>
            <div>
                <div>
                    <i class="fa-solid fa-phone"></i>
                </div>
                <p>EMAIL : esp@informatics.buu.ac.th</p>
                <p>Phone : 085-818-8910</p>
                <p class="text-gray-500 ">CONTACT</p>
            </div>
            <div>
                <div>
                    <i class="fa-solid fa-desktop"></i>
                </div>
                <a href="https://www.facebook.com/profile.php/?id=61556923694978" target="_blank"><i
                        class="fab fa-facebook"></i> Eastern Software Park at BUU</a><br>
                <a href="https://esp.informatics.buu.ac.th" target="_blank"><i class="fa-solid fa-earth-europe"
                        target="_blank"></i> https://esp.informatics.buu.ac.th<a>
                        <p class="text-gray-500 ">SOCIAL MEDIA</p>
            </div>
        </div>
        </div>
    </div>
</body>
<footer>
    <div class="p-4 text-center bg-gray-100">
        <p class="text-gray-400">© {{ date('Y') }} JobNexis. All rights reserved.</p>
        <p class="text-gray-400">Developed by Eastern Software Park at BUU</p>
    </div>
</footer>
<script>
    const btn = document.getElementById("backToTopBtn");
    const navbar = document.getElementById("navbar");
    window.addEventListener("scroll", () => {
        const scrollY = window.scrollY;

        if (scrollY > 100) {
            btn.classList.remove("hidden");
            // เปลี่ยนสีตาม scroll
            if (scrollY > 300) {
                navbar.classList.remove("bg-transparent");
                navbar.classList.add(
                    "bg-[linear-gradient(to_bottom,_theme('colors.sky.100')_20%,_theme('colors.sky.200')_100%)]"
                ); // สีใหม่ตอนเลื่อนเยอะ
            } else {
                navbar.classList.remove(
                    "bg-[linear-gradient(to_bottom,_theme('colors.sky.100')_20%,_theme('colors.sky.200')_100%)]"
                );
                navbar.classList.add("bg-transparent"); // สีเริ่มต้น
            }
        } else {
            btn.classList.add("hidden");
        }
    });
</script>
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

</html>
