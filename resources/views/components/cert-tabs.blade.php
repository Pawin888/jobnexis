@php
    $sortFn = fn($list) => $list->sortBy([
        ['cer_publiced', 'desc'], // เผยแพร่ก่อน
        ['cer_name', 'asc'], // แล้วเรียงตามชื่อ
    ]);
@endphp

<div class="w-full gap-4 tabs tabs-border">
    <!-- Search -->

    <label class="w-full border border-gray-300 input ">
        <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.3-4.3"></path>
            </g>
        </svg>
        <input type="search" class="w-full input" required placeholder="ค้นหาใบประกาศ..." id="cert-search" />
    </label>
    <!-- Tab: ทั้งหมด -->
    <input type="radio" name="cert_tabs" class="tab" aria-label="ทั้งหมด" checked="checked" />
    <div class="p-4 tab-content border-base-300 bg-base-100">
        <div class="flex flex-col w-full gap-4 mt-4 cert-list">
            @forelse ($sortFn($certificates) as $c)
                <x-cert-card :certificate="$c" />
            @empty
                <p class="text-gray-500">ไม่มีข้อมูล</p>
            @endforelse
        </div>
    </div>

    <!-- Tab: หลักสูตร -->
    <input type="radio" name="cert_tabs" class="tab" aria-label="หลักสูตร" />
    <div class="p-4 tab-content border-base-300 bg-base-100">
        <div class="flex flex-col w-full gap-4 mt-4 cert-list">
            @forelse ($sortFn($certificates->where('cer_from_lesson', true)) as $c)
                <x-cert-card :certificate="$c" />
            @empty
                <p class="text-gray-500">ไม่มีข้อมูลหลักสูตร</p>
            @endforelse
        </div>
    </div>

    <!-- Tab: นำเข้า -->
    <input type="radio" name="cert_tabs" class="tab" aria-label="นำเข้า" />
    <div class="p-4 tab-content border-base-300 bg-base-100">
        <div class="flex flex-col w-full gap-4 mt-4 cert-list">
            @forelse ($sortFn($certificates->where('cer_from_lesson', false)) as $c)
                <x-cert-card :certificate="$c" />
            @empty
                <p class="text-gray-500">ไม่มีข้อมูลนำเข้า</p>
            @endforelse
        </div>
    </div>
</div>
<script>
    document.getElementById('cert-search').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('.cert-list .card').forEach(card => {
            const text = card.innerText.toLowerCase();
            card.style.display = text.includes(query) ? '' : 'none';
        });
    });
</script>
