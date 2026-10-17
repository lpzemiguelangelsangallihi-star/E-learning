@if (session('success'))
    <div class="mb-6 flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
        <i class="fa-solid fa-circle-check mt-0.5"></i>
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-6 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
        {{ session('error') }}
    </div>
@endif
