@extends('layouts.app')

@section('title', 'แดชบอร์ด')

@section('content')
    <div class="flex justify-between gap-24">
        <div class="w-full shadow stat bg-base-100 stats rounded-xl">
            <div class="text-2xl text-blue-600 stat-figure">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="stat-title">ผู้ใช้งานทั้งหมด</div>
            <div class="stat-value">{{ number_format($totalUsers) }} คน</div>
            <div class="stat-desc">ผู้หางาน+ผู้ประกอบการ+สถานศึกษา</div>
        </div>
        <div class="w-full shadow stat bg-base-100 stats rounded-xl">
            <div class="text-2xl text-teal-600 stat-figure">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <div class="stat-title">คอร์สอบรมทั้งหมด</div>
            <div class="stat-value">{{ number_format($totalCourses) }} คอร์ส</div>
            <div class="stat-desc">จำนวนคอร์สที่มีในระบบ</div>
        </div>
        <div class="w-full shadow stat bg-base-100 stats rounded-xl">
            <div class="text-2xl stat-figure text-sky-600">
                <i class="fa-solid fa-scroll"></i>
            </div>
            <div class="stat-title">งานที่เปิดรับ</div>
            <div class="stat-value">{{ number_format($openRecruitments) }} ตำแหน่ง</div>
            <div class="stat-desc">จำนวนตำแหน่งที่ยังเปิดรับสมัคร</div>
        </div>
    </div>
    <div class="mt-4">
        <x-user-stats-board
    :endpoint="route('admin.userStats.data')"
    :roles="['jobber','provider','education']"
    :labels="['jobber'=>'ผู้หางาน','provider'=>'ผู้ประกอบการ','education'=>'สถานศึกษา']"
    :palette="['jobber'=>'#2563eb','provider'=>'#14b8a6','education'=>'#8b5cf6']"
    title="สถิติผู้ใช้งาน"
/>
    </div>

    <div class="mt-6 p-4 bg-base-100 rounded-2xl shadow">
        <h3 class="text-lg font-semibold mb-3">Top 5 Skills (All Exams)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="relative h-[300px]">
                <canvas id="admin-skill-pie"></canvas>
            </div>
            <div id="admin-skill-legend" class="flex flex-col gap-2"></div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            (async function(){
                const res = await fetch("{{ route('admin.skills.data') }}");
                const j = await res.json();
                const el = document.getElementById('admin-skill-pie');
                const colors = ['#60a5fa','#34d399','#f472b6','#f59e0b','#a78bfa'];
                new Chart(el.getContext('2d'), {
                    type: 'doughnut',
                    data: { labels: j.labels, datasets: [{ data: j.data, backgroundColor: colors, borderWidth: 0 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins:{ legend:{ display:false } } }
                });
                const legend = document.getElementById('admin-skill-legend');
                legend.innerHTML = j.labels.map((lb,i)=>`
                    <div class="flex items-center gap-3 p-2 rounded bg-base-200">
                        <span class="inline-block w-3 h-3 rounded" style="background:${colors[i%colors.length]}"></span>
                        <span class="flex-1">${lb}</span>
                        <span class="text-sm text-gray-600">attempts: <b>${j.data[i]||0}</b></span>
                        <span class="text-sm text-gray-600">avg: <b>${(j.avg[i]||0)}%</b></span>
                    </div>
                `).join('');
            })();
        </script>
    </div>
@endsection
