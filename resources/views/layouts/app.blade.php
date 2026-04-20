<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIKARA - Manajemen Lowongan</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind / Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">

    <div class="min-h-screen flex flex-col">
        <!-- Navigation Panel -->
        <nav class="bg-indigo-700 shadow-md border-b border-indigo-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 flex items-center">
                            <span class="text-white font-bold text-2xl tracking-wider">SIKARA</span>
                        </div>
                        <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
                            <a href="{{ route('lowongan.index') }}" class="bg-indigo-800 text-white px-3 py-2 rounded-md text-sm font-medium" aria-current="page">Manajemen Lowongan</a>
                            <!-- MOCK OTHER MENUS -->
                            <a href="#" class="text-indigo-100 hover:bg-indigo-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">Dashboard</a>
                            <a href="#" class="text-indigo-100 hover:bg-indigo-600 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">Pelamar</a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <span class="text-indigo-100 text-sm mr-4">Admin / Mitra</span>
                        <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">A</div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow w-full max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            
            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="mb-6 rounded-lg bg-emerald-50 p-4 border border-emerald-200 shadow-sm animate-fade-in-down">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-lg bg-red-50 p-4 border border-red-200 shadow-sm animate-fade-in-down">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @yield('content')
            
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 mt-auto py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-center text-slate-500 text-sm">
                &copy; {{ date('Y') }} SIKARA - Kelompok D. All rights reserved.
            </div>
        </footer>
    </div>
    
    <style>
        @keyframes fadeInDown {
            0% {
                opacity: 0;
                transform: translateY(-10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-down {
            animation: fadeInDown 0.4s ease-out forwards;
        }
    </style>
</body>
</html>
