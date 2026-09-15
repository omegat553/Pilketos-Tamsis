<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= page_title('Pilih Kandidat'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <link rel="stylesheet" href="<?= e(url('/assets/css/style.css')); ?>">
    <link rel="icon" type="image/png" href="<?= get_favicon_url('/assets/images/favicon.png'); ?>">
</head>
<body class="min-h-screen px-4 py-8">
    <header class="mx-auto mb-8 flex max-w-7xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between" data-aos="fade-down">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-[#f6c85f]">Pemilihan Ketua OSIS</p>
            <h1 class="text-3xl font-black text-white sm:text-4xl">Pilih Calon Ketua</h1>
            <p class="mt-2 text-slate-300">Halo, <?= e($_SESSION['user_username']); ?>. Suara hanya dapat dikirim satu kali.</p>
        </div>
        <a class="btn-ripple inline-flex items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-5 py-3 font-bold text-white shadow-lg transition hover:bg-white hover:text-[#07172f]" href="<?= e(url('/logout')); ?>">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Keluar
        </a>
    </header>

    <main class="mx-auto max-w-7xl">
        <?php if ($flash): ?>
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-semibold text-red-700"><?= e($flash['message']); ?></div>
        <?php endif; ?>

        <form method="post" data-vote-form>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <input type="hidden" name="candidate_id" value="">

            <div class="grid gap-6 lg:grid-cols-3">
                <?php foreach ($candidates as $candidate): ?>
                    <article class="candidate-card glass-light relative cursor-pointer overflow-hidden rounded-[2rem] border-2 border-transparent text-slate-900" data-aos="fade-up" data-aos-delay="<?= ((int) $candidate['number'] - 1) * 100; ?>" data-aos-anchor-placement="top-bottom" data-candidate-card data-candidate-id="<?= (int) $candidate['id']; ?>" data-candidate-name="<?= e($candidate['chair_name']); ?>">
                        <div class="select-badge absolute right-5 top-5 z-10 flex h-11 w-11 items-center justify-center rounded-full bg-[#f6c85f] text-[#07172f] shadow-xl">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div class="relative bg-[#07172f] p-6 text-white">
                            <div class="absolute left-6 top-6 rounded-2xl bg-[#f6c85f] px-4 py-2 text-xl font-black text-[#07172f]">No. <?= (int) $candidate['number']; ?></div>
                            <img class="mx-auto h-56 w-56 rounded-full border-4 border-white/30 object-cover shadow-2xl" src="<?= e(url($candidate['photo'])); ?>" alt="Foto calon ketua nomor <?= (int) $candidate['number']; ?>">
                        </div>
                        <div class="space-y-5 p-6">
                            <div>
                                <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Ketua</p>
                                <h2 class="text-2xl font-black text-[#07172f]"><?= e($candidate['chair_name']); ?></h2>
                            </div>
                            <div>
                                <p class="font-black text-[#07172f]">Visi</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600"><?= e($candidate['vision']); ?></p>
                            </div>
                            <div>
                                <p class="font-black text-[#07172f]">Misi</p>
                                <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600"><?= e($candidate['mission']); ?></p>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="sticky bottom-4 z-20 mt-8 flex justify-center">
                <button type="button" data-open-confirm disabled class="btn-ripple cursor-not-allowed rounded-2xl bg-[#f6c85f] px-8 py-4 text-lg font-black text-[#07172f] opacity-50 shadow-2xl transition hover:-translate-y-0.5">
                    <i class="fa-solid fa-paper-plane mr-2"></i>
                    Pilih
                </button>
            </div>

            <div class="modal-backdrop fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4" data-confirm-modal>
                <div class="modal-panel glass w-full max-w-md rounded-[2rem] p-7 text-center">
                    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-[#f6c85f] text-3xl text-[#07172f]">
                        <i class="fa-solid fa-circle-question"></i>
                    </div>
                    <h2 class="text-2xl font-black text-white">Konfirmasi Pilihan</h2>
                    <p class="mt-3 leading-7 text-slate-200">Anda yakin memilih <strong data-confirm-name></strong>? Pilihan tidak dapat diubah setelah dikirim.</p>
                    <div class="mt-7 grid gap-3 sm:grid-cols-2">
                        <button type="button" data-close-confirm class="btn-ripple rounded-2xl border border-white/20 bg-white/10 px-5 py-3 font-bold text-white transition hover:bg-white hover:text-[#07172f]">Batal</button>
                        <button type="submit" class="btn-ripple rounded-2xl bg-[#f6c85f] px-5 py-3 font-black text-[#07172f] transition hover:bg-white">Ya, Pilih</button>
                    </div>
                </div>
            </div>
        </form>
    </main>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>window.APP_BASE_URL = <?= json_encode(BASE_URL); ?>;</script>
    <script src="<?= e(url('/assets/js/main.js')); ?>"></script>
</body>
</html>
