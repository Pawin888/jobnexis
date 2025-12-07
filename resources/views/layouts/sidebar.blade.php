@if (Auth::check() && Auth::user()->role === 'admin')
    <aside
        class="bg-base-200 w-[230px] p-4 flex flex-col items-center shadow border-r border-gray-300 min-h-[calc(100vh-4rem)]">
        <a href="/">
            <img src="{{ asset('image/web-image/logo.png') }}" alt="logo" class="w-auto h-12 mb-6">
        </a>
        <ul class="w-full gap-2 menu menu-vertical text-base-content">
            <li>
                <a href="{{ url('admin/dashboard') }}"
                    class="flex items-center {{ request()->is('education/dashboard') ? 'text-blue-600' : '' }}">
                    <i
                        class="fa-solid fa-tachometer-alt w-5 text-blue-600 text-center mr-1 {{ request()->is('education/dashboard') ? 'text-blue-600' : '' }}"></i>
                    แดชบอร์ด
                </a>
            </li>
            {{-- <li>
                <a href="{{ url('#') }}"
                    class="flex items-center {{ request()->is('education/courses') ? 'text-blue-600' : '' }}">
                    <i
                        class="fa-solid fa-graduation-cap w-5 text-blue-600 text-center mr-1 {{ request()->is('education/courses') ? 'text-blue-600' : '' }}"></i>
                    คอร์สอบรม
                </a>
            </li>
            <li>
                <a href="{{ url('#') }}"
                    class="flex items-center {{ request()->is('education/courses') ? 'text-blue-600' : '' }}">
                    <i
                        class="fa-solid fa-clipboard-check w-5 text-blue-600 text-center mr-1 {{ request()->is('education/courses') ? 'text-blue-600' : '' }}"></i>
                    งาน
                </a> --}}
            </li>
            <li>
                <a href="{{ url('admin/jobber') }}"
                    class="flex items-center {{ request()->is('education/courses') ? 'text-blue-600' : '' }}">
                    <i
                        class="fa-solid fa-user w-5 text-blue-600 text-center mr-1 {{ request()->is('education/courses') ? 'text-blue-600' : '' }}"></i>
                    ผู้สมัครงาน
                </a>
            </li>
            <li>
                <a href="{{ url('admin/providers') }}"
                    class="flex items-center {{ request()->is('education/courses') ? 'text-blue-600' : '' }}">
                    <i
                        class="fa-solid fa-building w-5 text-blue-600 text-center mr-1 {{ request()->is('education/courses') ? 'text-blue-600' : '' }}"></i>
                    ผู้ประกอบการ
                </a>
            </li>
            <li>
                <a href="{{ url('admin/education') }}"
                    class="flex items-center {{ request()->is('education/courses') ? 'text-blue-600' : '' }}">
                    <i
                        class="fa-solid fa-building-columns w-5 text-blue-600 text-center mr-1 {{ request()->is('education/courses') ? 'text-blue-600' : '' }}"></i>
                    สถาบันการศึกษา
                </a>
            </li>
            <li>
                <a href="{{ route('management.skills.index') }}"
                    class="flex items-center {{ request()->is('admin/management-skills*') ? 'text-blue-600' : '' }}">
                    <i
                        class="fa-solid fa-lightbulb w-5 text-blue-600 text-center mr-1"></i>
                    จัดการทักษะ
                </a>
            </li>
            <li>

                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i
                        class="fa-solid fa-arrow-right-from-bracket w-5 text-blue-600 text-center mr-1 {{ request()->is('education/dashboard') ? 'text-blue-600' : '' }}"></i>
                    ออกจากระบบ
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>

            </li>
        </ul>
    </aside>
@elseif (Auth::check() && Auth::user()->role === 'education')
    <aside
        class="bg-base-200 w-[230px] p-4 flex flex-col items-center shadow border-r border-gray-300 min-h-[calc(100vh-4rem)]">
        <a href="/">
            <img src="{{ asset('image/web-image/logo.png') }}" alt="logo" class="w-auto h-12 mb-6">
        </a>
        <ul class="w-full gap-2 menu menu-vertical text-base-content">
            <li>
                <a href="{{ route('education.dashboard') }}"
                    class="flex items-center {{ request()->is('education/dashboard') ? 'text-blue-600' : '' }}">
                    <i
                        class="fa-solid fa-tachometer-alt w-5 text-blue-600 text-center mr-1 {{ request()->is('education/dashboard') ? 'text-blue-600' : '' }}"></i>
                    แดชบอร์ด
                </a>
            </li>
            <li>
                <a href="{{ url('education/courses') }}"
                    class="flex items-center {{ request()->is('education/courses') ? 'text-blue-600' : '' }}">
                    <i
                        class="fa-solid fa-graduation-cap w-5 text-blue-600 text-center mr-1 {{ request()->is('education/courses') ? 'text-blue-600' : '' }}"></i>
                    คอร์สอบรม
                </a>
            </li>
            <li>

                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i
                        class="fa-solid fa-arrow-right-from-bracket w-5 text-blue-600 text-center mr-1 {{ request()->is('education/dashboard') ? 'text-blue-600' : '' }}"></i>
                    ออกจากระบบ
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>

            </li>
        </ul>
    </aside>
@elseif (Auth::check() && Auth::user()->role === 'provider')
    <aside
        class="bg-base-200 w-[230px] p-4 flex flex-col items-center shadow border-r border-gray-300 min-h-[calc(100vh-4rem)]">
        <a href="/">
            <img src="{{ asset('image/web-image/logo.png') }}" alt="logo" class="w-auto h-12 mb-6">
        </a>
        <ul class="w-full gap-2 menu menu-vertical text-base-content">
            <li>
                <a href="{{ route('provider.dashboard') }}"
                    class="flex items-center {{ request()->is('education/dashboard') ? 'text-blue-600' : '' }}">
                    <i
                        class="fa-solid fa-tachometer-alt w-5 text-blue-600 text-center mr-1 {{ request()->is('education/dashboard') ? 'text-blue-600' : '' }}"></i>
                    แดชบอร์ด
                </a>
            </li>
            <li>
                <a href="{{ route('provider.recruitments.index') }}"
                    class="flex items-center {{ request()->is('education/courses') ? 'text-blue-600' : '' }}">
                    <i
                        class="fa-solid fa-clipboard-check w-5 text-blue-600 text-center mr-1 {{ request()->is('education/courses') ? 'text-blue-600' : '' }}"></i>
                    ประกาศงาน
                </a>
            </li>
            <li>

                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i
                        class="fa-solid fa-arrow-right-from-bracket w-5 text-blue-600 text-center mr-1 {{ request()->is('education/dashboard') ? 'text-blue-600' : '' }}"></i>
                    ออกจากระบบ
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>

            </li>
        </ul>
    </aside>
@endif
