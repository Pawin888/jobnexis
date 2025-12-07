@props(['certificate'])
@php
    use Illuminate\Support\Facades\Storage;
    $raw = $certificate->cer_image_path;
    $isUrl = filter_var($raw, FILTER_VALIDATE_URL);
    $fileUrl = $isUrl ? $raw : Storage::url($raw);
@endphp

<div class="shadow-xl card card-side bg-base-100">
    <figure>
        <img src="{{ $fileUrl }}" class="w-[247px] h-[147px] object-cover" />
    </figure>
    <div class="card-body">
        <h1 class="card-title">{{ $certificate->cer_name }}</h1>
        <p class="text-xs">{{ $certificate->cer_institute_name }} • รหัส: {{ $certificate->cer_ref_number }}</p>
        <div>
            <div
                class="badge {{ $certificate->cer_publiced ? 'text-green-600 bg-green-200' : 'text-gray-600 bg-gray-200' }}">
                {{ $certificate->cer_publiced ? 'เผยแพร่' : 'ซ่อน' }}
            </div>
        </div>
    </div>
    <div class="justify-end mr-10">
        <div class="flex items-center justify-center h-full gap-4">
            <form method="POST" action="{{ route('certificates.toggle', $certificate->cer_id) }}">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="flex items-center justify-center w-10 h-10 border rounded-2xl hover:bg-blue-600 hover:text-white">
                    <i class="fa-solid fa-bullhorn"></i>
                </button>
            </form>

            <a href="{{ $fileUrl }}" download target="_blank" rel="noopener"
                class="flex items-center justify-center w-10 h-10 border rounded-2xl hover:bg-blue-600 hover:text-white">
                <i class="fa-solid fa-download"></i>
            </a>

            @if (!$certificate->cer_from_lesson)
                <form method="POST" action="{{ route('certificates.destroy', $certificate->cer_id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="flex items-center justify-center w-10 h-10 text-red-600 border rounded-2xl hover:bg-red-600 hover:text-white">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
