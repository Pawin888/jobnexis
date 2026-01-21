@extends('layouts.app')

@section('title', 'Create Resume')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">สร้างเรซูเม่</h1>

    <form method="POST" action="{{ route('jobber.resumes.store') }}">
        @csrf
        @include('jobber.resumes._form')

        <button class="mt-6 px-6 py-2 bg-blue-600 text-white rounded">
            บันทึก
        </button>
    </form>
</div>
@endsection
