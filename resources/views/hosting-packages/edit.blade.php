@extends('layouts.app')
@section('title', 'Edit Paket Hosting')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <form method="POST" action="{{ route('hosting-packages.update', $package) }}">
            @csrf @method('PUT')
            @include('hosting-packages._form')
            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm transition-colors">Perbarui</button>
                <a href="{{ route('hosting-packages.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 text-sm">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
