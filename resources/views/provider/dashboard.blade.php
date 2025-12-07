@extends('layouts.app')

@section('title', 'แดชบอร์ดผู้ประกอบการ')

@section('content')
    <div class="w-full p-6 shadow bg-base-200 rounded-2xl">
        <div class="flex items-center justify-end pb-4 mb-6 border-b">
            <a href="{{ route('provider.recruitments.create') }}" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">ประกาศงานใหม่</a>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="p-4 bg-white shadow rounded-xl">
                <div class="text-sm text-gray-500">เปิดรับ</div>
                <div class="mt-1 text-2xl font-bold text-green-600">{{ $totals['open'] ?? 0 }}</div>
            </div>
            <div class="p-4 bg-white shadow rounded-xl">
                <div class="text-sm text-gray-500">ฉบับร่าง</div>
                <div class="mt-1 text-2xl font-bold text-yellow-600">{{ $totals['draft'] ?? 0 }}</div>
            </div>
            <div class="p-4 bg-white shadow rounded-xl">
                <div class="text-sm text-gray-500">ปิดรับ</div>
                <div class="mt-1 text-2xl font-bold text-gray-700">{{ $totals['closed'] ?? 0 }}</div>
            </div>
            <div class="p-4 bg-white shadow rounded-xl">
                <div class="text-sm text-gray-500">ยอดเข้าชมรวม</div>
                <div class="mt-1 text-2xl font-bold">{{ number_format($views ?? 0) }}</div>
            </div>
        </div>

        <div class="mt-6">
            <h2 class="mb-3 text-xl font-semibold">ประกาศงานล่าสุด</h2>
            <div class="overflow-x-auto bg-white shadow rounded-xl">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ชื่องาน</th>
                            <th>สถานะ</th>
                            <th>โพสต์เมื่อ</th>
                            <th>การทำงาน</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentJobs as $r)
                            @php
                                $badge = [
                                    'open' => 'bg-green-200 text-green-700',
                                    'draft'=> 'bg-yellow-200 text-yellow-700',
                                    'closed'=> 'bg-gray-200 text-gray-700',
                                ][$r->rc_status] ?? 'bg-gray-200';
                            @endphp
                            <tr>
                                <td class="max-w-[320px] line-clamp-1">{{ $r->rc_title }}</td>
                                <td><span class="px-3 py-1 text-sm rounded-full {{ $badge }}">{{ $r->rc_status === 'open' ? 'เปิดรับ' : ($r->rc_status === 'draft' ? 'ฉบับร่าง' : 'ปิดรับ') }}</span></td>
                                <td>{{ optional($r->rc_posted_at)->timezone('Asia/Bangkok')->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>
                                    <div class="join">
                                        <a href="{{ route('provider.recruitments.edit', $r->rc_id) }}" class="join-item btn btn-sm">แก้ไข</a>
                                        <form method="POST" action="{{ route('provider.recruitments.status', $r->rc_id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="to" value="{{ $r->rc_status === 'open' ? 'draft' : 'open' }}">
                                            <button type="submit" class="join-item btn btn-sm">{{ $r->rc_status === 'open' ? 'ตั้งเป็นร่าง' : 'เผยแพร่' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-500">ยังไม่มีประกาศงาน</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
