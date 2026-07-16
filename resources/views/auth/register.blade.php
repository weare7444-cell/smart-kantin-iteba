<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Smart-Kantin ITEBA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 text-gray-100 font-sans antialiased min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <span class="text-4xl">🍽️</span>
            <h1 class="text-2xl font-bold text-white mt-2">
                Smart<span class="text-amber-400">-Kantin</span>
                <span class="text-sm font-normal text-gray-500">ITEBA</span>
            </h1>
            <p class="text-gray-500 text-sm mt-1">Buat akun baru</p>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">

            @if ($errors->any())
                <div class="mb-4 bg-red-500/15 border border-red-500/30 text-red-300 px-4 py-2.5 rounded-lg text-sm">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Nama Lengkap</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all"
                        placeholder="Nama Anda"
                    >
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Email</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all"
                        placeholder="contoh@email.com"
                    >
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Password</label>
                    <input
                        type="password"
                        name="password"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all"
                        placeholder="Minimal 6 karakter"
                    >
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Konfirmasi Password</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all"
                        placeholder="Ulangi password"
                    >
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Daftar Sebagai</label>
                    <select
                        name="role"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3.5 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all"
                    >
                        <option value="mahasiswa" {{ old('role') === 'mahasiswa' ? 'selected' : '' }}>🎓 Mahasiswa</option>
                        <option value="penjual" {{ old('role') === 'penjual' ? 'selected' : '' }}>👨‍🍳 Penjual Kantin</option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-400 text-gray-950 font-semibold text-sm px-4 py-2.5 rounded-lg transition-all duration-150 active:scale-95"
                >
                    Daftar
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-4">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-amber-400 hover:text-amber-300 transition-colors">Login</a>
            </p>
        </div>

        <p class="text-center text-xs text-gray-600 mt-6">
            &copy; 2026 Smart-Kantin ITEBA &mdash; UAS Pemrograman Web
        </p>
    </div>

</body>
</html>
