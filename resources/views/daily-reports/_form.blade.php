<form class="space-y-5" action="{{ $action }}" method="POST">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700" for="reported_at">報告日</label>
        <input
            class="block w-full rounded-lg border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            type="date"
            id="reported_at"
            name="reported_at"
            value="{{ old('reported_at', $dailyReport->reported_at?->format('Y-m-d')) }}"
        >
        @error('reported_at')
            <p class="error text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700" for="title">タイトル</label>
        <input
            class="block w-full rounded-lg border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            type="text"
            id="title"
            name="title"
            value="{{ old('title', $dailyReport->title) }}"
        >
        @error('title')
            <p class="error text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700" for="content">内容</label>
        <textarea
            class="block min-h-32 w-full rounded-lg border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            id="content"
            name="content"
        >{{ old('content', $dailyReport->content) }}</textarea>
        @error('content')
            <p class="error text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-medium text-slate-700" for="status">ステータス</label>
        <select
            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            id="status"
            name="status"
        >
            <option value="draft" @selected(old('status', $dailyReport->status ?? 'draft') === 'draft')>下書き</option>
            <option value="submitted" @selected(old('status', $dailyReport->status ?? 'draft') === 'submitted')>提出済</option>
        </select>
        @error('status')
            <p class="error text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <button
        class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        type="submit"
    >{{ $submitLabel }}</button>
</form>
