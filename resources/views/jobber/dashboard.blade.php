<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('User Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="mb-4 text-lg font-semibold">ยินดีต้อนรับ {{ Auth::user()->name }}!</h3>
                    <p class="mb-4">นี่คือหน้า Jobber Dashboard ของคุณ</p>

                    <div class="grid grid-cols-1 gap-4 mt-6 md:grid-cols-2">
                        <div class="p-4 bg-yellow-100 rounded-lg">
                            <h4 class="font-semibold text-yellow-800">ข้อมูลส่วนตัว</h4>
                            <p class="text-sm text-yellow-600">จัดการข้อมูลส่วนตัวของคุณ</p>
                        </div>
                        <div class="p-4 bg-red-100 rounded-lg">
                            <h4 class="font-semibold text-red-800">ประวัติการใช้งาน</h4>
                            <p class="text-sm text-red-600">ดูประวัติการใช้งานของคุณ</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
