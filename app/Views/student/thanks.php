<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= page_title('Terima Kasih'); ?></title>
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
    <main class="glass max-w-2xl rounded-[2rem] p-8 text-center sm:p-12" data-aos="zoom-in">
        <div class="mx-auto mb-7 flex h-28 w-28 items-center justify-center rounded-full bg-emerald-400 text-6xl text-white shadow-2xl">
            <i class="fa-solid fa-check"></i>
        </div>
        <h1 class="text-3xl font-black text-white sm:text-5xl">Terima Kasih Telah Memilih</h1>
        <p class="mt-6 text-lg leading-8 text-slate-200">Terima kasih telah berpartisipasi dalam Pemilihan Ketua OSIS SMAN 1 Gedeg.</p>
        <p class="mt-3 text-lg leading-8 text-slate-200">Setiap pilihan adalah langkah untuk meningkatkan mutu SMAN 1 Gedeg.</p>
        <a href="<?= e(url('/logout')); ?>" class="btn-ripple mt-8 inline-flex items-center justify-center gap-3 rounded-2xl bg-[#f6c85f] px-8 py-4 font-black text-[#07172f] shadow-xl transition hover:-translate-y-0.5 hover:bg-white">
            <i class="fa-solid fa-house"></i>
            Selesai
        </a>
    </main>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>window.APP_BASE_URL = <?= json_encode(BASE_URL); ?>;</script>
    <script src="<?= e(url('/assets/js/main.js')); ?>"></script>
</body>
</html>
