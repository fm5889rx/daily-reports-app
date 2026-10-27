<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>日報を新規作成</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto flex min-h-screen max-w-2xl flex-col justify-center px-4 py-10 sm:px-6">
        <section class="rounded-xl bg-white p-6 shadow-lg sm:p-8">
            <h1 class="mb-6 text-3xl font-bold tracking-tight">日報を新規作成</h1>
            @include('daily-reports._form', [
                'dailyReport' => $dailyReport,
                'action' => route('daily-reports.store'),
                'method' => 'POST',
                'submitLabel' => '作成',
            ])
            <p class="mt-6 text-center">
                <a class="font-medium text-indigo-600 hover:text-indigo-800" href="{{ route('daily-reports.index') }}">一覧に戻る</a>
            </p>
        </section>
    </main>
</body>
</html>
