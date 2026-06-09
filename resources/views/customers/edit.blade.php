@extends('layouts.app')
@section('title', 'Edit Customer')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-4">
            @csrf @method('PUT')
            @include('customers._form', ['customer' => $customer])
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm transition-colors">Update</button>
                <a href="{{ route('customers.show', $customer) }}" class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-6 py-2 rounded-lg text-sm transition-colors">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
