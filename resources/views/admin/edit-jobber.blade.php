@extends('layouts.app')

@section('title', 'จัดการโปรไฟล์')

@section('content')
    <div class="p-4 overflow-x-auto border shadow bg-base-200 rounded-2xl">


        <form method="POST"
            action="{{ auth()->user()->role === 'admin'
                ? route('profile-details.store', $targetUserId ?? auth()->id())
                : route('profile-jobber.store') }}">
            @csrf
            <div>
                @if ($errors->any())
                    <ul class="mt-3 text-sm text-red-600 list-disc list-inside">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif

                {{-- ================= ข้อมูลส่วนตัว (user_profiles) ================= --}}
                <div class="flex justify-between">
                    <div class="w-full">
                        <h1>โปรไฟล์</h1>
                        <p>จัดการข้อมูลบัญชีและการตั้งค่าของคุณ</p>
                    </div>
                    <div class="flex flex-col justify-end w-full gap-4">
                        <div class="flex justify-end gap-4">
                            <fieldset class="fieldset">
                                <legend class="mb-1 fieldset-legend">คำนำหน้า</legend>
                                <select name="up_prefix" class="pl-2 border border-gray-300 select w-72">
                                    <option value="">-- เลือกคำนำหน้า --</option>
                                    <option value="นาย"
                                        {{ old('up_prefix', $profile->up_prefix ?? '') == 'นาย' ? 'selected' : '' }}>นาย
                                    </option>
                                    <option value="นาง"
                                        {{ old('up_prefix', $profile->up_prefix ?? '') == 'นาง' ? 'selected' : '' }}>นาง
                                    </option>
                                    <option value="นางสาว"
                                        {{ old('up_prefix', $profile->up_prefix ?? '') == 'นางสาว' ? 'selected' : '' }}>
                                        นางสาว
                                    </option>
                                    <option value="เด็กชาย"
                                        {{ old('up_prefix', $profile->up_prefix ?? '') == 'เด็กชาย' ? 'selected' : '' }}>
                                        เด็กชาย
                                    </option>
                                    <option value="เด็กหญิง"
                                        {{ old('up_prefix', $profile->up_prefix ?? '') == 'เด็กหญิง' ? 'selected' : '' }}>
                                        เด็กหญิง</option>
                                    <option value="อื่นๆ"
                                        {{ old('up_prefix', $profile->up_prefix ?? '') == 'อื่นๆ' ? 'selected' : '' }}>อื่นๆ
                                    </option>
                                </select>

                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="mb-1 fieldset-legend">ชื่อ-นามสกุล</legend>
                                <input value="{{ old('up_name', $profile->up_name ?? '') }}" type="text" name="up_name"
                                    class="pl-2 border border-gray-300 input w-72" placeholder="ชื่อ-นามสกุล" />
                            </fieldset>

                        </div>
                        <div class="flex justify-end gap-4">
                            <fieldset class="fieldset">
                                <legend class="mb-1 fieldset-legend">เบอร์โทรศัพท์</legend>
                                <input value="{{ old('up_phone', $profile->up_phone ?? '') }}" type="text"
                                    name="up_phone" class="pl-2 border border-gray-300 input w-72" placeholder="Phone"
                                    maxlength="10" />
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="mb-1 fieldset-legend">เพศ</legend>
                                <select name="up_gender" class="pl-2 border border-gray-300 select w-72">
                                    <option value="">-- เลือกเพศ --</option>
                                    <option value="ชาย"
                                        {{ old('up_gender', $profile->up_gender ?? '') == 'ชาย' ? 'selected' : '' }}>ชาย
                                    </option>
                                    <option value="หญิง"
                                        {{ old('up_gender', $profile->up_gender ?? '') == 'หญิง' ? 'selected' : '' }}>หญิง
                                    </option>
                                    <option value="อื่นๆ"
                                        {{ old('up_gender', $profile->up_gender ?? '') == 'อื่นๆ' ? 'selected' : '' }}>
                                        อื่นๆ
                                    </option>
                                </select>
                            </fieldset>
                        </div>
                        <div class="flex justify-end gap-4">
                            <fieldset class="fieldset">
                                <legend class="mb-1 fieldset-legend">วันเกิด</legend>
                                <input value="{{ old('up_birth_date', $profile->up_birth_date ?? '') }}" type="date"
                                    name="up_birth_date" max="{{ now()->toDateString() }}"
                                    class="pl-2 border border-gray-300 input w-72" />
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend for="up_city" class="mb-1 fieldset-legend">จังหวัด</legend>
                                <select name="up_city" id="up_city" class="pl-2 border border-gray-300 select w-72">
                                    <option value="">-- เลือกจังหวัด --</option>
                                    @foreach ($provinces as $province)
                                        <option value="{{ $province }}"
                                            {{ old('up_city', $profile->up_city ?? '') == $province ? 'selected' : '' }}>
                                            {{ $province }}
                                        </option>
                                    @endforeach
                                </select>
                            </fieldset>
                        </div>
                    </div>
                </div>

                <hr class="my-12 border-gray-300">

                {{-- ================= ประสบการณ์การศึกษา (educations) ================= --}}
                <div class="flex justify-between">
                    <h1 class="w-full">ประสบการณ์การศึกษา</h1>
                    <div id="education-container">
                        @foreach ($educations ?? collect() as $i => $edu)
                            <div class="relative edu-row" data-id="{{ $edu->ed_id }}">
                                <div class="flex flex-col gap-4">
                                    <fieldset class="fieldset">
                                        <legend class="mb-1 fieldset-legend">สถานศึกษาและสาขา</legend>
                                        <input type="text" name="educations[{{ $i }}][ed_name]"
                                            value="{{ old("educations.$i.ed_name", $edu->ed_name) }}"
                                            class="pl-2 border border-gray-300 input w-72">
                                    </fieldset>
                                    <div class="flex gap-4">
                                        <fieldset class="fieldset">
                                            <legend class="mb-1 fieldset-legend">วันที่เริ่ม</legend>
                                            <input type="date" name="educations[{{ $i }}][ed_start_date]"
                                                value="{{ old("educations.$i.ed_start_date", $edu->ed_start_date) }}"
                                                class="pl-2 border border-gray-300 input w-72">
                                        </fieldset>
                                        <fieldset class="fieldset">
                                            <legend class="mb-1 fieldset-legend">วันที่สิ้นสุด</legend>
                                            <input type="date" name="educations[{{ $i }}][ed_end_date]"
                                                value="{{ old("educations.$i.ed_end_date", $edu->ed_end_date) }}"
                                                class="pl-2 border border-gray-300 input w-72">
                                        </fieldset>
                                    </div>
                                </div>
                                <div class="flex justify-end mt-1">
                                    <button type="button" class="text-red-600 delete-education"
                                        data-id="{{ $edu->ed_id }}"><i class="fa-solid fa-trash"></i>
                                        ลบ</button>
                                </div>
                            </div>
                        @endforeach

                        @if (($educations ?? collect())->count() == 0)
                            <div class="relative edu-row">
                                <div class="flex flex-col gap-4">
                                    <fieldset class="fieldset">
                                        <legend class="mb-1 fieldset-legend">สถานศึกษาและสาขา</legend>
                                        <input type="text" name="educations[0][ed_name]"
                                            class="pl-2 border border-gray-300 input w-72">
                                    </fieldset>
                                    <div class="flex gap-4">
                                        <fieldset class="fieldset">
                                            <legend class="mb-1 fieldset-legend">วันที่เริ่ม</legend>
                                            <input type="date" name="educations[0][ed_start_date]"
                                                class="pl-2 border border-gray-300 input w-72">
                                        </fieldset>
                                        <fieldset class="fieldset">
                                            <legend class="mb-1 fieldset-legend">วันที่สิ้นสุด</legend>
                                            <input type="date" name="educations[0][ed_end_date]"
                                                class="pl-2 border border-gray-300 input w-72">
                                        </fieldset>
                                    </div>

                                </div>
                            </div>
                        @endif

                        <button type="button" id="add-education"
                            class="w-full mt-2 text-blue-600 border-2 border-blue-600 border-dashed rounded-lg btn btn-sm">
                            <i class="fa-solid fa-plus"></i> เพิ่มการศึกษา
                        </button>
                    </div>
                </div>



                <hr class="my-12 border-gray-300">

                {{-- ================= ประสบการณ์ทำงาน (work_experiences) ================= --}}
                <div class="flex justify-between">
                    <h1>ประสบการณ์ทำงาน</h1>
                    <div id="work-container">
                        @foreach ($works ?? collect() as $i => $work)
                            <div class="relative" data-id="{{ $work->we_id }}">
                                <div class="flex flex-col gap-4">
                                    <fieldset class="fieldset">
                                        <legend class="mb-1 fieldset-legend">ชื่อบริษัท</legend>
                                        <input type="text"
                                            name="work_experiences[{{ $i }}][we_company_name]"
                                            value="{{ old("work_experiences.$i.we_company_name", $work->we_company_name) }}"
                                            class="pl-2 border border-gray-300 input w-72">
                                    </fieldset>
                                    <div class="flex gap-4 ">
                                        <fieldset class="fieldset">
                                            <legend class="mb-1 fieldset-legend">วันที่เริ่ม</legend>
                                            <input type="date"
                                                name="work_experiences[{{ $i }}][we_start_date]"
                                                value="{{ old("work_experiences.$i.we_start_date", $work->we_start_date) }}"
                                                class="pl-2 border border-gray-300 input w-72">
                                        </fieldset>
                                        <fieldset class="fieldset">
                                            <legend class="mb-1 fieldset-legend">วันที่สิ้นสุด</legend>
                                            <input type="date"
                                                name="work_experiences[{{ $i }}][we_end_date]"
                                                value="{{ old("work_experiences.$i.we_end_date", $work->we_end_date) }}"
                                                class="pl-2 border border-gray-300 input w-72">
                                        </fieldset>
                                    </div>
                                </div>
                                <div class="flex justify-end w-full mt-1">
                                    <button type="button" class="text-red-600 delete-work"
                                        data-id="{{ $work->we_id }}"><i class="fa-solid fa-trash"></i>
                                        ลบ</button>
                                </div>
                            </div>
                        @endforeach

                        @if (($works ?? collect())->count() == 0)
                            <div class="relative">
                                <div class="flex flex-col gap-4">
                                    <fieldset class="fieldset">
                                        <legend class="mb-1 fieldset-legend">ชื่อบริษัท</legend>
                                        <input type="text" name="work_experiences[0][we_company_name]"
                                            class="pl-2 border border-gray-300 input w-72">
                                    </fieldset>
                                    <div class="flex gap-4 ">
                                        <fieldset class="fieldset">
                                            <legend class="mb-1 fieldset-legend">วันที่เริ่ม</legend>
                                            <input type="date" name="work_experiences[0][we_start_date]"
                                                class="pl-2 border border-gray-300 input w-72">
                                        </fieldset>
                                        <fieldset class="fieldset">
                                            <legend class="mb-1 fieldset-legend">วันที่สิ้นสุด</legend>
                                            <input type="date" name="work_experiences[0][we_end_date]"
                                                class="pl-2 border border-gray-300 input w-72">
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <button type="button" id="add-work"
                            class="w-full mt-2 text-blue-600 border-2 border-blue-600 border-dashed rounded-lg btn btn-sm">
                            <i class="fa-solid fa-plus"></i> เพิ่มการทำงาน
                        </button>
                    </div>
                </div>
                <div class="flex justify-end mt-10">
                    <button type="submit" class="px-6 py-2 text-white bg-blue-600 rounded-lg shadow">
                        บันทึกข้อมูล
                    </button>
                </div>
            </div>
        </form>
        <hr class="my-12 border-gray-300">
        {{-- ================= ใบประกาศนียบัตร (certificates) ================= --}}
        <div class="flex flex-col items-start gap-4">
            <div class="flex justify-between w-full">
                <h1>ประกาศนียบัตร</h1>
                <button onclick="document.getElementById('addForm').classList.toggle('hidden')"
                    class="p-2 text-blue-600 border-2 border-blue-600 border-dashed rounded-lg btn btn-sm">
                    <i class="fa-solid fa-plus"></i> เพิ่มประกาศนียบัตร
                </button>
            </div>

            {{-- ฟอร์มเพิ่มใบประกาศ --}}
            {{-- ฟอร์มเพิ่มใบประกาศ --}}
            <form method="POST"
                action="{{ auth()->user()->role === 'admin'
                    ? route('admin.certificates.store', ['userId' => $targetUserId])
                    : route('certificates.store') }}"
                enctype="multipart/form-data" id="addForm" class="hidden w-full p-4 border rounded-lg bg-base-200">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <fieldset class="fieldset">
                        <legend class="mb-1 fieldset-legend">ชื่อใบประกาศ *</legend>
                        <input name="cer_name" type="text" placeholder="ชื่อใบประกาศ *"
                            class="w-full pl-2 border border-gray-300 input" required />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="mb-1 fieldset-legend">สถาบันผู้ออก *</legend>
                        <input name="cer_institute_name" type="text" placeholder="สถาบันผู้ออก *"
                            class="w-full pl-2 border border-gray-300 input" required />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="mb-1 fieldset-legend">รหัสอ้างอิง</legend>
                        <input name="cer_ref_number" type="text" placeholder="รหัสอ้างอิง"
                            class="w-full pl-2 border border-gray-300 input" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="mb-1 fieldset-legend">อัปโหลดรูปภาพ *</legend>
                        <input type="file" name="cer_image" accept="image/*"
                            class="w-full pl-2 border border-gray-300 file-input file-input-bordered" required>
                        <p class="flex justify-end w-full text-xs text-gray-400">jpg,jpeg,png,webp:max 4 MB</p>
                    </fieldset>
                </div>
                <div class="flex justify-end w-full ">
                    <button type="submit" class="px-4 py-2 mt-4 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        เพิ่มใบประกาศ
                    </button>
                </div>

            </form>


            {{-- แท็บแสดงรายการใบประกาศ (component) --}}
            <div class="w-full mt-6">
                <x-cert-tabs :certificates="$certificates" />
            </div>
        </div>
    </div>

    <script>
        // Helper: attach validation between a start and end date inputs
        function attachDatePairValidation(startInput, endInput) {
            if (!startInput || !endInput) return;

            const validate = () => {
                if (startInput.value && endInput.value && endInput.value < startInput.value) {
                    endInput.setCustomValidity('วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่ม');
                } else {
                    endInput.setCustomValidity('');
                }
            };

            const syncMin = () => {
                if (startInput.value) {
                    endInput.min = startInput.value;
                } else {
                    endInput.removeAttribute('min');
                }
                validate();
            };

            startInput.addEventListener('input', syncMin);
            endInput.addEventListener('input', validate);

            // Initialize constraints on load
            syncMin();
        }

        // Initialize validation for existing rows on page load
        function initExistingDateValidation() {
            // Educations
            document.querySelectorAll('#education-container .relative').forEach(row => {
                const s = row.querySelector('input[name$="[ed_start_date]"]');
                const e = row.querySelector('input[name$="[ed_end_date]"]');
                attachDatePairValidation(s, e);
            });
            // Work experiences
            document.querySelectorAll('#work-container .relative').forEach(row => {
                const s = row.querySelector('input[name$="[we_start_date]"]');
                const e = row.querySelector('input[name$="[we_end_date]"]');
                attachDatePairValidation(s, e);
            });
        }

        initExistingDateValidation();

        function createRow(type, index) {
            const row = document.createElement('div');
            row.classList.add('relative');

            if (type === 'education') {
                row.innerHTML = `
                <div class="flex flex-col gap-4">
                    <fieldset class="fieldset">
                        <legend class="mb-1 fieldset-legend">สถานศึกษาและสาขา</legend>
                        <input type="text" name="educations[${index}][ed_name]"
                            class="pl-2 border border-gray-300 input w-72">
                    </fieldset>
                    <div class="flex gap-4">
                        <fieldset class="fieldset">
                        <legend class="mb-1 fieldset-legend">วันที่เริ่ม</legend>
                        <input type="date" name="educations[${index}][ed_start_date]"
                            class="pl-2 border border-gray-300 input w-72">
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="mb-1 fieldset-legend">วันที่สิ้นสุด</legend>
                        <input type="date" name="educations[${index}][ed_end_date]"
                            class="pl-2 border border-gray-300 input w-72">
                    </fieldset>
                    </div>
                </div>
                <div class="flex justify-end w-full mt-1">
                    <button type="button" class="text-red-600 delete-row">
                        <i class="fa-solid fa-trash"></i> ลบ
                    </button>
                </div>`;
            }

            if (type === 'work') {
                row.innerHTML = `
                <div class="flex flex-col gap-4">
                    <fieldset class="fieldset">
                        <legend class="mb-1 fieldset-legend">ชื่อบริษัท</legend>
                        <input type="text" name="work_experiences[${index}][we_company_name]"
                            class="pl-2 border border-gray-300 input w-72">
                    </fieldset>
                    <div class="flex gap-4">
                        <fieldset class="fieldset">
                        <legend class="mb-1 fieldset-legend">วันที่เริ่ม</legend>
                        <input type="date" name="work_experiences[${index}][we_start_date]"
                            class="pl-2 border border-gray-300 input w-72">
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="mb-1 fieldset-legend">วันที่สิ้นสุด</legend>
                        <input type="date" name="work_experiences[${index}][we_end_date]"
                            class="pl-2 border border-gray-300 input w-72">
                    </fieldset>
                    </div>
                </div>
                <div class="flex justify-end w-full mt-1">
                    <button type="button" class="text-red-600 delete-row">
                        <i class="fa-solid fa-trash"></i> ลบ
                    </button>
                </div>`;
            }

            row.querySelector('.delete-row').addEventListener('click', () => row.remove());

            // Attach date validation for the newly created row
            if (type === 'education') {
                const s = row.querySelector(`input[name="educations[${index}][ed_start_date]"]`);
                const e = row.querySelector(`input[name="educations[${index}][ed_end_date]"]`);
                attachDatePairValidation(s, e);
            }
            if (type === 'work') {
                const s = row.querySelector(`input[name="work_experiences[${index}][we_start_date]"]`);
                const e = row.querySelector(`input[name="work_experiences[${index}][we_end_date]"]`);
                attachDatePairValidation(s, e);
            }
            return row;
        }

        document.getElementById('add-education').addEventListener('click', () => {
            const container = document.getElementById('education-container');
            const index = container.querySelectorAll('.relative').length;
            container.insertBefore(createRow('education', index), document.getElementById('add-education'));
        });

        document.getElementById('add-work').addEventListener('click', () => {
            const container = document.getElementById('work-container');
            const index = container.querySelectorAll('.relative').length;
            container.insertBefore(createRow('work', index), document.getElementById('add-work'));
        });
    </script>
    <script>
        // ลบ Education
        document.querySelectorAll('.delete-education').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                if (confirm('คุณต้องการลบข้อมูลนี้ใช่หรือไม่?')) {
                    fetch(`/admin/profile/education/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.closest('.relative').remove();
                            } else {
                                alert(data.error || 'เกิดข้อผิดพลาด');
                            }
                        });
                }
            });
        });

        // ลบ Work
        document.querySelectorAll('.delete-work').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                if (confirm('คุณต้องการลบข้อมูลนี้ใช่หรือไม่?')) {
                    fetch(`/admin/profile/work/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.closest('.relative').remove();
                            } else {
                                alert(data.error || 'เกิดข้อผิดพลาด');
                            }
                        });
                }
            });
        });
    </script>




@endsection
