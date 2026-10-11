{{-- Validation Errors --}}
@if ($errors->any())
<div class="mb-4 space-y-2" role="alert">
    @foreach ($errors->all() as $error)
    <div class="flex items-start gap-2 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-700">
        <span class="font-bold">خطأ!</span>
        <span>{{ $error }}</span>
    </div>
    @endforeach
</div>
@endif

{{-- Success Message --}}
@if (session('success'))
<div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-700" role="status">
    {{ session('success') }}
</div>
@endif

{{-- General Message --}}
@if (session('message'))
<div class="mb-4 rounded-lg border border-blue-300 bg-blue-50 px-4 py-3 text-blue-700" role="status">
    {{ session('message') }}
</div>
@endif