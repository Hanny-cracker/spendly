<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <title>Spendly — Take control of your money</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-white text-gray-900 antialiased">

    <div class="min-h-screen">

        {{-- ============================================================
             HERO / LANDING
             ============================================================ --}}
        <section class="relative overflow-hidden bg-[#052f2b] text-white">

            {{-- Background glow --}}
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -left-40 top-10 h-[420px] w-[420px] rounded-full bg-emerald-400/10 blur-3xl"></div>
                <div class="absolute right-[-120px] top-[-100px] h-[520px] w-[520px] rounded-full bg-emerald-400/15 blur-3xl"></div>
                <div class="absolute bottom-[-160px] left-1/2 h-[320px] w-[620px] -translate-x-1/2 rounded-full bg-teal-300/10 blur-3xl"></div>
            </div>

            {{-- Navigation --}}
            <header class="relative z-20">
                <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 sm:px-6 lg:px-8">

                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-400 text-lg font-black text-[#052f2b] shadow-lg shadow-emerald-400/20">
                            S
                        </div>

                        <span class="text-2xl font-bold tracking-tight">
                            Spendly
                        </span>
                    </a>

                    <div class="flex items-center gap-2 sm:gap-3">
                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="rounded-xl bg-emerald-400 px-5 py-2.5 text-sm font-semibold text-[#052f2b] transition hover:bg-emerald-300"
                            >
                                Dashboard
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="hidden rounded-xl px-4 py-2.5 text-sm font-semibold text-white/90 transition hover:bg-white/10 sm:inline-flex"
                            >
                                Sign in
                            </a>

                            <a
                                href="{{ route('register') }}"
                                class="rounded-xl bg-emerald-400 px-5 py-2.5 text-sm font-semibold text-[#052f2b] shadow-lg shadow-emerald-400/20 transition hover:bg-emerald-300"
                            >
                                Get Started
                            </a>
                        @endauth
                    </div>
                </div>
            </header>

            {{-- Hero content --}}
            <div class="relative z-10 mx-auto grid max-w-7xl items-center gap-14 px-5 pb-16 pt-10 sm:px-6 sm:pb-20 lg:grid-cols-[0.92fr_1.08fr] lg:px-8 lg:pb-24 lg:pt-16">

                {{-- Left copy --}}
                <div>

                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-300/30 bg-white/5 px-4 py-2 text-xs font-medium text-emerald-100">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Your money, a clearer tomorrow
                    </div>

                    <h1 class="mt-8 max-w-xl text-5xl font-bold leading-[1.05] tracking-tight sm:text-6xl lg:text-7xl">
                        Take control
                        <br>
                        of your
                        <span class="text-emerald-300">money</span>
                    </h1>

                    <p class="mt-6 max-w-xl text-base leading-7 text-white/75 sm:text-lg">
                        Spendly helps you track your income, expenses, accounts and savings in one simple place.
                        Understand your finances, make better decisions, and build the future you want.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        @guest
                            <a
                                href="{{ route('register') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-400 px-6 py-3.5 text-sm font-bold text-[#052f2b] shadow-xl shadow-emerald-400/20 transition hover:-translate-y-0.5 hover:bg-emerald-300"
                            >
                                Get Started Free
                                <span aria-hidden="true">→</span>
                            </a>

                            <a
                                href="#features"
                                class="inline-flex items-center justify-center rounded-2xl border border-emerald-300/70 px-6 py-3.5 text-sm font-semibold text-emerald-200 transition hover:bg-white/10"
                            >
                                See How It Works
                            </a>
                        @else
                            <a
                                href="{{ route('dashboard') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-400 px-6 py-3.5 text-sm font-bold text-[#052f2b]"
                            >
                                Open Spendly
                                <span aria-hidden="true">→</span>
                            </a>
                        @endguest
                    </div>

                    <div class="mt-10 grid max-w-xl grid-cols-3 gap-4 border-t border-white/10 pt-8">

                        <div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-400/10 text-emerald-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l7 3v5c0 5-3 8-7 10-4-2-7-5-7-10V6l7-3z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.5 12l1.7 1.7L15 10"/>
                                </svg>
                            </div>
                            <p class="mt-3 text-sm font-semibold">Secure & Private</p>
                            <p class="mt-1 text-xs text-white/55">Your data is safe</p>
                        </div>

                        <div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-400/10 text-emerald-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 2L4 14h7l-1 8 9-12h-7l1-8z"/>
                                </svg>
                            </div>
                            <p class="mt-3 text-sm font-semibold">Simple to Use</p>
                            <p class="mt-1 text-xs text-white/55">Get started in minutes</p>
                        </div>

                        <div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-400/10 text-emerald-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 19V9m7 10V5m7 14v-7"/>
                                </svg>
                            </div>
                            <p class="mt-3 text-sm font-semibold">Powerful Insights</p>
                            <p class="mt-1 text-xs text-white/55">Make smarter decisions</p>
                        </div>

                    </div>
                </div>

                {{-- =====================================================
                     ACTUAL SPENDLY APP PREVIEW
                     Uses the same light UI language as the real app:
                     white sidebar, stone-100 canvas, gray borders,
                     rounded-2xl cards, green/red/blue financial states.
                     ===================================================== --}}
                <div class="relative">

                    <div class="absolute -inset-8 -z-10 rounded-full bg-emerald-300/10 blur-3xl"></div>

                    <div class="overflow-hidden rounded-[28px] border border-white/15 bg-[#111827] p-2 shadow-2xl shadow-black/40">

                        {{-- Browser chrome --}}
                        <div class="flex h-9 items-center gap-2 rounded-t-[21px] bg-[#101820] px-4">
                            <span class="h-2.5 w-2.5 rounded-full bg-red-400"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-300"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                        </div>

                        <div class="flex min-h-[570px] overflow-hidden rounded-b-[21px] bg-stone-100">

                            {{-- Real app sidebar --}}
                            <aside class="hidden w-44 shrink-0 border-r border-gray-200 bg-white lg:flex lg:flex-col">

                                <div class="flex h-16 items-center px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-600 text-xs font-bold text-white">
                                            S
                                        </div>
                                        <span class="text-lg font-semibold tracking-tight text-gray-900">
                                            Spendly
                                        </span>
                                    </div>
                                </div>

                                <nav class="flex-1 px-3 py-3">
                                    <p class="mb-2 px-3 text-[9px] font-semibold uppercase tracking-wider text-gray-400">
                                        Application
                                    </p>

                                    <div class="space-y-1 text-[11px] font-medium text-gray-600">

                                        <div class="flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2.5 text-emerald-700">
                                            <span>⌂</span>
                                            <span>Dashboard</span>
                                        </div>

                                        <div class="flex items-center gap-2 rounded-xl px-3 py-2.5">
                                            <span>◫</span>
                                            <span>Analytics</span>
                                        </div>

                                        <div class="flex items-center gap-2 rounded-xl px-3 py-2.5">
                                            <span>↔</span>
                                            <span>Transactions</span>
                                        </div>

                                        <div class="flex items-center gap-2 rounded-xl px-3 py-2.5">
                                            <span>▣</span>
                                            <span>Accounts</span>
                                        </div>

                                        <div class="flex items-center gap-2 rounded-xl px-3 py-2.5">
                                            <span>◒</span>
                                            <span>Budgets</span>
                                        </div>

                                        <div class="flex items-center gap-2 rounded-xl px-3 py-2.5">
                                            <span>▦</span>
                                            <span>Categories</span>
                                        </div>

                                        <div class="flex items-center gap-2 rounded-xl px-3 py-2.5">
                                            <span>◎</span>
                                            <span>Goals</span>
                                        </div>

                                        <div class="flex items-center gap-2 rounded-xl px-3 py-2.5">
                                            <span>↻</span>
                                            <span>Recurring</span>
                                        </div>

                                        <div class="flex items-center gap-2 rounded-xl px-3 py-2.5">
                                            <span>▤</span>
                                            <span>Reports</span>
                                        </div>
                                    </div>

                                    <div class="my-4 border-t border-gray-200"></div>

                                    <p class="mb-2 px-3 text-[9px] font-semibold uppercase tracking-wider text-gray-400">
                                        System
                                    </p>

                                    <div class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-[11px] font-medium text-gray-600">
                                        <span>⚙</span>
                                        <span>Settings</span>
                                    </div>
                                </nav>

                                <div class="border-t border-gray-200 p-3">
                                    <div class="flex items-center gap-2 rounded-xl bg-gray-50 p-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-[10px] font-bold text-white">
                                            U
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-[10px] font-semibold text-gray-900">User</p>
                                            <p class="truncate text-[9px] text-gray-400">user@email.com</p>
                                        </div>
                                    </div>
                                </div>
                            </aside>

                            {{-- Main application --}}
                            <div class="min-w-0 flex-1">

                                {{-- Actual app header --}}
                                <div class="flex h-14 items-center justify-between border-b border-gray-200 bg-white/95 px-4">
                                    <div>
                                        <p class="text-[11px] text-gray-400">Dashboard</p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-gray-100 text-xs text-gray-600">
                                            🔔
                                        </div>
                                        <div class="hidden h-8 items-center rounded-xl bg-gray-100 px-3 text-[10px] font-medium text-gray-700 sm:flex">
                                            Hanniel
                                        </div>
                                    </div>
                                </div>

                                <div class="p-3 sm:p-4">

                                    {{-- Greeting --}}
                                    <div class="mb-4">
                                        <p class="text-[10px] text-gray-500">Good morning</p>
                                        <h2 class="mt-0.5 text-sm font-semibold text-gray-900">
                                            Financial Overview
                                        </h2>
                                    </div>

                                    {{-- Real dashboard financial cards --}}
                                    <div class="grid grid-cols-2 gap-2 xl:grid-cols-4">

                                        <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <p class="text-[9px] font-medium text-gray-500">Income</p>
                                                    <p class="mt-2 text-sm font-bold text-gray-900">450,000</p>
                                                </div>

                                                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-green-100">
                                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-6 6m6-6l6 6"/>
                                                    </svg>
                                                </div>
                                            </div>

                                            <div class="mt-3 flex items-center justify-between text-[8px]">
                                                <span class="text-gray-400">8 transactions</span>
                                                <span class="font-medium text-green-600">Avg. 56k</span>
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <p class="text-[9px] font-medium text-gray-500">Expenses</p>
                                                    <p class="mt-2 text-sm font-bold text-gray-900">175,000</p>
                                                </div>

                                                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-red-100">
                                                    <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m0 0l6-6m-6 6l-6-6"/>
                                                    </svg>
                                                </div>
                                            </div>

                                            <div class="mt-3 flex items-center justify-between text-[8px]">
                                                <span class="text-gray-400">13 transactions</span>
                                                <span class="font-medium text-red-600">Avg. 13k</span>
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <p class="text-[9px] font-medium text-gray-500">Net Cash Flow</p>
                                                    <p class="mt-2 text-sm font-bold text-green-600">275,000</p>
                                                </div>

                                                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-green-100">
                                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18m-6-6l6 6-6 6"/>
                                                    </svg>
                                                </div>
                                            </div>

                                            <div class="mt-3 flex items-center justify-between text-[8px]">
                                                <span class="text-gray-400">Income − Expenses</span>
                                                <span class="font-medium text-green-600">Positive</span>
                                            </div>
                                        </div>

                                        <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <p class="text-[9px] font-medium text-gray-500">Savings Rate</p>
                                                    <p class="mt-2 text-sm font-bold text-gray-900">61.1%</p>
                                                </div>

                                                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-100">
                                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10v2m0 8v2m9-6a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                            </div>

                                            <div class="mt-3 flex items-center justify-between text-[8px]">
                                                <span class="text-gray-400">Saved</span>
                                                <span class="font-medium text-blue-600">275,000</span>
                                            </div>
                                        </div>

                                    </div>

                                    {{-- Accounts --}}
                                    <div class="mt-3 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">

                                        <div class="mb-3 flex items-center justify-between">
                                            <div>
                                                <p class="text-[10px] font-semibold text-gray-900">Accounts</p>
                                                <p class="text-[8px] text-gray-400">Your balances</p>
                                            </div>
                                            <span class="text-[8px] font-semibold text-emerald-600">View all</span>
                                        </div>

                                        <div class="flex gap-2 overflow-hidden">
                                            <div class="min-w-[42%] rounded-2xl bg-gray-950 p-3 text-white">
                                                <p class="text-[8px] text-gray-400">Bank account</p>
                                                <p class="mt-4 text-sm font-semibold">720,000</p>
                                                <p class="mt-1 text-[8px] text-gray-400">Main account</p>
                                            </div>

                                            <div class="min-w-[42%] rounded-2xl bg-emerald-600 p-3 text-white">
                                                <p class="text-[8px] text-emerald-100">Mobile money</p>
                                                <p class="mt-4 text-sm font-semibold">315,000</p>
                                                <p class="mt-1 text-[8px] text-emerald-100">MTN MoMo</p>
                                            </div>

                                            <div class="min-w-[42%] rounded-2xl bg-sky-600 p-3 text-white">
                                                <p class="text-[8px] text-sky-100">Savings</p>
                                                <p class="mt-4 text-sm font-semibold">210,000</p>
                                                <p class="mt-1 text-[8px] text-sky-100">Savings</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bottom dashboard row --}}
                                    <div class="mt-3 grid gap-3 xl:grid-cols-[1.05fr_0.95fr]">

                                        {{-- Transactions --}}
                                        <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                                            <div class="mb-2 flex items-center justify-between">
                                                <div>
                                                    <p class="text-[10px] font-semibold text-gray-900">Recent Transactions</p>
                                                    <p class="text-[8px] text-gray-400">Latest account activity</p>
                                                </div>
                                                <span class="text-[8px] font-semibold text-emerald-600">View all</span>
                                            </div>

                                            <div class="space-y-1">

                                                <div class="flex items-center justify-between rounded-xl px-1 py-2">
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-orange-100 text-[10px]">🍔</div>
                                                        <div>
                                                            <p class="text-[9px] font-semibold text-gray-900">Lunch</p>
                                                            <p class="text-[8px] text-gray-400">Food · Today</p>
                                                        </div>
                                                    </div>
                                                    <span class="text-[9px] font-semibold text-gray-800">-4,500</span>
                                                </div>

                                                <div class="flex items-center justify-between rounded-xl px-1 py-2">
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-green-100 text-[10px]">↗</div>
                                                        <div>
                                                            <p class="text-[9px] font-semibold text-gray-900">Freelance Payment</p>
                                                            <p class="text-[8px] text-gray-400">Income · Yesterday</p>
                                                        </div>
                                                    </div>
                                                    <span class="text-[9px] font-semibold text-green-600">+125,000</span>
                                                </div>

                                                <div class="flex items-center justify-between rounded-xl px-1 py-2">
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 text-[10px]">🚕</div>
                                                        <div>
                                                            <p class="text-[9px] font-semibold text-gray-900">Transport</p>
                                                            <p class="text-[8px] text-gray-400">Transport · Sep 8</p>
                                                        </div>
                                                    </div>
                                                    <span class="text-[9px] font-semibold text-gray-800">-2,000</span>
                                                </div>

                                            </div>
                                        </div>

                                        {{-- Financial health --}}
                                        <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">

                                            <div>
                                                <p class="text-[10px] font-semibold text-gray-900">Financial Health</p>
                                                <p class="text-[8px] text-gray-400">Overall performance</p>
                                            </div>

                                            <div class="mt-4 flex items-center justify-between">
                                                <div>
                                                    <p class="text-[8px] text-gray-400">Health Score</p>
                                                    <p class="mt-1 text-xl font-bold text-gray-900">82/100</p>
                                                </div>

                                                <div class="rounded-full bg-green-100 px-3 py-1.5 text-[9px] font-semibold text-green-700">
                                                    Good
                                                </div>
                                            </div>

                                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-gray-100">
                                                <div class="h-full w-[82%] rounded-full bg-emerald-500"></div>
                                            </div>

                                            <div class="mt-4 grid grid-cols-3 gap-2">
                                                <div class="rounded-xl bg-gray-50 p-2">
                                                    <p class="text-[7px] text-gray-400">Income</p>
                                                    <p class="mt-1 text-[9px] font-semibold text-gray-800">Strong</p>
                                                </div>

                                                <div class="rounded-xl bg-gray-50 p-2">
                                                    <p class="text-[7px] text-gray-400">Spending</p>
                                                    <p class="mt-1 text-[9px] font-semibold text-gray-800">Stable</p>
                                                </div>

                                                <div class="rounded-xl bg-gray-50 p-2">
                                                    <p class="text-[7px] text-gray-400">Savings</p>
                                                    <p class="mt-1 text-[9px] font-semibold text-gray-800">Good</p>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Phone-style mobile preview --}}
                    <div class="absolute -bottom-8 -right-3 hidden w-[150px] overflow-hidden rounded-[28px] border-[5px] border-[#111827] bg-stone-100 shadow-2xl shadow-black/50 xl:block">

                        <div class="h-4 bg-[#111827]"></div>

                        <div class="bg-white px-3 pb-3 pt-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-600 text-[8px] font-bold text-white">
                                        S
                                    </div>
                                    <span class="text-[9px] font-semibold text-gray-900">Spendly</span>
                                </div>
                                <span class="text-[9px]">☰</span>
                            </div>
                        </div>

                        <div class="space-y-2 p-3">
                            <div>
                                <p class="text-[7px] text-gray-400">Total balance</p>
                                <p class="mt-1 text-[14px] font-bold text-gray-900">1,245,000</p>
                            </div>

                            <div class="rounded-xl border border-gray-200 bg-white p-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[7px] text-gray-500">Income</span>
                                    <span class="text-[8px] font-semibold text-green-600">450,000</span>
                                </div>
                            </div>

                            <div class="rounded-xl border border-gray-200 bg-white p-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[7px] text-gray-500">Expenses</span>
                                    <span class="text-[8px] font-semibold text-red-600">175,000</span>
                                </div>
                            </div>

                            <div class="rounded-xl border border-gray-200 bg-white p-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[7px] text-gray-500">Savings rate</span>
                                    <span class="text-[8px] font-semibold text-blue-600">61.1%</span>
                                </div>
                            </div>

                            <div class="pt-1">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-[8px] font-semibold text-gray-900">Transactions</span>
                                    <span class="text-[7px] text-emerald-600">See all</span>
                                </div>

                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between rounded-lg bg-white p-2">
                                        <span class="text-[7px] text-gray-600">Food</span>
                                        <span class="text-[7px] font-semibold">-4,500</span>
                                    </div>

                                    <div class="flex items-center justify-between rounded-lg bg-white p-2">
                                        <span class="text-[7px] text-gray-600">Freelance</span>
                                        <span class="text-[7px] font-semibold text-green-600">+125,000</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ============================================================
             FEATURES
             ============================================================ --}}
        <section id="features" class="bg-white">
            <div class="mx-auto max-w-7xl px-5 py-20 sm:px-6 lg:px-8 lg:py-24">

                <div class="mx-auto max-w-3xl text-center">
                    <div class="inline-flex rounded-full bg-emerald-50 px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                        Why Spendly
                    </div>

                    <h2 class="mt-5 text-3xl font-bold tracking-tight text-gray-950 sm:text-4xl">
                        Everything you need to manage your money
                    </h2>

                    <p class="mt-4 text-base text-gray-500 sm:text-lg">
                        Simple tools. Powerful insights. A better financial future.
                    </p>
                </div>

                <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                    <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                            <span class="text-xl">▣</span>
                        </div>
                        <h3 class="mt-5 text-base font-bold text-gray-950">Multiple Accounts</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">
                            Track cash, bank accounts and mobile money balances separately.
                        </p>
                    </article>

                    <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-violet-700">
                            <span class="text-xl">◫</span>
                        </div>
                        <h3 class="mt-5 text-base font-bold text-gray-950">Smart Analytics</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">
                            Understand your income, expenses, cash flow, savings and financial health.
                        </p>
                    </article>

                    <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-100 text-orange-600">
                            <span class="text-xl">↻</span>
                        </div>
                        <h3 class="mt-5 text-base font-bold text-gray-950">Recurring Transactions</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">
                            Automatically keep repeating income and expenses on schedule.
                        </p>
                    </article>

                    <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                            <span class="text-xl">◎</span>
                        </div>
                        <h3 class="mt-5 text-base font-bold text-gray-950">Set and Reach Goals</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">
                            Save toward the things that matter and see your progress clearly.
                        </p>
                    </article>

                </div>
            </div>
        </section>

        {{-- ============================================================
             CTA
             ============================================================ --}}
        <section class="bg-white px-5 pb-20 sm:px-6 lg:px-8">

            <div class="mx-auto max-w-7xl overflow-hidden rounded-[30px] bg-gradient-to-br from-emerald-50 via-[#ecfbf4] to-emerald-100 px-6 py-14 text-center sm:px-10 lg:py-16">

                <div class="inline-flex rounded-full bg-emerald-200/70 px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-emerald-800">
                    Get Started Today
                </div>

                <h2 class="mx-auto mt-5 max-w-2xl text-3xl font-bold tracking-tight text-gray-950 sm:text-4xl">
                    A clearer, brighter financial future starts here.
                </h2>

                <p class="mx-auto mt-4 max-w-xl text-sm leading-6 text-gray-600 sm:text-base">
                    Join Spendly and take the first step toward better money management.
                </p>

                @guest
                    <div class="mt-8">
                        <a
                            href="{{ route('register') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-7 py-3.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/15 transition hover:bg-emerald-700"
                        >
                            Create Your Account
                            <span aria-hidden="true">→</span>
                        </a>

                        <p class="mt-4 text-sm text-gray-600">
                            Already have an account?
                            <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">
                                Sign in
                            </a>
                        </p>
                    </div>
                @endguest

            </div>
        </section>

        {{-- Footer --}}
        <footer class="bg-[#052f2b] text-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-6 px-5 py-8 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">

                <div>
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-400 text-xs font-black text-[#052f2b]">
                            S
                        </div>
                        <span class="text-lg font-bold">Spendly</span>
                    </div>

                    <p class="mt-2 text-xs text-white/55">
                        Take control of your money.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-5 text-xs text-white/65">
                    <span>Privacy</span>
                    <span>Terms</span>
                    <span>Contact</span>
                    <span>© {{ date('Y') }} Spendly</span>
                </div>
            </div>
        </footer>

    </div>

</body>
</html>
