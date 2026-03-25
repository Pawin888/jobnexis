@extends('layouts.app')

@section('title', 'ใบเ Resume ผู้สมัคร')

@section('content')
<div class="flex flex-col gap-4 p-4 shadow bg-base-200 rounded-2xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">ใบ Resume ผู้สมัครที่เปิดเผย</h1>
            <p class="text-sm opacity-70">ทั้งหมด {{ number_format($resumes->total()) }} รายการ</p>
        </div>
    </div>

    <form method="GET" class="grid items-end grid-cols-1 gap-4 md:grid-cols-6">
        <fieldset class="fieldset md:col-span-2">
            <legend class="mb-1 fieldset-legend">ค้นหา</legend>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                class="pl-2 w-full border border-gray-300 input input-bordered"
                placeholder="ชื่อ, อีเมล, หรือทักษะ">
        </fieldset>
        <fieldset class="fieldset md:col-span-2">
            <legend class="mb-1 fieldset-legend">สถานที่ที่ต้องการ</legend>
            <input type="text" name="location" value="{{ $filters['location'] ?? '' }}"
                class="pl-2 w-full border border-gray-300 input input-bordered"
                placeholder="เช่น กรุงเทพ, เชียงใหม่">
        </fieldset>
        <fieldset class="fieldset md:col-span-1">
            <legend class="mb-1 fieldset-legend">ตัวเลือก</legend>
            <label class="h-10 px-3 border border-gray-300 rounded-lg bg-base-100 inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="saved_only" value="1" class="checkbox checkbox-sm" {{ !empty($filters['saved_only']) ? 'checked' : '' }}>
                เฉพาะที่บันทึก
            </label>
        </fieldset>
        <fieldset class="fieldset md:col-span-1">
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">ค้นหา</button>
                <a href="{{ url()->current() }}" class="btn">ล้าง</a>
            </div>
        </fieldset>
    </form>

    <div class="overflow-x-auto rounded-2xl border shadow bg-base-200">
        <table class="table table-fixed w-full min-w-[980px]">
            <thead class="bg-base-300 text-xs uppercase tracking-wide">
                <tr>
                    <th class="w-[25%] py-3 px-4">ผู้สมัคร</th>
                    <th class="w-[20%] py-3 px-3">ติดต่อ</th>
                    <th class="w-[20%] py-3 px-3">ทักษะเด่น</th>
                    <th class="w-[15%] py-3 px-3">อัปเดตล่าสุด</th>
                    <th class="w-[20%] py-3 px-3 text-left">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-base-300">
                @forelse($resumes as $resume)
                    @php
                        $isSaved = in_array($resume->id, $savedResumeIds, true);
                    @endphp
                    <tr class="hover:bg-base-100 transition-colors">
                        <td class="py-3 px-4 max-w-0">
                            <div class="font-medium truncate text-sm">
                                {{ trim(($resume->first_name ?? '').' '.($resume->last_name ?? '')) }}
                            </div>
                            <div class="text-xs opacity-60 truncate mt-0.5">
                                {{ $resume->preferred_location ?: 'ไม่ระบุพื้นที่ต้องการ' }}
                            </div>
                            @if($isSaved)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 mt-1 text-[11px] rounded-full bg-amber-100 text-amber-700">
                                    <i class="fa-solid fa-bookmark"></i> บันทึกแล้ว
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-xs">
                            <div>{{ $resume->email ?: '-' }}</div>
                            <div class="opacity-70 mt-1">{{ $resume->phone ?: '-' }}</div>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse($resume->resumeSkills->take(4) as $s)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $s->skill->name ?? '-' }}
                                    </span>
                                @empty
                                    <span class="text-xs opacity-60">ยังไม่ระบุทักษะ</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="py-3 px-3 text-xs">
                            {{ optional($resume->updated_at)->format('d/m/Y') ?: '-' }}
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center justify-start gap-1.5 whitespace-nowrap">
                                <a href="{{ route('provider.candidates.show', $resume->id) }}"
                                   class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-blue-50 hover:border-blue-400 transition text-gray-600 hover:text-blue-600"
                                   title="ดูรายละเอียด Resume">
                                    <i class="fa-solid fa-search text-sm"></i>
                                </a>

                                <form method="POST" action="{{ route('provider.candidates.save', $resume->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-amber-50 hover:border-amber-400 transition text-gray-600 hover:text-amber-600"
                                        title="{{ $isSaved ? 'ยกเลิกบันทึกผู้สมัคร' : 'บันทึกผู้สมัคร' }}">
                                        <i class="fa-{{ $isSaved ? 'solid' : 'regular' }} fa-bookmark text-sm"></i>
                                    </button>
                                </form>

                                <button type="button"
                                    class="candidate-invite-btn flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-emerald-50 hover:border-emerald-400 transition text-gray-600 hover:text-emerald-600"
                                    data-form-id="invite-form-{{ $resume->id }}"
                                    title="เชิญสมัครงาน">
                                    <i class="fa-solid fa-paper-plane text-sm"></i>
                                </button>

                                <form id="invite-form-{{ $resume->id }}" method="POST" action="{{ route('provider.candidates.invite', $resume->id) }}" class="hidden">
                                    @csrf
                                    <input type="hidden" name="recruitment_id" value="">
                                </form>

                                <a href="{{ route('provider.candidates.resume-pdf', $resume->id) }}"
                                   class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 bg-base-100 hover:bg-red-50 hover:border-red-400 transition text-gray-600 hover:text-red-600"
                                   title="ดาวน์โหลด Resume">
                                    <i class="fa-solid fa-file-pdf text-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center text-gray-400">
                            <i class="fa-solid fa-user-group text-3xl mb-3 block opacity-40"></i>
                            ไม่พบ Resume ที่เปิดเผย
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($resumes->isNotEmpty())
        <div class="bg-white rounded-xl shadow p-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <label for="perPage" class="text-sm text-gray-700 font-medium">แสดง:</label>
                    <select
                        id="perPage"
                        onchange="window.location.href = '{{ url()->current() }}?perPage=' + this.value + '&q={{ request('q') }}&location={{ request('location') }}&saved_only={{ request('saved_only') }}'"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"
                    >
                        @foreach ([10, 20, 30, 50] as $n)
                            <option value="{{ $n }}" {{ request('perPage', 20) == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                    <span class="text-sm text-gray-700">รายการต่อหน้า</span>
                    <span class="text-sm text-gray-600 ml-4">
                        (แสดง
                        <span class="font-semibold text-gray-800">{{ $resumes->firstItem() ?? 0 }}</span>
                        -
                        <span class="font-semibold text-gray-800">{{ $resumes->lastItem() ?? 0 }}</span>
                        จากทั้งหมด
                        <span class="font-semibold text-gray-800">{{ number_format($resumes->total()) }}</span>
                        รายการ)
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    @if ($resumes->onFirstPage())
                        <button disabled class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed text-sm">ก่อนหน้า</button>
                    @else
                        <a href="{{ $resumes->appends(request()->except('page'))->previousPageUrl() }}"
                           class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">ก่อนหน้า</a>
                    @endif

                    <div class="flex items-center gap-1">
                        @php
                            $current = $resumes->currentPage();
                            $last    = $resumes->lastPage();
                            $start   = max(1, $current - 2);
                            $end     = min($last, $current + 2);
                        @endphp

                        @if ($start > 1)
                            <a href="{{ $resumes->appends(request()->except('page'))->url(1) }}"
                               class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">1</a>
                            @if ($start > 2)
                                <span class="px-2 text-gray-500">...</span>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            @if ($i == $current)
                                <span class="w-8 h-8 flex items-center justify-center bg-blue-600 text-white rounded-lg text-sm font-medium">{{ $i }}</span>
                            @else
                                <a href="{{ $resumes->appends(request()->except('page'))->url($i) }}"
                                   class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">{{ $i }}</a>
                            @endif
                        @endfor

                        @if ($end < $last)
                            @if ($end < $last - 1)
                                <span class="px-2 text-gray-500">...</span>
                            @endif
                            <a href="{{ $resumes->appends(request()->except('page'))->url($last) }}"
                               class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">{{ $last }}</a>
                        @endif
                    </div>

                    @if ($resumes->hasMorePages())
                        <a href="{{ $resumes->appends(request()->except('page'))->nextPageUrl() }}"
                           class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">ถัดไป</a>
                    @else
                        <button disabled class="px-3 py-1.5 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed text-sm">ถัดไป</button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const openRecruitments = @json($openRecruitments->map(fn($j) => ['id' => $j->rc_id, 'title' => $j->rc_title])->values());

    document.querySelectorAll('.candidate-invite-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!openRecruitments.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'ยังไม่มีประกาศงานเปิดรับ',
                    text: 'กรุณาสร้างหรือเปิดประกาศงานก่อนเชิญผู้สมัคร',
                    confirmButtonColor: '#2563eb',
                });
                return;
            }

            const optionsHtml = openRecruitments
                .map(j => `<option value="${j.id}">${j.title}</option>`)
                .join('');

            Swal.fire({
                title: 'เลือกประกาศงาน',
                html: `
                    <select id="invite-recruitment" class="swal2-input" style="width:100%;margin:0;">
                        <option value="">-- เลือกประกาศงาน --</option>
                        ${optionsHtml}
                    </select>
                `,
                showCancelButton: true,
                confirmButtonText: 'เชิญสมัคร',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#059669',
                preConfirm: () => {
                    const selected = document.getElementById('invite-recruitment').value;
                    if (!selected) {
                        Swal.showValidationMessage('กรุณาเลือกประกาศงาน');
                        return false;
                    }
                    return selected;
                },
            }).then((result) => {
                if (!result.isConfirmed) return;

                const formId = btn.dataset.formId;
                const form = document.getElementById(formId);
                if (!form) return;

                form.querySelector('input[name="recruitment_id"]').value = result.value;
                form.submit();
            });
        });
    });

    @if (session('swal_success'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: '{{ session('swal_success') }}',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    @endif

    @if (session('swal_error'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: '{{ session('swal_error') }}',
            showConfirmButton: false,
            timer: 3200,
            timerProgressBar: true,
        });
    @endif
</script>
@endpush
