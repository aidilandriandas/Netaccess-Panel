@extends('layouts.app')
@section('title', 'Tambah Paket')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <form method="POST" action="{{ route('packages.store') }}" class="space-y-4">
            @csrf
            @include('packages._form')
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm transition-colors">Simpan</button>
                <a href="{{ route('packages.index') }}" class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-6 py-2 rounded-lg text-sm transition-colors">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
