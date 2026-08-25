<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MTS DevTools - Authentication Required</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center">
    <div class="bg-gray-800 rounded-lg shadow-xl p-8 w-full max-w-md">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-white">MTS DevTools</h1>
            <p class="text-gray-400 mt-2">Enter password to continue</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-900/50 border border-red-700 rounded-lg p-4 mb-6">
                <p class="text-red-300 text-sm">{{ $errors->first('devtools_password') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ request()->url() }}">
            @csrf
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                <input
                    type="password"
                    name="devtools_password"
                    id="password"
                    class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Enter password"
                    autofocus
                    required
                >
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200"
            >
                Authenticate
            </button>
        </form>
    </div>
</body>
</html>
