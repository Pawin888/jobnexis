@extends('layouts.app')

@section('title', 'Edit Resume')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">แก้ไขเรซูเม่</h1>

    <form method="POST" action="{{ route('jobber.resumes.update', $resume) }}">
        @csrf
        @method('PUT')

        @include('jobber.resumes._form')

        <button class="mt-6 px-6 py-2 bg-green-600 text-white rounded">
            อัปเดต
        </button>
    </form>
    
    <form method="POST" action="{{ route('jobber.resumes.destroy', $resume) }}" 
        onsubmit="return confirm('คุณแน่ใจหรือว่าต้องการลบเรซูเม่นี้?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="mt-4 px-6 py-2 bg-red-600 text-white rounded">
            ลบเรซูเม่
        </button>
    </form>
</div>
@endsection
