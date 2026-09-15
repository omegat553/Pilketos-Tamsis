<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= page_title('Login Admin'); ?></title>
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
    <main class="glass w-full max-w-md rounded-[2rem] p-8" data-aos="fade-up">
        <!-- Logo Row Card -->
        <div class="flex items-center justify-center gap-6 rounded-2xl bg-white/95 p-4 mb-6 shadow-inner">
            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-slate-100 object-contain overflow-hidden">
                <?php if (file_exists(__DIR__ . '/../../../assets/uploads/logo_1.png')): ?>
                    <img class="h-full w-full object-contain" src="<?= get_logo_url(1, ''); ?>" alt="Logo SMAN 1 Gedeg">
                <?php else: ?>
                    <i class="fa-solid fa-graduation-cap text-[#07172f] text-xl" title="SMAN 1 Gedeg"></i>
                <?php endif; ?>
            </div>
            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-slate-100 object-contain overflow-hidden">
                <?php if (file_exists(__DIR__ . '/../../../assets/uploads/logo_2.png')): ?>
                    <img class="h-full w-full object-contain" src="<?= get_logo_url(2, ''); ?>" alt="Logo OSIS">
                <?php else: ?>
                    <i class="fa-solid fa-shield-halved text-[#07172f] text-xl" title="OSIS"></i>
                <?php endif; ?>
            </div>
            <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-slate-100 object-contain overflow-hidden">
                <?php if (file_exists(__DIR__ . '/../../../assets/uploads/logo_3.png')): ?>
                    <img class="h-full w-full object-contain" src="<?= get_logo_url(3, ''); ?>" alt="Logo MPK">
                <?php else: ?>
                    <i class="fa-solid fa-users text-[#07172f] text-xl" title="MPK"></i>
                <?php endif; ?>
            </div>
            <?php if (file_exists(__DIR__ . '/../../../assets/uploads/logo_4.png')): ?>
                <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-slate-100 object-contain overflow-hidden">
                    <img class="h-full w-full object-contain" src="<?= get_logo_url(4, ''); ?>" alt="Logo Tambahan">
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-7 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-3xl text-[#07172f] shadow-xl">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <h1 class="text-2xl font-black text-white">Dashboard Admin</h1>
            <p class="mt-2 text-sm text-slate-300">Pantau hasil voting secara real-time.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"><?= e($error); ?></div>
        <?php endif; ?>

        <form method="post" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <label class="block">
                <span class="mb-2 block text-sm font-semibold text-slate-200">Username</span>
                <input class="focus-ring w-full rounded-2xl border border-white/20 bg-white/90 px-4 py-3 font-semibold text-slate-900" type="text" name="username" placeholder="admin" required>
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-semibold text-slate-200">Password</span>
                <input class="focus-ring w-full rounded-2xl border border-white/20 bg-white/90 px-4 py-3 font-semibold text-slate-900" type="password" name="password" placeholder="password" required>
            </label>
            <button class="btn-ripple flex w-full items-center justify-center gap-3 rounded-2xl bg-[#f6c85f] px-5 py-3 font-black text-[#07172f] shadow-xl transition hover:bg-white" type="submit">
                <i class="fa-solid fa-lock-open"></i>
                Masuk Admin
            </button>
        </form>
        <a href="<?= e(url('/login')); ?>" class="mt-6 block text-center text-sm font-bold text-white underline decoration-white/30 underline-offset-4">Kembali ke Login Siswa</a>
    </main>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>window.APP_BASE_URL = <?= json_encode(BASE_URL); ?>;</script>
    <script src="<?= e(url('/assets/js/main.js')); ?>"></script>
</body>
</html>
