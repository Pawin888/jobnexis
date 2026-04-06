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
                <a href="{{ route('admin.master-data.skills-overview') }}"
                    class="flex items-center px-3 py-2 rounded-lg {{ request()->is('admin/master-data/master-skills*') ? 'text-blue-600' : '' }}">
                    <i
                        class="fa-solid fa-layer-group w-5 text-blue-600 text-center mr-1"></i>
                    คลังทักษะ ESCO
                </a>
            </li>
            <li>
                <a href="{{ route('admin.custom-taxonomy.index') }}"
                    class="flex items-center px-3 py-2 rounded-lg {{ request()->is('admin/custom-taxonomy*') ? 'text-blue-600' : '' }}">
                    <i class="fa-solid fa-sitemap w-5 text-blue-600 text-center mr-1"></i>
                    โครงสร้างทักษะเฉพาะ
                </a>
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
                    class="flex items-center {{ request()->is('provider/dashboard') ? 'text-blue-600' : '' }}">
                    <i
                        class="fa-solid fa-tachometer-alt w-5 text-blue-600 text-center mr-1 {{ request()->is('provider/dashboard') ? 'text-blue-600' : '' }}"></i>
                    แดชบอร์ด
                </a>
            </li>
            <li>
                <a href="{{ route('provider.recruitments.index') }}"
                    class="flex items-center {{ request()->is('my/recruitments*') ? 'text-blue-600' : '' }}">
                    <i class="fa-solid fa-clipboard-check w-5 text-blue-600 text-center mr-1 {{ request()->is('my/recruitments*') ? 'text-blue-600' : '' }}"></i>
                    ประกาศงาน
                </a>
            </li>
            <li>
                <a href="{{ route('provider.applications.index') }}"
                    class="flex items-center {{ request()->is('provider/applications*') ? 'text-blue-600' : '' }}">
                    <i class="fa-solid fa-file-signature w-5 text-blue-600 text-center mr-1 {{ request()->is('provider/applications*') ? 'text-blue-600' : '' }}"></i>
                    ใบสมัครงาน
                </a>
            </li>
            <li>
                <a href="{{ route('provider.candidates.index') }}"
                    class="flex items-center {{ request()->is('provider/candidates*') ? 'text-blue-600' : '' }}">
                    <i class="fa-solid fa-address-book w-5 text-blue-600 text-center mr-1 {{ request()->is('provider/candidates*') ? 'text-blue-600' : '' }}"></i>
                    ใบ Resume ผู้สมัคร
                </a>
            </li>
            <li>
                <a href="{{ route('jobs.index') }}"
                    class="flex items-center {{ request()->is('jobs*') ? 'text-blue-600' : '' }}">
                    <i
                        class="fa-solid fa-magnifying-glass w-5 text-blue-600 text-center mr-1 {{ request()->is('jobs*') ? 'text-blue-600' : '' }}"></i>
                    งานที่เปิดรับสมัคร
                </a>
            </li>
        </ul>
    </aside>
@endif
