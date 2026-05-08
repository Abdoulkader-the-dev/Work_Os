<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Diagnostic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-neutral-950 text-white">
    <main class="mx-auto flex min-h-screen max-w-5xl items-center px-6 py-16">
        <section class="w-full overflow-hidden rounded-[28px] border border-white/10 bg-white/5 shadow-2xl shadow-black/30 backdrop-blur">
            <div class="grid gap-10 p-8 md:grid-cols-[1.3fr_0.7fr] md:p-12">
                <div class="space-y-6">
                    <p class="inline-flex rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-cyan-200">
                        Laravel + Vite + Tailwind v4
                    </p>
                    <div class="space-y-4">
                        <h1 class="text-4xl font-semibold tracking-tight text-white md:text-6xl">
                            UniPod is rendering correctly.
                        </h1>
                        <p class="max-w-2xl text-base leading-7 text-neutral-300 md:text-lg">
                            Si cette page s'affiche avec le fond sombre, les cartes translucides et les styles Tailwind,
                            alors Laravel, Vite et Tailwind v4 fonctionnent ensemble sur ce projet.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <article class="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-4">
                            <p class="text-sm text-emerald-200">Framework</p>
                            <p class="mt-2 text-lg font-medium">Laravel 13</p>
                        </article>
                        <article class="rounded-2xl border border-sky-400/20 bg-sky-400/10 p-4">
                            <p class="text-sm text-sky-200">Bundler</p>
                            <p class="mt-2 text-lg font-medium">Vite 8</p>
                        </article>
                        <article class="rounded-2xl border border-fuchsia-400/20 bg-fuchsia-400/10 p-4">
                            <p class="text-sm text-fuchsia-200">CSS</p>
                            <p class="mt-2 text-lg font-medium">Tailwind v4</p>
                        </article>
                    </div>
                </div>

                <aside class="rounded-3xl border border-white/10 bg-black/20 p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-neutral-400">Checks</h2>
                    <ul class="mt-5 space-y-3 text-sm text-neutral-200">
                        <li class="rounded-xl border border-white/8 bg-white/5 px-4 py-3">
                            APP_URL: {{ config('app.url') }}
                        </li>
                        <li class="rounded-xl border border-white/8 bg-white/5 px-4 py-3">
                            Environment: {{ app()->environment() }}
                        </li>
                        <li class="rounded-xl border border-white/8 bg-white/5 px-4 py-3">
                            PHP: {{ PHP_VERSION }}
                        </li>
                    </ul>
                </aside>
            </div>
        </section>
    </main>
</body>
</html>
