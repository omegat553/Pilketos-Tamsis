<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= page_title('Login Siswa'); ?></title>
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
    <main class="grid w-full max-w-6xl gap-8 lg:grid-cols-[1.15fr_0.85fr] lg:items-center">
        <section class="space-y-7" data-aos="fade-right">
            <!-- Logo Row Card -->
            <div class="inline-flex items-center gap-5 rounded-2xl bg-white/95 p-3 shadow-lg">
                <div class="flex items-center justify-center h-10 w-10 rounded-lg bg-slate-100 object-contain overflow-hidden">
                    <?php if (file_exists(__DIR__ . '/../../../assets/uploads/logo_1.png')): ?>
                        <img class="h-full w-full object-contain" src="<?= get_logo_url(1, ''); ?>" alt="Logo SMAN 1 Gedeg">
                    <?php else: ?>
                        <i class="fa-solid fa-graduation-cap text-[#07172f] text-lg" title="SMAN 1 Gedeg"></i>
                    <?php endif; ?>
                </div>
                <div class="flex items-center justify-center h-10 w-10 rounded-lg bg-slate-100 object-contain overflow-hidden">
                    <?php if (file_exists(__DIR__ . '/../../../assets/uploads/logo_2.png')): ?>
                        <img class="h-full w-full object-contain" src="<?= get_logo_url(2, ''); ?>" alt="Logo OSIS">
                    <?php else: ?>
                        <i class="fa-solid fa-shield-halved text-[#07172f] text-lg" title="OSIS"></i>
                    <?php endif; ?>
                </div>
                <div class="flex items-center justify-center h-10 w-10 rounded-lg bg-slate-100 object-contain overflow-hidden">
                    <?php if (file_exists(__DIR__ . '/../../../assets/uploads/logo_3.png')): ?>
                        <img class="h-full w-full object-contain" src="<?= get_logo_url(3, ''); ?>" alt="Logo MPK">
                    <?php else: ?>
                        <i class="fa-solid fa-users text-[#07172f] text-lg" title="MPK"></i>
                    <?php endif; ?>
                </div>
                <?php if (file_exists(__DIR__ . '/../../../assets/uploads/logo_4.png')): ?>
                    <div class="flex items-center justify-center h-10 w-10 rounded-lg bg-slate-100 object-contain overflow-hidden">
                        <img class="h-full w-full object-contain" src="<?= get_logo_url(4, ''); ?>" alt="Logo Tambahan">
                    </div>
                <?php endif; ?>
            </div>
            <div class="space-y-4">
                <h1 class="max-w-3xl text-4xl font-black leading-tight text-white sm:text-5xl lg:text-6xl">E-Voting Ketua OSIS SMAN 1 Gedeg</h1>
                <p class="max-w-2xl text-lg leading-8 text-slate-200">Masuk dengan akun siswa untuk memberikan satu suara resmi pada Pemilihan Ketua OSIS.</p>
            </div>
            <div class="grid max-w-2xl gap-4 sm:grid-cols-3">
                <div class="glass rounded-3xl p-5">
                    <p class="text-3xl font-black text-white">1360</p>
                    <p class="text-sm font-medium text-slate-300">Peserta</p>
                </div>
                <div class="glass rounded-3xl p-5">
                    <p class="text-3xl font-black text-white">3</p>
                    <p class="text-sm font-medium text-slate-300">Calon</p>
                </div>
                <div class="glass rounded-3xl p-5">
                    <p class="text-3xl font-black text-white">1x</p>
                    <p class="text-sm font-medium text-slate-300">Kesempatan</p>
                </div>
            </div>
        </section>

        <section class="glass rounded-[2rem] p-6 sm:p-8" data-aos="fade-left">

            <div class="mb-7 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-3xl text-[#07172f] shadow-xl">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <h2 class="text-2xl font-bold text-white">Login Siswa</h2>
                <p class="mt-2 text-sm text-slate-300">Masukkan kode peserta 20 karakter dari panitia.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"><?= e($error); ?></div>
            <?php endif; ?>
            <?php if ($flash): ?>
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700"><?= e($flash['message']); ?></div>
            <?php endif; ?>

            <form method="post" class="space-y-5" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-slate-200">Kode Peserta</span>
                    <input class="focus-ring w-full rounded-2xl border border-white/20 bg-white/90 px-4 py-3 font-semibold uppercase text-slate-900 shadow-inner" type="text" name="username" placeholder="Contoh: FZ9BRSTCWXD69V6SQ2AT" minlength="20" maxlength="20" pattern="[A-Za-z0-9]{20}" autocapitalize="characters" autocomplete="off" required>
                </label>
                <button class="btn-ripple focus-ring flex w-full items-center justify-center gap-3 rounded-2xl bg-[#f6c85f] px-5 py-3 font-black text-[#07172f] shadow-xl transition hover:-translate-y-0.5 hover:bg-white" type="submit">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Masuk Voting
                </button>
            </form>
        </section>
    </main>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>window.APP_BASE_URL = <?= json_encode(BASE_URL); ?>;</script>
    <script src="<?= e(url('/assets/js/main.js')); ?>"></script>
</body>
</html>
