<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instalasi Sistem E-Voting Ketua OSIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <link rel="stylesheet" href="<?= e(url('/assets/css/style.css')); ?>">
    <link rel="icon" type="image/png" href="<?= get_favicon_url('/assets/images/favicon.png'); ?>">
</head>
<body class="flex min-h-screen items-center justify-center px-4 py-10">
    <main class="glass w-full max-w-2xl rounded-[2rem] p-8" data-aos="fade-up">
        <div class="mb-7 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-3xl text-[#07172f] shadow-xl">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <h1 class="text-3xl font-black text-white">Instalasi Sistem E-Voting</h1>
            <p class="mt-2 text-sm text-slate-300">Konfigurasi database dan admin utama untuk pertama kali.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-semibold text-red-700 shadow-lg text-sm">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i><?= e($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-6">
            <!-- Database Credentials Section -->
            <div>
                <h2 class="text-lg font-bold text-[#f6c85f] border-b border-white/10 pb-2 mb-4"><i class="fa-solid fa-database mr-2"></i>Konfigurasi Database</h2>
                
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-xs font-semibold text-slate-200">Database Host</span>
                        <input class="focus-ring w-full rounded-2xl border border-white/20 bg-white/90 px-4 py-3 font-semibold text-slate-900 text-sm" type="text" name="host" value="localhost" placeholder="localhost atau 127.0.0.1" required>
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-xs font-semibold text-slate-200">Port</span>
                        <input class="focus-ring w-full rounded-2xl border border-white/20 bg-white/90 px-4 py-3 font-semibold text-slate-900 text-sm" type="text" name="port" value="3306" required>
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-3 mt-4">
                    <label class="block sm:col-span-1">
                        <span class="mb-2 block text-xs font-semibold text-slate-200">Nama Database</span>
                        <input class="focus-ring w-full rounded-2xl border border-white/20 bg-white/90 px-4 py-3 font-semibold text-slate-900 text-sm" type="text" name="db_name" value="evoting_osis_gedeg" required>
                    </label>
                    <label class="block sm:col-span-1">
                        <span class="mb-2 block text-xs font-semibold text-slate-200">Username DB</span>
                        <input class="focus-ring w-full rounded-2xl border border-white/20 bg-white/90 px-4 py-3 font-semibold text-slate-900 text-sm" type="text" name="username" value="root" required>
                    </label>
                    <label class="block sm:col-span-1">
                        <span class="mb-2 block text-xs font-semibold text-slate-200">Password DB</span>
                        <input class="focus-ring w-full rounded-2xl border border-white/20 bg-white/90 px-4 py-3 font-semibold text-slate-900 text-sm" type="password" name="password" placeholder="Kosongkan jika root default">
                    </label>
                </div>
            </div>

            <!-- Admin Credentials Section -->
            <div>
                <h2 class="text-lg font-bold text-[#f6c85f] border-b border-white/10 pb-2 mb-4"><i class="fa-solid fa-user-shield mr-2"></i>Konfigurasi Akun Administrator</h2>
                
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-xs font-semibold text-slate-200">Username Admin Baru</span>
                        <input class="focus-ring w-full rounded-2xl border border-white/20 bg-white/90 px-4 py-3 font-semibold text-slate-900 text-sm" type="text" name="admin_user" value="Osmantamsis1@" required>
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-xs font-semibold text-slate-200">Password Admin (Min. 8 Karakter)</span>
                        <input class="focus-ring w-full rounded-2xl border border-white/20 bg-white/90 px-4 py-3 font-semibold text-slate-900 text-sm" type="password" name="admin_pass" minlength="8" required>
                    </label>
                </div>
            </div>

            <button class="btn-ripple focus-ring flex w-full items-center justify-center gap-3 rounded-2xl bg-[#f6c85f] px-5 py-3.5 font-black text-[#07172f] shadow-xl transition hover:-translate-y-0.5 hover:bg-white text-base mt-8" type="submit">
                <i class="fa-solid fa-circle-check"></i>
                Simpan & Pasang Sekarang
            </button>
        </form>
    </main>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 700, once: true });
    </script>
</body>
</html>
