@extends('layouts.app')

@section('title', 'ผู้ประกอบการ')

@section('content')
    <div class="w-full p-6 shadow bg-base-200 rounded-2xl">
        <div class="flex items-center justify-between pb-4 mb-4 border-b">
            <div>
                <h1 class="text-2xl font-semibold">ผู้ประกอบการทั้งหมด</h1>
                <p class="text-sm opacity-70">{{ number_format($providers->total()) }} รายการ</p>
            </div>
        </div>

        <form method="GET" class="grid items-end grid-cols-1 gap-4 md:grid-cols-5">
            <fieldset class="fieldset md:col-span-2">
                <legend class="mb-1 fieldset-legend">ค้นหาบริษัท</legend>
                <input type="text" name="q" value="{{ $q }}" class="w-full border border-gray-300 input input-bordered" placeholder="  ชื่อบริษัท / อีเมล">
            </fieldset>
            <fieldset class="fieldset">
                <legend class="mb-1 fieldset-legend">จังหวัด</legend>
                <select name="province" class="w-full border border-gray-300 select select-bordered">
                    <option value="">— ทั้งหมด —</option>
                    @foreach($provinces as $pv)
                        <option value="{{ $pv }}" @selected($pv===$province)>{{ $pv }}</option>
                    @endforeach
                </select>
            </fieldset>
            <div class="flex gap-2">
                <button class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">ค้นหา</button>
                <a href="{{ url()->current() }}" class="btn">ล้าง</a>
            </div>
        </form>

        <div class="grid grid-cols-1 gap-4 mt-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($providers as $p)
                <div class="overflow-hidden bg-white shadow rounded-xl">
                    @php
                        $banner = $p->co_banner_img ? asset('storage/'.$p->co_banner_img) : asset('image/web-image/work-group.jpg');
                        $logo   = $p->co_profile_img ? asset('storage/'.$p->co_profile_img) : asset('image/web-image/logo.png');
                    @endphp
                    <div class="relative h-28">
                        <img src="{{ $banner }}" class="object-cover w-full h-full" />
                        <div class="absolute bottom-[-24px] left-4 w-14 h-14 rounded-full overflow-hidden border-2 border-white bg-white">
                            <img src="{{ $logo }}" class="object-cover w-full h-full" />
                        </div>
                    </div>
                    <div class="px-4 pt-8 pb-4">
                        <h2 class="text-lg font-semibold line-clamp-1">{{ $p->co_name ?? $p->email }}</h2>
                        <p class="text-sm text-gray-500 line-clamp-1">{{ $p->co_type ?? 'ไม่ระบุประเภท' }} • {{ $p->co_province ?? 'ไม่ระบุจังหวัด' }}</p>
                        <div class="flex items-center justify-between mt-3">
                            <span class="px-2 py-1 text-xs text-blue-700 bg-blue-100 rounded-full">เปิดรับ {{ $p->open_jobs }} งาน</span>
                            <a href="{{ (auth()->check() && auth()->user()->role==='jobber') ? route('jobber.companies.show', $p->id) : route('companies.show', $p->id) }}" class="text-blue-600 hover:underline">ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 bg-white shadow rounded-xl md:col-span-3">ไม่พบบริษัท</div>
            @endforelse
        </div>

        <div class="flex justify-center mt-6">
            {{ $providers->links() }}
        </div>
    </div>
@endsection
