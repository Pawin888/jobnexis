@extends('layouts.app')

@section('title', 'ใบสมัครงาน')

@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-28">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-4xl font-bold mb-8">ใบสมัครงานทั้งหมด</h1>

        <!-- Filter -->
        <div class="mb-6 flex gap-3">
            <form method="GET" class="flex gap-2">
                <select name="status" class="select select-bordered" onchange="this.form.submit()">
                    <option value="">ทั้งหมด</option>
                    @foreach(['applied' => 'สมัครแล้ว', 'reviewing' => 'กำลังพิจารณา', 'accepted' => 'ยอมรับ', 'rejected' => 'ปฏิเสธ', 'withdrawn' => 'ถอนการสมัคร'] as $val => $label)
                        <option value="{{ $val }}" {{ $status === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Applications List -->
        <div class="space-y-4">
            @forelse($applications as $app)
                <a href="{{ route('provider.applications.show', $app->id) }}" class="block card bg-white shadow hover:shadow-lg transition">
                    <div class="card-body">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-bold">{{ $app->recruitment->rc_title }}</h3>
                                <p class="text-gray-600">โดย {{ $app->jobber->profile->up_name ?? $app->jobber->name }}</p>
                                <p class="text-gray-500 text-sm">รีซูเม: {{ $app->resume->first_name }} {{ $app->resume->last_name }}</p>
                            </div>
                            <div class="text-right">
                                <span class="badge {{ $app->status === 'accepted' ? 'badge-success' : ($app->status === 'rejected' ? 'badge-error' : 'badge-info') }}">
                                    {{ ['applied' => 'สมัครแล้ว', 'reviewing' => 'กำลังพิจารณา', 'accepted' => 'ยอมรับ', 'rejected' => 'ปฏิเสธ', 'withdrawn' => 'ถอนการสมัคร'][$app->status] ?? $app->status }}
                                </span>
                                <p class="text-gray-500 text-sm">{{ $app->applied_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-center text-gray-500">ไม่มีใบสมัคร</p>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $applications->links() }}
        </div>
    </div>
</div>
@endsection