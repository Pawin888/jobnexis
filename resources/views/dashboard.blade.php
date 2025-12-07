<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="mb-4 text-lg font-semibold">สวัสดี {{ Auth::user()->name }}!</h3>
                    <p class="mb-4">Role ของคุณ: <span class="font-semibold">{{ Auth::user()->role }}</span></p>

                    @if (Auth::user()->isAdmin())
                        <div class="mb-4">
                            <a href="{{ route('admin.dashboard') }}"
                                class="px-4 py-2 font-bold text-white bg-blue-500 rounded hover:bg-blue-700">
                                เข้าสู่ Admin Dashboard
                            </a>
                        </div>
                    @endif

                    @if (Auth::user()->isEducation())
                        <div class="mb-4">
                            <a href="{{ route('education.dashboard') }}"
                                class="px-4 py-2 font-bold text-white bg-green-500 rounded hover:bg-green-700">
                                เข้าสู่ User Education
                            </a>
                        </div>
                    @endif

                    @if (Auth::user()->isProvider())
                        <div class="mb-4">
                            <a href="{{ route('provider.dashboard') }}"
                                class="px-4 py-2 font-bold text-white bg-yellow-500 rounded hover:bg-yellow-700">
                                เข้าสู่ User Provider
                            </a>
                        </div>
                    @endif

                    @if (Auth::user()->isJobber())
                        <div class="mb-4">
                            <a href="{{ route('jobber.dashboard') }}"
                                class="px-4 py-2 font-bold text-white bg-purple-500 rounded hover:bg-purple-700">
                                เข้าสู่ User Jobber
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
