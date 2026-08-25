<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTS DevTools Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen" x-data="{ activeTab: 'application' }">
    {{-- Header --}}
    <header class="bg-gray-800 border-b border-gray-700 px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-bold text-white">MTS DevTools</h1>
                <span class="px-3 py-1 rounded-full text-sm font-medium
                    {{ $overallHealth->value === 'healthy' ? 'bg-green-900 text-green-300' : '' }}
                    {{ $overallHealth->value === 'warning' ? 'bg-yellow-900 text-yellow-300' : '' }}
                    {{ $overallHealth->value === 'critical' ? 'bg-red-900 text-red-300' : '' }}">
                    {{ $overallHealth->icon() }} {{ $overallHealth->label() }}
                </span>
            </div>
            <div class="flex items-center gap-4">
                @if($refreshInterval > 0)
                    <span class="text-sm text-gray-400" x-data x-init="setInterval(() => location.reload(), {{ $refreshInterval * 1000 }})">
                        Auto-refresh: {{ $refreshInterval }}s
                    </span>
                @endif
                <form method="POST" action="{{ route('devtools.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-gray-400 hover:text-white transition-colors">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-6 py-8">
        {{-- Tab Navigation --}}
        <nav class="flex gap-1 mb-8 bg-gray-800 rounded-lg p-1">
            <button
                @click="activeTab = 'application'"
                :class="activeTab === 'application' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                class="flex-1 py-2.5 px-4 rounded-md text-sm font-medium transition-colors">
                Application
            </button>
            <button
                @click="activeTab = 'performance'"
                :class="activeTab === 'performance' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                class="flex-1 py-2.5 px-4 rounded-md text-sm font-medium transition-colors">
                Performance
            </button>
            <button
                @click="activeTab = 'security'"
                :class="activeTab === 'security' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                class="flex-1 py-2.5 px-4 rounded-md text-sm font-medium transition-colors">
                Security
            </button>
            <button
                @click="activeTab = 'packages'"
                :class="activeTab === 'packages' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'"
                class="flex-1 py-2.5 px-4 rounded-md text-sm font-medium transition-colors">
                Packages
            </button>
        </nav>

        {{-- Application Tab --}}
        <div x-show="activeTab === 'application'" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Laravel Version --}}
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Laravel</h3>
                    <p class="text-2xl font-bold text-white">{{ $data['application']['laravel'] ?? 'N/A' }}</p>
                </div>

                {{-- PHP Version --}}
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">PHP</h3>
                    <p class="text-2xl font-bold text-white">{{ $data['application']['php'] ?? 'N/A' }}</p>
                </div>

                {{-- Environment --}}
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Environment</h3>
                    <p class="text-2xl font-bold text-white">{{ $data['application']['environment'] ?? 'N/A' }}</p>
                </div>

                {{-- Database --}}
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Database</h3>
                    <p class="text-lg font-bold text-white">{{ $data['application']['database']['driver'] ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-400 mt-1">{{ $data['application']['database']['version'] ?? '' }}</p>
                </div>

                {{-- Cache --}}
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Cache</h3>
                    <p class="text-2xl font-bold text-white">{{ $data['application']['cache']['default'] ?? 'N/A' }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($data['application']['cache']['stores'] ?? [] as $store => $driver)
                            <span class="text-xs bg-gray-700 px-2 py-1 rounded">{{ $store }}: {{ $driver }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- Queue --}}
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Queue</h3>
                    <p class="text-2xl font-bold text-white">{{ $data['application']['queue']['default'] ?? 'N/A' }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($data['application']['queue']['connections'] ?? [] as $name => $driver)
                            <span class="text-xs bg-gray-700 px-2 py-1 rounded">{{ $name }}: {{ $driver }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Performance Tab --}}
        <div x-show="activeTab === 'performance'" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Requests</h3>
                    <p class="text-2xl font-bold text-white">{{ number_format($data['performance']['requests'] ?? 0) }}</p>
                </div>
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Queries</h3>
                    <p class="text-2xl font-bold text-white">{{ number_format($data['performance']['queries'] ?? 0) }}</p>
                </div>
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Slow Queries</h3>
                    <p class="text-2xl font-bold text-white">{{ number_format(count($data['performance']['slow_queries'] ?? [])) }}</p>
                </div>
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Jobs</h3>
                    <p class="text-2xl font-bold text-white">{{ number_format($data['performance']['jobs'] ?? 0) }}</p>
                </div>
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Failed Jobs</h3>
                    <p class="text-2xl font-bold {{ ($data['performance']['failed_jobs'] ?? 0) > 0 ? 'text-red-400' : 'text-white' }}">
                        {{ number_format($data['performance']['failed_jobs'] ?? 0) }}
                    </p>
                </div>
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Cache</h3>
                    <p class="text-lg font-bold text-white">{{ $data['performance']['cache']['driver'] ?? 'N/A' }}</p>
                    <div class="mt-1 text-sm text-gray-400">
                        Hit: {{ number_format($data['performance']['cache']['hit'] ?? 0) }}
                        | Miss: {{ number_format($data['performance']['cache']['miss'] ?? 0) }}
                    </div>
                </div>
            </div>

            {{-- Slow Queries --}}
            @if(! empty($data['performance']['slow_queries']))
                <div class="bg-gray-800 rounded-lg border border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-700">
                        <h3 class="text-lg font-semibold text-white">Slow Queries</h3>
                    </div>
                    <div class="divide-y divide-gray-700 max-h-96 overflow-y-auto">
                        @foreach(array_slice($data['performance']['slow_queries'], -20) as $query)
                            <div class="px-6 py-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-yellow-400 font-mono">{{ number_format($query['duration_ms'], 1) }}ms</span>
                                </div>
                                <p class="text-sm text-gray-300 mt-1 font-mono break-all">{{ Str::limit($query['query'], 120) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Security Tab --}}
        <div x-show="activeTab === 'security'" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Debug Mode</h3>
                    <p class="text-2xl font-bold {{ ($data['security']['debug_mode']['enabled'] ?? false) ? 'text-red-400' : 'text-green-400' }}">
                        {{ $data['security']['debug_mode']['status'] ?? 'N/A' }}
                    </p>
                    <p class="text-sm text-gray-400 mt-1">Risk: {{ $data['security']['debug_mode']['risk'] ?? 'N/A' }}</p>
                </div>

                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Environment</h3>
                    <p class="text-2xl font-bold text-white">{{ $data['security']['environment']['status'] ?? 'N/A' }}</p>
                </div>

                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Configuration</h3>
                    <p class="text-2xl font-bold text-white">{{ $data['security']['configuration']['status'] ?? 'N/A' }}</p>
                </div>

                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h3 class="text-sm font-medium text-gray-400 mb-2">Routes</h3>
                    <p class="text-2xl font-bold text-white">{{ $data['security']['routes']['total'] ?? 0 }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($data['security']['routes']['methods'] ?? [] as $method => $count)
                            <span class="text-xs bg-gray-700 px-2 py-1 rounded">{{ $method }}: {{ $count }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- PHP Extensions --}}
            <div class="bg-gray-800 rounded-lg border border-gray-700">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h3 class="text-lg font-semibold text-white">PHP Extensions</h3>
                </div>
                <div class="divide-y divide-gray-700">
                    @foreach($data['security']['php_extensions'] ?? [] as $name => $info)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <span class="font-medium text-white">{{ $name }}</span>
                                <span class="text-sm text-gray-400 ml-2">{{ $info['description'] }}</span>
                            </div>
                            <span class="{{ $info['loaded'] ? 'text-green-400' : 'text-red-400' }}">
                                {{ $info['loaded'] ? '✓' : '✗' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Packages Tab --}}
        <div x-show="activeTab === 'packages'" x-cloak>
            {{-- Outdated --}}
            @if(! empty($data['packages']['outdated']))
                <div class="bg-yellow-900/20 border border-yellow-700 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-yellow-300 mb-4">Outdated Packages ({{ count($data['packages']['outdated']) }})</h3>
                    <div class="divide-y divide-yellow-800/50">
                        @foreach($data['packages']['outdated'] as $name => $info)
                            <div class="py-3 flex items-center justify-between">
                                <span class="font-medium text-white">{{ $name }}</span>
                                <div class="text-sm">
                                    <span class="text-gray-400">{{ $info['current'] }}</span>
                                    <span class="text-yellow-400 mx-2">→</span>
                                    <span class="text-green-400">{{ $info['latest'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Installed --}}
            <div class="bg-gray-800 rounded-lg border border-gray-700">
                <div class="px-6 py-4 border-b border-gray-700">
                    <h3 class="text-lg font-semibold text-white">Installed Packages ({{ count($data['packages']['installed'] ?? []) }})</h3>
                </div>
                <div class="divide-y divide-gray-700 max-h-[32rem] overflow-y-auto">
                    @foreach($data['packages']['installed'] ?? [] as $name => $info)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <span class="font-medium text-white">{{ $name }}</span>
                                @if($info['dev'])
                                    <span class="ml-2 text-xs bg-yellow-900 text-yellow-300 px-2 py-0.5 rounded">dev</span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-400">
                                <span>{{ $info['version'] }}</span>
                                <span class="ml-2 text-gray-500">{{ $info['constraint'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Health Checks --}}
        <div class="mt-8 bg-gray-800 rounded-lg border border-gray-700">
            <div class="px-6 py-4 border-b border-gray-700">
                <h3 class="text-lg font-semibold text-white">Health Checks</h3>
            </div>
            <div class="divide-y divide-gray-700">
                @foreach($health as $check)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <span class="text-white">{{ $check['label'] }}</span>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-400">{{ $check['message'] }}</span>
                            <span class="px-2 py-1 rounded text-sm
                                {{ $check['status']->value === 'healthy' ? 'bg-green-900 text-green-300' : '' }}
                                {{ $check['status']->value === 'warning' ? 'bg-yellow-900 text-yellow-300' : '' }}
                                {{ $check['status']->value === 'critical' ? 'bg-red-900 text-red-300' : '' }}">
                                {{ $check['status']->icon() }} {{ $check['status']->label() }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <footer class="text-center text-sm text-gray-500 py-6">
        MTS DevTools &middot; Laravel {{ $data['application']['laravel'] ?? '' }} &middot; PHP {{ $data['application']['php'] ?? '' }}
    </footer>
</body>
</html>
