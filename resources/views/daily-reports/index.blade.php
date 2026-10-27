<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日報一覧</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-4 py-10 sm:px-6 lg:px-8">
        <section class="rounded-xl bg-white p-6 shadow-lg sm:p-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-3xl font-bold tracking-tight">日報一覧</h1>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-600">ログイン中: {{ auth()->user()->name }}</span>
                    <a
                        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700"
                        href="{{ route('daily-reports.create') }}"
                    >日報を新規作成</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="font-medium text-slate-600 hover:text-slate-900" type="submit">ログアウト</button>
                    </form>
                </div>
            </div>

            @if (session('success'))
                <p class="mb-5 rounded-lg bg-green-50 p-4 text-green-700">{{ session('success') }}</p>
            @endif

            @if ($dailyReports->isEmpty())
                <p class="rounded-lg bg-slate-50 p-6 text-center text-slate-600">日報はありません。</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-slate-200 text-sm text-slate-500">
                                <th class="px-4 py-3 font-medium">報告日</th>
                                <th class="px-4 py-3 font-medium">タイトル</th>
                                <th class="px-4 py-3 font-medium">内容</th>
                                <th class="px-4 py-3 font-medium">ステータス</th>
                                <th class="px-4 py-3 font-medium">報告者</th>
                                <th class="px-4 py-3 font-medium">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dailyReports as $dailyReport)
                                <tr class="border-b border-slate-100">
                                    <td class="whitespace-nowrap px-4 py-4">{{ $dailyReport->reported_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-4 font-medium">{{ $dailyReport->title }}</td>
                                    <td class="max-w-sm px-4 py-4">{{ $dailyReport->content }}</td>
                                    <td class="whitespace-nowrap px-4 py-4">{{ $dailyReport->status === 'submitted' ? '提出済' : '下書き' }}</td>
                                    <td class="whitespace-nowrap px-4 py-4">{{ $dailyReport->user?->name ?? '不明' }}</td>
                                    <td class="whitespace-nowrap px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            @can('update', $dailyReport)
                                                <a
                                                    class="font-medium text-indigo-600 hover:text-indigo-800"
                                                    href="{{ route('daily-reports.edit', $dailyReport) }}"
                                                >編集</a>
                                            @endcan
                                            @can('delete', $dailyReport)
                                                <form
                                                    action="{{ route('daily-reports.destroy', $dailyReport) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('この日報を削除してもよろしいですか？')"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="font-medium text-red-600 hover:text-red-800" type="submit">削除</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </main>
</body>
</html>
