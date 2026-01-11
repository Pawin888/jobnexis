@extends('layouts.app')

@section('title', 'Master Skills Overview')

@section('content')
<div class="max-w-7xl mx-auto py-8">

    <h1 class="text-3xl font-bold mb-6">
        Master Skills Overview
    </h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Skill Groups --}}
        <div class="md:col-span-1 bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold text-lg mb-4">
                Skill Groups
            </h2>

            @if($skillGroups->isNotEmpty())
                <ul class="space-y-2 max-h-[70vh] overflow-y-auto">
                    @foreach($skillGroups as $group)
                        <li
                            class="p-3 rounded-lg border hover:bg-gray-50 transition flex justify-between items-center"
                        >
                            <div class="font-medium text-gray-900">
                                {{ $group->name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $group->skills_count }}
                                skill{{ $group->skills_count !== 1 ? 's' : '' }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500 text-sm">ยังไม่มี Skill Groups</p>
            @endif
        </div>

        {{-- Skills --}}
        <div class="md:col-span-2 bg-white rounded-xl shadow p-6 overflow-x-auto">
            <h2 class="font-semibold text-lg mb-4">
                Skills
            </h2>

            @if($skills->isNotEmpty())
                <table class="w-full text-sm border border-gray-200 rounded-lg">
                    <thead class="bg-gray-50">
                        <tr class="border-b">
                            <th class="text-left py-2 px-3">Skill</th>
                            <th class="text-left py-2 px-3">Group</th>
                            <th class="text-left py-2 px-3">ESCO URI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($skills as $skill)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3 font-medium text-gray-900">
                                    {{ $skill->name ?? '-' }}
                                </td>

                                <td class="py-2 px-3 text-gray-600">
                                    {{ $skill->skillGroups->first()?->name ?? '-' }}
                                </td>

                                <td class="py-2 px-3 text-xs text-gray-500 break-all">
                                    {{ $skill->esco_uri ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $skills->links() }}
                </div>
            @else
                <p class="text-gray-500 text-sm">ยังไม่มี Skills</p>
            @endif
        </div>

    </div>
</div>
@endsection
