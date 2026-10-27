<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン・新規登録</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-10 sm:px-6">
        <section class="rounded-xl bg-white p-6 shadow-lg sm:p-8">
            <h1 class="mb-2 text-3xl font-bold tracking-tight">日報アプリ</h1>
            <p class="mb-6 text-sm text-slate-600">ログインまたは新規登録してください。</p>

            @if ($errors->any())
                <ul class="mb-5 space-y-1 rounded-lg bg-red-50 p-4 text-sm text-red-700" role="alert">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form action="{{ route('login.store') }}" method="POST" class="space-y-5">
                @csrf

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700" for="email">メールアドレス</label>
                    <input
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus
                    >
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700" for="password">パスワード</label>
                    <input
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700" for="password_confirmation">パスワード確認</label>
                    <input
                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                    >
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button
                        class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        type="submit"
                    >ログイン</button>
                    <button
                        class="w-full rounded-lg border border-indigo-600 px-4 py-2.5 font-semibold text-indigo-700 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        type="submit"
                        formaction="{{ route('register.store') }}"
                    >新規登録</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
