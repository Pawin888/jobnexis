@props([
    'title' => 'สถิติผู้ใช้งาน',
    // endpoint JSON สำหรับดึงข้อมูล (ใช้ route() ส่งเข้ามา)
    'endpoint',
    // รายชื่อ role ที่จะแสดง (ลำดับ = ลำดับเส้น/ชิป)
    'roles' => ['jobber', 'provider', 'education', 'admin'],
    // label ภาษาไทยของแต่ละ role (ถ้าไม่ส่งมา จะ fallback เป็น ucfirst)
    'labels' => ['jobber' => 'ผู้หางาน', 'provider' => 'ผู้ประกอบการ', 'education' => 'สถานศึกษา', 'admin' => 'แอดมิน'],
    // สีประจำ role
    'palette' => ['jobber' => '#2563eb', 'provider' => '#14b8a6', 'education' => '#8b5cf6', 'admin' => '#f59e0b'],
    // แสดงกราฟสะสม/โดนัทไหม
    'showCumulative' => true,
    'showPie' => true,
])

@php
    $uid = 'stats-' . (string) \Illuminate\Support\Str::uuid(); // กันชนกันหลายอัน
    $roleList = array_values($roles);
    $labelMap = array_merge(array_combine($roleList, $roleList), $labels ?? []);
    $colorMap = array_merge(array_combine($roleList, array_fill(0, count($roleList), '#777')), $palette ?? []);
@endphp

<div id="{{ $uid }}" class="flex flex-col gap-4 ">
    <div class="p-4 space-y-4 border shadow-sm bg-base-100 rounded-2xl">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">{{ $title }}</h2>

            <div class="flex items-center gap-4">
                {{-- role toggles --}}
                <div class="flex items-center gap-4">
                    @foreach ($roleList as $r)
                        <label class="flex items-center gap-2 text-sm">
                            {{ $labelMap[$r] ?? ucfirst($r) }}
                            <input type="checkbox" class="border border-gray-300 toggle toggle-sm" data-role-toggle
                                value="{{ $r }}"
                                {{ $loop->first || $r === 'provider' || $r === 'jobber' || $r === 'education' ? 'checked' : '' }}>
                        </label>
                    @endforeach
                </div>

                {{-- quick range dropdown --}}
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-sm">ช่วงเวลา</div>
                    <ul tabindex="0" class="p-2 shadow dropdown-content menu bg-base-100 rounded-box w-44">
                        <li><a data-range="month_to_date">เดือนนี้</a></li>
                        <li><a data-range="last_3_months">3 เดือนล่าสุด</a></li>
                        <li><a data-range="year_to_date">ปีนี้</a></li>
                        <li><a data-range="custom">กำหนดเอง…</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- custom range --}}
        <div class="items-center hidden gap-4 md:flex" data-custom-range>
            <span class="text-sm text-gray-500">ช่วงเวลา:</span>
            <input type="date" class="border border-gray-300 input input-bordered input-sm" data-from>
            <span class="text-sm">ถึง</span>
            <input type="date" class="border border-gray-300 input input-bordered input-sm" data-to>
            <select class="border border-gray-300 select select-bordered select-sm" data-interval>
                <option value="day">รายวัน</option>
                <option value="week">รายสัปดาห์</option>
                <option value="month">รายเดือน</option>
            </select>
            <button class="px-6 py-2 text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 btn btn-sm"
                data-apply>ตกลง</button>
        </div>

        {{-- Line: ผู้ใช้ใหม่ --}}
        <div class="p-2 bg-base-200 rounded-xl">
            <div class="relative h-[300px]"><canvas data-chart-new></canvas></div>
        </div>

        {{-- legend chips --}}
        <div class="flex flex-wrap gap-3">
            @foreach ($roleList as $r)
                <span class="inline-flex items-center gap-2 px-3 py-1 text-sm rounded-full bg-base-200">
                    <span class="w-3 h-3 rounded-full" style="background: {{ $colorMap[$r] ?? '#999' }}"></span>
                    {{ $labelMap[$r] ?? ucfirst($r) }}
                </span>
            @endforeach
        </div>
    </div>
    {{-- @if ($showCumulative) --}}
    {{-- Line: ผู้ใช้สะสม --}}
    {{-- <div class="p-2 bg-base-200 rounded-xl">
    <div class="pl-2 mb-2 text-sm font-semibold">ผู้ใช้สะสม</div>
    <div class="relative h-[260px]"><canvas data-chart-cumu></canvas></div>
  </div>
  @endif --}}
    <div class="p-4 space-y-4 border shadow-sm bg-base-100 rounded-2xl w-fit">
        @if ($showPie)
            {{-- Doughnut: สัดส่วนตาม role --}}
            <div class="p-2 bg-base-200 rounded-xl">
                <div class="pl-2 mb-2 text-sm font-semibold">สัดส่วนผู้ใช้ตามบทบาท (ในช่วงที่เลือก)</div>
                <div class="grid items-center grid-cols-1 gap-4 md:grid-cols-12">
                    <div class="md:col-span-8">
                        <div class="relative h-[260px]"><canvas data-chart-pie></canvas></div>
                    </div>
                    <div class="flex flex-col gap-2 md:col-span-4" data-summary></div>
                </div>
            </div>
        @endif
    </div>
</div>
@once
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endonce
<script>
    (() => {
        const root = document.getElementById(@json($uid));
        const endpoint = @json($endpoint);
        const ROLES = @json($roleList);
        const LABELS = @json($labelMap);
        const COLORS = @json($colorMap);

        const qs = (sel, el = root) => el.querySelector(sel);
        const qsa = (sel, el = root) => Array.from(el.querySelectorAll(sel));

        const elNew = qs('[data-chart-new]');
        const elCumu = qs('[data-chart-cumu]');
        const elPie = qs('[data-chart-pie]');
        const boxCustom = qs('[data-custom-range]');
        const fromEl = qs('[data-from]');
        const toEl = qs('[data-to]');
        const intervalEl = qs('[data-interval]');
        const applyBtn = qs('[data-apply]');
        const summaryEl = qs('[data-summary]');

        const fmt = (d) => new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
        const now = new Date(),
            toDef = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const fromDef = new Date(toDef.getFullYear(), toDef.getMonth(), 1);

        function setRange(type) {
            boxCustom.classList.add('hidden');
            if (type === 'month_to_date') {
                fromEl.value = fmt(fromDef);
                toEl.value = fmt(toDef);
                intervalEl.value = 'day';
                load();
            } else if (type === 'last_3_months') {
                const from = new Date(toDef.getFullYear(), toDef.getMonth() - 2, 1);
                fromEl.value = fmt(from);
                toEl.value = fmt(toDef);
                intervalEl.value = 'month';
                load();
            } else if (type === 'year_to_date') {
                const from = new Date(toDef.getFullYear(), 0, 1);
                fromEl.value = fmt(from);
                toEl.value = fmt(toDef);
                intervalEl.value = 'month';
                load();
            } else {
                boxCustom.classList.remove('hidden');
                if (!fromEl.value) fromEl.value = fmt(fromDef);
                if (!toEl.value) toEl.value = fmt(toDef);
                if (!intervalEl.value) intervalEl.value = 'day';
            }
        }
        qsa('[data-range]').forEach(el => el.addEventListener('click', () => setRange(el.dataset.range)));
        if (applyBtn) applyBtn.addEventListener('click', load);

        let chartNew, chartCumu, chartPie, idxNew = {},
            idxCumu = {};

        async function load() {
            const params = new URLSearchParams();
            ROLES.forEach(r => params.append('roles[]', r));
            params.set('from', fromEl.value);
            params.set('to', toEl.value);
            params.set('interval', intervalEl.value);

            const res = await fetch(`${endpoint}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const j = await res.json();

            // datasets
            const makeLine = (bucket) => {
                const ds = [];
                const idx = {};
                ROLES.forEach(r => {
                    const row = j[bucket].find(d => d.label === r);
                    if (!row) return;
                    idx[r] = ds.length;
                    ds.push({
                        label: LABELS[r] ?? r,
                        data: row.data,
                        borderColor: COLORS[r] ?? '#777',
                        backgroundColor: COLORS[r] ?? '#777',
                        borderWidth: 3,
                        tension: .35,
                        pointRadius: 2,
                        fill: false
                    });
                });
                return {
                    ds,
                    idx
                };
            };

            const optLine = {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,.7)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 10,
                        cornerRadius: 10
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,.06)'
                        },
                        ticks: {
                            maxRotation: 0,
                            autoSkip: true
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,.06)'
                        },
                        ticks: {
                            precision: 0
                        }
                    }
                }
            };

            // new users
            {
                const {
                    ds,
                    idx
                } = makeLine('datasets');
                idxNew = idx;
                if (!chartNew) chartNew = new Chart(elNew.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: j.labels,
                        datasets: ds
                    },
                    options: optLine
                });
                else {
                    chartNew.data.labels = j.labels;
                    chartNew.data.datasets = ds;
                    chartNew.update();
                }
            }
            // cumulative
            if (elCumu) {
                const {
                    ds,
                    idx
                } = makeLine('cumulative');
                idxCumu = idx;
                if (!chartCumu) chartCumu = new Chart(elCumu.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: j.labels,
                        datasets: ds
                    },
                    options: optLine
                });
                else {
                    chartCumu.data.labels = j.labels;
                    chartCumu.data.datasets = ds;
                    chartCumu.update();
                }
            }
            // pie
            if (elPie) {
                const pieLabels = ROLES.map(r => LABELS[r] ?? r);
                const pieData = ROLES.map(r => j.distribution[r] ?? 0);
                const pieColors = ROLES.map(r => COLORS[r] ?? '#777');
                if (!chartPie) chartPie = new Chart(elPie.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: pieLabels,
                        datasets: [{
                            data: pieData,
                            backgroundColor: pieColors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
                else {
                    chartPie.data.labels = pieLabels;
                    chartPie.data.datasets[0].data = pieData;
                    chartPie.data.datasets[0].backgroundColor = pieColors;
                    chartPie.update();
                }

                // summary chips
                if (summaryEl) {
                    summaryEl.innerHTML = `
          <div class="px-3 py-2 text-sm rounded-xl bg-base-200">รวม: <b>${j.summary.total}</b></div>
          ${ROLES.map(r=>`
            <div class="px-3 py-2 text-sm rounded-xl bg-base-200">
              <span class="inline-block w-2 h-2 mr-2 rounded-full" style="background:${COLORS[r]}"></span>
              ${(LABELS[r] ?? r)}: <b>${j.summary.byRole[r] ?? 0}</b>
            </div>`).join('')}
        `;
                }
            }

            // sync toggles
            qsa('[data-role-toggle]').forEach(el => {
                const r = el.value;
                const i1 = idxNew[r];
                if (i1 != null) chartNew.setDatasetVisibility(i1, el.checked);
                if (chartCumu) {
                    const i2 = idxCumu[r];
                    if (i2 != null) chartCumu.setDatasetVisibility(i2, el.checked);
                }
            });
            chartNew.update();
            if (chartCumu) chartCumu.update();
            if (chartPie) { // ปิด role ใน pie ด้วยการทำค่าเป็น 0
                chartPie.data.datasets[0].data = ROLES.map(r => {
                    const tgl = qsa('[data-role-toggle]').find(x => x.value === r);
                    return tgl && !tgl.checked ? 0 : (j.distribution[r] ?? 0);
                });
                chartPie.update();
            }
        }

        // bind toggles
        qsa('[data-role-toggle]').forEach(el => {
            el.addEventListener('change', () => {
                const r = el.value;
                if (idxNew[r] != null) chartNew.setDatasetVisibility(idxNew[r], el.checked);
                if (idxCumu[r] != null && chartCumu) chartCumu.setDatasetVisibility(idxCumu[r], el
                    .checked);
                chartNew.update();
                if (chartCumu) chartCumu.update();
                if (chartPie) {
                    const data = chartPie.data.datasets[0].data;
                    const i = ROLES.indexOf(r);
                    if (i >= 0) data[i] = el.checked ? data[i] : 0;
                    chartPie.update();
                }
            });
        });

        // first load = เดือนนี้
        setRange('month_to_date');
    })();
</script>
