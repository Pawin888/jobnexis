@extends('layouts.app')

@section('title', 'บุคคล')

@section('content')
    <div class="w-full p-6 bg-base-200 rounded-2xl shadow">

        <!-- Header Section -->
        <div class="flex items-center justify-between border-b pb-3 mb-6">
            <!-- Left side -->
            <div class="flex items-baseline gap-3">
                <!-- ลิงก์รายละเอียดคอร์ส -->
                <a href="{{ route('courses.show', ['id' => $course->c_id]) }}"
                    class="text-xl text-base-content cursor-pointer hover:text-blue-600">
                    รายละเอียด
                </a>

                <!-- ลิงก์บุคคล -->
                <a href="{{ route('courses.person.show', ['id' => $course->c_id]) }}"
                    class="text-3xl font-bold text-blue-600 underline cursor-pointer hover:text-blue-600">
                    ผู้เรียน
                </a>
            </div>

            <!-- Right side: Icon -->
            <div>
                <a href="{{ route('courses.edit', ['id' => $course->c_id ?? $id]) }}">
                    <i class="fa-solid fa-gear text-xl text-base-content cursor-pointer hover:text-blue-600"></i>
                </a>
            </div>
        </div>

        <!-- Section: มหาวิทยาลัยที่เปิดสอน -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">
                <i class="fa-solid fa-building-columns mr-2 text-blue-600"></i>
                มหาวิทยาลัยที่เปิดสอน
            </h2>
            <div class="flex items-center gap-4 p-4 bg-white rounded-xl shadow">
                <div>
                    <h3 class="text-lg font-bold">
                        {{ $universityProfile->e_name ?? 'ไม่พบข้อมูลมหาวิทยาลัย' }}
                    </h3>
                    @if (!empty($universityProfile?->e_email) || !empty($universityProfile?->e_phone))
                        <p class="text-gray-500">
                            {{ $universityProfile->e_email ?? '' }}
                            {{ !empty($universityProfile->e_email) && !empty($universityProfile->e_phone) ? ' · ' : '' }}
                            {{ $universityProfile->e_phone ?? '' }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Section: ผู้เข้าร่วม -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-semibold">
                    <i class="fa-solid fa-users mr-2 text-blue-600"></i>
                    ผู้เข้าร่วม ({{ $members->count() }} คน)
                </h2>
                @php
                    $issuedIds = collect($issuedUserIds ?? []);
                    $memberIds = $members->pluck('cm_u_id');
                    $notIssuedCount = $memberIds->diff($issuedIds)->count();
                @endphp
                @if (auth()->check() && (auth()->user()->role === 'admin' || auth()->id() === $course->c_create_by_id))
                    @if ($notIssuedCount > 0)
                        <form method="POST"
                            action="{{ route('courses.members.certificate.issueAll', ['id' => $course->c_id]) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                ออกประกาศนียบัตรให้ทุกคน ({{ $notIssuedCount }})
                            </button>
                        </form>
                    @else
                        <span class="px-3 py-1 text-sm text-green-700 bg-green-100 rounded-lg">ออกครบแล้ว</span>
                    @endif
                @endif
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                @forelse($members as $m)
                    <div class=" p-4 bg-white rounded-xl shadow">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div>
                                    <h3 class="text-lg font-bold">
                                        {{ optional($m->user->profile)->up_name ?? ($m->user->email ?? 'User #' . $m->cm_u_id) }}
                                    </h3>
                                    <p class="text-gray-500">สมัครเมื่อ
                                        {{ $m->created_at?->timezone('Asia/Bangkok')->format('Y-m-d H:i') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-3 py-1 rounded-lg text-sm {{ $m->cm_passed ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $m->cm_passed ? 'ผ่านคอร์ส' : 'กำลังเรียน' }}
                                </span>
                                @if (!empty($issuedUserIds) && in_array($m->cm_u_id, $issuedUserIds))
                                    <span class="px-3 py-1 text-sm text-blue-700 bg-blue-100 rounded-lg">ออกแล้ว</span>
                                @else
                                    @if (auth()->check() && (auth()->user()->role === 'admin' || auth()->id() === $course->c_create_by_id))
                                        <form method="POST"
                                            action="{{ route('courses.members.certificate.issue', ['id' => $course->c_id, 'userId' => $m->cm_u_id]) }}">
                                            @csrf
                                            <button type="submit"
                                                class="px-3 py-1 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                                ออกประกาศนียบัตร
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @php $sum = isset($summaryByUser) ? $summaryByUser->get($m->cm_u_id) : null; @endphp
                        @if ($sum)
                            <p class="text-sm mt-1">
                                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700">ทดสอบ: {{ $sum->passed }} /
                                    {{ $sum->total }} ({{ number_format($sum->percentage, 1) }}%)</span>
                            </p>
                        @else
                            <p class="text-sm mt-1">
                                <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700">ทดสอบ: 0 /
                                    {{ $courseExamTotal ?? 0 }}</span>
                            </p>
                        @endif

                    </div>
                @empty
                    <div class="p-4 bg-white rounded-xl shadow text-gray-500">ยังไม่มีผู้เข้าร่วม</div>
                @endforelse
            </div>
        </div>

        {{-- @if (auth()->check() && in_array(auth()->user()->role, ['education', 'admin']))
    <div class="w-full mt-8 p-6 shadow bg-base-200 rounded-2xl">
        <div class="flex items-center justify-between mb-3 border-b pb-2">
            <h3 class="text-xl font-semibold text-base-content">สรุปผลแบบทดสอบของคอร์สนี้</h3>
            <span class="text-sm text-gray-600">แบบทดสอบทั้งหมด: {{ $courseExamTotal ?? 0 }}</span>
        </div>
        @if (($reportRows->count() ?? 0) === 0)
            <div class="p-4 bg-white rounded-lg text-center text-gray-500">ยังไม่มีผู้เรียนหรือตัวชี้วัด</div>
        @else
            <div class="overflow-x-auto bg-white rounded-lg">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="text-left">ผู้ใช้</th>
                            <th class="text-center">ผ่าน/ทั้งหมด</th>
                            <th class="text-center">เปอร์เซ็นต์</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reportRows as $row)
                            <tr>
                                <td class="py-3">{{ $row->name }}</td>
                                <td class="text-center">{{ $row->passed }} / {{ $row->total }}</td>
                                <td class="text-center">{{ number_format($row->percentage, 1) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @endif --}}
    </div>
@endsection
