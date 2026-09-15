<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= page_title('Dashboard Admin'); ?></title>
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
    <main class="mx-auto max-w-7xl" data-admin-dashboard>
        <!-- Header -->
        <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between" data-aos="fade-down">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-[#f6c85f]">Dashboard Terpadu</p>
                <h1 class="text-3xl font-black text-white sm:text-4xl">Hasil E-Voting OSIS</h1>
                <p class="mt-2 text-slate-300">Login sebagai <?= e($_SESSION['admin_username']); ?>. <span data-refresh-status>Memuat data...</span></p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button class="btn-ripple inline-flex items-center justify-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-5 py-3 font-bold text-white shadow-xl transition hover:bg-white hover:text-[#07172f]" data-settings-trigger>
                    <i class="fa-solid fa-user-gear"></i>
                    Ubah Akun
                </button>
                <a class="btn-ripple inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 font-black text-[#07172f] shadow-xl transition hover:-translate-y-0.5" href="<?= e(url('/admin/logout')); ?>">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    Keluar
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
        <?php if (!empty($flash)): ?>
            <div class="mb-6 rounded-2xl border <?php echo $flash['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700'; ?> px-5 py-4 font-semibold shadow-lg">
                <?= e($flash['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <nav class="mb-8 flex border-b border-white/10" data-aos="fade-up">
            <button class="tab-button active px-6 py-3 font-bold text-white border-b-2 border-[#f6c85f] transition hover:text-[#f6c85f]" data-tab="realtime">
                <i class="fa-solid fa-chart-pie mr-2"></i> Hasil Real-Time
            </button>
            <button class="tab-button px-6 py-3 font-bold text-slate-400 transition hover:text-white" data-tab="candidates">
                <i class="fa-solid fa-users-gear mr-2"></i> Kelola Kandidat
            </button>
            <button class="tab-button px-6 py-3 font-bold text-slate-400 transition hover:text-white" data-tab="codes">
                <i class="fa-solid fa-key mr-2"></i> Kode Peserta
            </button>
            <button class="tab-button px-6 py-3 font-bold text-slate-400 transition hover:text-white" data-tab="settings">
                <i class="fa-solid fa-gears mr-2"></i> Pengaturan
            </button>
        </nav>

        <!-- TAB 1: Realtime Results -->
        <div id="tab-realtime" class="tab-content block">
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <div class="glass rounded-[1.5rem] p-6" data-aos="fade-up">
                    <i class="fa-solid fa-users mb-5 text-3xl text-[#54d6ff]"></i>
                    <p class="text-sm font-semibold text-slate-300">Total Peserta</p>
                    <p class="mt-2 text-4xl font-black text-white" data-total-voters>0</p>
                </div>
                <div class="glass rounded-[1.5rem] p-6" data-aos="fade-up" data-aos-delay="80">
                    <i class="fa-solid fa-check-to-slot mb-5 text-3xl text-emerald-300"></i>
                    <p class="text-sm font-semibold text-slate-300">Sudah Memilih</p>
                    <p class="mt-2 text-4xl font-black text-white" data-voted>0</p>
                </div>
                <div class="glass rounded-[1.5rem] p-6" data-aos="fade-up" data-aos-delay="160">
                    <i class="fa-solid fa-user-clock mb-5 text-3xl text-[#f6c85f]"></i>
                    <p class="text-sm font-semibold text-slate-300">Belum Memilih</p>
                    <p class="mt-2 text-4xl font-black text-white" data-not-voted>0</p>
                </div>
                <div class="glass rounded-[1.5rem] p-6" data-aos="fade-up" data-aos-delay="240">
                    <i class="fa-solid fa-percent mb-5 text-3xl text-white"></i>
                    <p class="text-sm font-semibold text-slate-300">Partisipasi</p>
                    <p class="mt-2 text-4xl font-black text-white" data-participation>0%</p>
                </div>
            </section>

            <section class="mt-8 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="glass-light rounded-[2rem] p-6" data-aos="fade-right">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wide text-slate-500">Grafik Voting</p>
                            <h2 class="text-2xl font-black text-[#07172f]">Distribusi Suara</h2>
                        </div>
                        <i class="fa-solid fa-chart-pie text-3xl text-[#07172f]"></i>
                    </div>
                    <canvas id="voteChart" class="h-80 w-full"></canvas>
                </div>

                <div class="space-y-5" data-aos="fade-left">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wide text-[#f6c85f]">Hasil Real-Time</p>
                            <h2 class="text-2xl font-black text-white">Progress Kandidat</h2>
                        </div>
                        <i class="fa-solid fa-signal text-2xl text-white"></i>
                    </div>
                    <div class="grid gap-4" data-candidate-results></div>
                </div>
            </section>
        </div>

        <!-- TAB 2: Kelola Kandidat -->
        <div id="tab-candidates" class="tab-content hidden">
            <section class="grid gap-6 md:grid-cols-3">
                <?php foreach ($candidates as $candidate): ?>
                    <article class="glass p-6 rounded-[2rem] text-white flex flex-col justify-between" data-aos="fade-up" data-aos-delay="<?= ((int)$candidate['number'] - 1) * 100; ?>">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="rounded-xl bg-[#f6c85f] px-3 py-1 text-sm font-black text-[#07172f]">No. Urut <?= (int)$candidate['number']; ?></span>
                                <span class="text-slate-400 font-bold text-sm"><i class="fa-solid fa-vote-yea mr-1"></i><?= (int)$candidate['total_votes']; ?> Suara</span>
                            </div>
                            <img class="mx-auto h-40 w-40 rounded-full border-4 border-white/20 object-cover shadow-2xl mb-4" src="../<?= e($candidate['photo']); ?>" alt="Foto Kandidat">
                            <h3 class="text-xl font-black text-center mb-4"><?= e($candidate['chair_name']); ?></h3>
                            
                            <div class="space-y-3 mb-6">
                                <div>
                                    <h4 class="font-bold text-[#f6c85f] text-sm">Visi</h4>
                                    <p class="text-xs text-slate-300 leading-5 mt-1"><?= e($candidate['vision']); ?></p>
                                </div>
                                <div>
                                    <h4 class="font-bold text-[#f6c85f] text-sm">Misi</h4>
                                    <p class="text-xs text-slate-300 leading-5 whitespace-pre-line mt-1"><?= e($candidate['mission']); ?></p>
                                </div>
                            </div>
                        </div>
                        <button class="btn-ripple w-full rounded-xl bg-white/10 border border-white/10 hover:bg-white hover:text-[#07172f] py-2.5 font-bold transition flex items-center justify-center gap-2"
                                data-edit-candidate-btn
                                data-candidate-id="<?= (int)$candidate['id']; ?>"
                                data-candidate-number="<?= (int)$candidate['number']; ?>"
                                data-candidate-name="<?= e($candidate['chair_name']); ?>"
                                data-candidate-vision="<?= e($candidate['vision']); ?>"
                                data-candidate-mission="<?= e($candidate['mission']); ?>">
                            <i class="fa-solid fa-pen-to-square"></i> Edit Kandidat
                        </button>
                    </article>
                <?php endforeach; ?>
            </section>
        </div>

        <!-- TAB 3: Kode Peserta -->
        <div id="tab-codes" class="tab-content hidden">
            <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
                <!-- List & Table -->
                <div class="glass p-6 rounded-[2rem]">
                    <div class="flex flex-col sm:flex-row gap-4 justify-between items-center mb-6">
                        <h2 class="text-xl font-black text-white">Daftar Kode Peserta</h2>
                        <div class="flex gap-2 w-full sm:w-auto">
                            <input type="text" id="search-code" placeholder="Cari kode..." class="focus-ring flex-1 sm:flex-none rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white placeholder-slate-400">
                            <select id="filter-status" class="focus-ring rounded-xl border border-white/10 bg-white/5 px-3 py-2.5 text-sm font-semibold text-white">
                                <option value="" class="bg-[#07172f]">Semua Status</option>
                                <option value="belum" class="bg-[#07172f]">Belum Memilih</option>
                                <option value="sudah" class="bg-[#07172f]">Sudah Memilih</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto rounded-xl">
                        <table class="w-full text-left text-sm text-slate-200">
                            <thead class="bg-white/10 text-xs uppercase tracking-wider text-slate-300 font-bold">
                                <tr>
                                    <th class="px-5 py-3">No</th>
                                    <th class="px-5 py-3">Kode Peserta</th>
                                    <th class="px-5 py-3">Status Voting</th>
                                    <th class="px-5 py-3">Dibuat Pada</th>
                                </tr>
                            </thead>
                            <tbody id="codes-table-body">
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-slate-400">Memuat data kode...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex justify-between items-center mt-6">
                        <p class="text-xs text-slate-400 font-semibold" id="pagination-info">Menampilkan 0 dari 0</p>
                        <div class="flex gap-2">
                            <button id="prev-page" disabled class="btn-ripple px-4 py-2 rounded-xl bg-white/5 border border-white/10 font-bold text-xs text-white opacity-50 cursor-not-allowed transition hover:bg-white hover:text-[#07172f]">
                                <i class="fa-solid fa-angle-left mr-1"></i> Sblm
                            </button>
                            <button id="next-page" disabled class="btn-ripple px-4 py-2 rounded-xl bg-white/5 border border-white/10 font-bold text-xs text-white opacity-50 cursor-not-allowed transition hover:bg-white hover:text-[#07172f]">
                                Next <i class="fa-solid fa-angle-right ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Admin Action Controls -->
                <div class="space-y-6">
                    <!-- Export Box -->
                    <div class="glass p-6 rounded-[2rem] text-white">
                        <h3 class="text-lg font-black mb-3"><i class="fa-solid fa-file-csv text-[#f6c85f] mr-2"></i>Ekspor Kode</h3>
                        <p class="text-xs text-slate-300 leading-5 mb-5">Unduh seluruh data kode peserta beserta status pemilihan dalam format tabel CSV (bisa dibuka langsung di Microsoft Excel atau Google Sheets).</p>
                        <a href="<?= e(url('/admin/codes/export')); ?>" class="btn-ripple w-full text-center inline-flex items-center justify-center gap-2 rounded-xl bg-[#f6c85f] px-5 py-3.5 font-black text-[#07172f] shadow-xl transition hover:-translate-y-0.5 hover:bg-white">
                            <i class="fa-solid fa-download"></i> Ekspor CSV
                        </a>
                    </div>

                    <!-- Import Box -->
                    <div class="glass p-6 rounded-[2rem] text-white">
                        <h3 class="text-lg font-black mb-3"><i class="fa-solid fa-file-excel text-[#54d6ff] mr-2"></i>Impor dari Excel</h3>
                        <p class="text-xs text-slate-300 leading-5 mb-5">Unggah berkas Excel (`.xlsx`) berisi **tepat 1360 kode** unik di kolom B untuk memperbarui seluruh daftar pemilih secara otomatis.</p>
                        
                        <form action="<?= e(url('/admin/codes/import')); ?>" method="post" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                            <label class="block cursor-pointer border-2 border-dashed border-white/20 rounded-xl p-4 text-center hover:border-[#54d6ff] transition bg-white/5">
                                <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-400 mb-2"></i>
                                <span class="block text-xs font-bold text-slate-300" id="file-label">Pilih file .xlsx</span>
                                <input type="file" name="excel" accept=".xlsx" class="hidden" required id="excel-file-input">
                            </label>
                            <button type="submit" class="btn-ripple w-full rounded-xl bg-[#54d6ff] py-3 font-black text-[#07172f] shadow-xl transition hover:bg-white">
                                <i class="fa-solid fa-upload mr-1"></i> Impor Sekarang
                            </button>
                        </form>
                    </div>

                    <!-- Reset & Generate Box -->
                    <div class="glass p-6 rounded-[2rem] text-white border border-red-500/20">
                        <h3 class="text-lg font-black mb-3 text-red-300"><i class="fa-solid fa-triangle-exclamation mr-2"></i>Reset & Generate Kode</h3>
                        <p class="text-xs text-slate-300 leading-5 mb-5">Tindakan ini akan **menghapus semua suara masuk** dan membuat 1360 kode peserta baru secara acak. Harap cadangkan data sebelum melakukan tindakan ini.</p>
                        <button class="btn-ripple w-full rounded-xl bg-red-600 hover:bg-red-500 py-3 font-black text-white shadow-xl transition" data-open-generate-modal>
                            <i class="fa-solid fa-rotate mr-1"></i> Generate Kode Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: Pengaturan -->
        <div id="tab-settings" class="tab-content hidden">
            <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <!-- Upload Logos Grid Card -->
                <div class="glass p-6 rounded-[2rem] text-white">
                    <h2 class="text-xl font-black mb-4 flex items-center gap-2"><i class="fa-solid fa-images text-[#f6c85f]"></i>Kelola Logo &amp; Favicon</h2>
                    <p class="text-xs text-slate-300 leading-5 mb-6">Unggah berkas gambar berformat PNG transparan untuk logo sekolah/OSIS/MPK, serta berkas PNG/ICO untuk Favicon sistem.</p>
                    
                    <form action="<?= e(url('/admin/settings/logos')); ?>" method="post" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                        
                        <div class="grid gap-4 sm:grid-cols-2">
                            <!-- Logo 1 SMANEG -->
                            <div class="border border-white/10 rounded-2xl p-4 bg-white/5 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-xs font-bold text-[#f6c85f] mb-2">Logo 1: <?= e(SCHOOL_NAME); ?></h3>
                                    <div class="h-16 w-16 bg-white/10 rounded-xl mx-auto flex items-center justify-center mb-3 overflow-hidden">
                                        <?php if (file_exists(__DIR__ . '/../../../assets/uploads/logo_1.png')): ?>
                                            <img class="h-full w-full object-contain" src="<?= get_logo_url(1, ''); ?>" alt="Logo 1">
                                        <?php else: ?>
                                            <i class="fa-solid fa-graduation-cap text-white/50 text-xl"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <input type="file" name="logo_1" accept=".png" class="w-full text-xs text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-[#f6c85f] file:text-[#07172f] hover:file:bg-white">
                            </div>

                            <!-- Logo 2 OSIS -->
                            <div class="border border-white/10 rounded-2xl p-4 bg-white/5 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-xs font-bold text-[#f6c85f] mb-2">Logo 2: OSIS SMANEG</h3>
                                    <div class="h-16 w-16 bg-white/10 rounded-xl mx-auto flex items-center justify-center mb-3 overflow-hidden">
                                        <?php if (file_exists(__DIR__ . '/../../../assets/uploads/logo_2.png')): ?>
                                            <img class="h-full w-full object-contain" src="<?= get_logo_url(2, ''); ?>" alt="Logo 2">
                                        <?php else: ?>
                                            <i class="fa-solid fa-shield-halved text-white/50 text-xl"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <input type="file" name="logo_2" accept=".png" class="w-full text-xs text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-[#f6c85f] file:text-[#07172f] hover:file:bg-white">
                            </div>

                            <!-- Logo 3 MPK -->
                            <div class="border border-white/10 rounded-2xl p-4 bg-white/5 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-xs font-bold text-[#f6c85f] mb-2">Logo 3: MPK SMANEG</h3>
                                    <div class="h-16 w-16 bg-white/10 rounded-xl mx-auto flex items-center justify-center mb-3 overflow-hidden">
                                        <?php if (file_exists(__DIR__ . '/../../../assets/uploads/logo_3.png')): ?>
                                            <img class="h-full w-full object-contain" src="<?= get_logo_url(3, ''); ?>" alt="Logo 3">
                                        <?php else: ?>
                                            <i class="fa-solid fa-users text-white/50 text-xl"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <input type="file" name="logo_3" accept=".png" class="w-full text-xs text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-[#f6c85f] file:text-[#07172f] hover:file:bg-white">
                            </div>

                            <!-- Logo 4 Extra -->
                            <div class="border border-white/10 rounded-2xl p-4 bg-white/5 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-xs font-bold text-[#f6c85f] mb-2">Logo 4: Tambahan</h3>
                                    <div class="h-16 w-16 bg-white/10 rounded-xl mx-auto flex items-center justify-center mb-3 overflow-hidden">
                                        <?php if (file_exists(__DIR__ . '/../../../assets/uploads/logo_4.png')): ?>
                                            <img class="h-full w-full object-contain" src="<?= get_logo_url(4, ''); ?>" alt="Logo 4">
                                        <?php else: ?>
                                            <i class="fa-solid fa-plus text-white/50 text-xl"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <input type="file" name="logo_4" accept=".png" class="w-full text-xs text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-[#f6c85f] file:text-[#07172f] hover:file:bg-white">
                            </div>
                        </div>

                        <!-- Favicon system slot -->
                        <div class="border border-white/10 rounded-2xl p-4 bg-white/5 flex items-center justify-between gap-4 mt-4">
                            <div class="flex items-center gap-3">
                                <div class="h-12 w-12 bg-white/10 rounded-xl flex items-center justify-center overflow-hidden">
                                    <?php if (file_exists(__DIR__ . '/../../../assets/uploads/favicon.png')): ?>
                                        <img class="h-full w-full object-contain" src="<?= get_favicon_url(''); ?>" alt="Favicon">
                                    <?php else: ?>
                                        <i class="fa-solid fa-globe text-white/50 text-lg"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-[#f6c85f]">Logo 5: Favicon Browser</h3>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Format PNG/ICO transparan.</p>
                                </div>
                            </div>
                            <input type="file" name="favicon" accept=".png,.ico" class="text-xs text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-[#f6c85f] file:text-[#07172f] hover:file:bg-white">
                        </div>

                        <button type="submit" class="btn-ripple w-full rounded-xl bg-[#f6c85f] py-3.5 font-black text-[#07172f] shadow-xl transition hover:bg-white mt-6">
                            <i class="fa-solid fa-cloud-arrow-up mr-1"></i> Unggah Semua Logo
                        </button>
                    </form>
                </div>

                <!-- Change Password Form Card -->
                <div class="glass p-6 rounded-[2rem] text-white">
                    <h2 class="text-xl font-black mb-4 flex items-center gap-2"><i class="fa-solid fa-user-lock text-[#54d6ff]"></i>Ubah Kredensial Admin</h2>
                    <p class="text-xs text-slate-300 leading-5 mb-6">Perbarui username administrator dan ganti password lama dengan password baru yang lebih aman.</p>
                    
                    <form action="<?= e(url('/admin/account')); ?>" method="post" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                        
                        <label class="block">
                            <span class="mb-2 block text-xs font-semibold text-slate-200">Username Admin</span>
                            <input class="focus-ring w-full rounded-xl border border-white/20 bg-white/95 px-4 py-2.5 font-semibold text-slate-900 text-sm" type="text" name="username" value="<?= e($_SESSION['admin_username']); ?>" required>
                        </label>
                        
                        <label class="block">
                            <span class="mb-2 block text-xs font-semibold text-slate-200">Password Saat Ini</span>
                            <input class="focus-ring w-full rounded-xl border border-white/20 bg-white/95 px-4 py-2.5 font-semibold text-slate-900 text-sm" type="password" name="current_password" required>
                        </label>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-xs font-semibold text-slate-200">Password Baru</span>
                                <input class="focus-ring w-full rounded-xl border border-white/20 bg-white/95 px-4 py-2.5 font-semibold text-slate-900 text-sm" type="password" name="new_password" minlength="8" required>
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-xs font-semibold text-slate-200">Konfirmasi Password</span>
                                <input class="focus-ring w-full rounded-xl border border-white/20 bg-white/95 px-4 py-2.5 font-semibold text-slate-900 text-sm" type="password" name="confirm_password" minlength="8" required>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-ripple w-full rounded-xl bg-[#54d6ff] py-3.5 font-black text-[#07172f] shadow-xl transition hover:bg-white mt-6">
                            <i class="fa-solid fa-key mr-1"></i> Simpan Password Baru
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL: Edit Kandidat -->
        <div class="modal-backdrop fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4" id="edit-candidate-modal">
            <div class="modal-panel glass w-full max-w-lg rounded-[2rem] p-7">
                <h2 class="text-2xl font-black text-white mb-5"><i class="fa-solid fa-user-pen mr-2 text-[#f6c85f]"></i>Edit Calon Ketua</h2>
                <form action="<?= e(url('/admin/candidate/edit')); ?>" method="post" enctype="multipart/form-data" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                    <input type="hidden" name="id" id="edit-id">

                    <div>
                        <span class="mb-2 block text-sm font-semibold text-slate-200">Kandidat Nomor Urut <span id="edit-number-badge" class="font-bold text-[#f6c85f]"></span></span>
                    </div>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-slate-200">Nama Lengkap</span>
                        <input class="focus-ring w-full rounded-2xl border border-white/20 bg-white/95 px-4 py-3 font-semibold text-slate-900" type="text" name="chair_name" id="edit-chair-name" required>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-slate-200">Visi Kandidat</span>
                        <textarea class="focus-ring w-full rounded-2xl border border-white/20 bg-white/95 px-4 py-3 font-semibold text-slate-900 h-20" name="vision" id="edit-vision" required></textarea>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-slate-200">Misi Kandidat</span>
                        <textarea class="focus-ring w-full rounded-2xl border border-white/20 bg-white/95 px-4 py-3 font-semibold text-slate-900 h-28" name="mission" id="edit-mission" required></textarea>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-slate-200">Unggah Foto Baru (Opsional, JPG/PNG, Max 2MB)</span>
                        <input class="w-full text-sm text-slate-300 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#f6c85f] file:text-[#07172f] hover:file:bg-white" type="file" name="photo" accept=".jpg,.jpeg,.png">
                    </label>

                    <div class="mt-7 grid gap-3 sm:grid-cols-2">
                        <button type="button" class="btn-ripple rounded-2xl border border-white/20 bg-white/10 px-5 py-3 font-bold text-white transition hover:bg-white hover:text-[#07172f]" data-close-edit-modal>Batal</button>
                        <button type="submit" class="btn-ripple rounded-2xl bg-[#f6c85f] px-5 py-3 font-black text-[#07172f] transition hover:bg-white">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Konfirmasi Reset & Generate -->
        <div class="modal-backdrop fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4" id="generate-confirm-modal">
            <div class="modal-panel glass w-full max-w-md rounded-[2rem] p-7 text-center">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-red-600 text-3xl text-white shadow-xl">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h2 class="text-2xl font-black text-white">Reset Data & Buat Kode?</h2>
                <p class="mt-3 leading-6 text-xs text-slate-200">Tindakan ini permanen. Seluruh suara pemilih yang masuk akan dihapus, dan 1360 kode baru akan dihasilkan. Sistem voting akan ter-reset ke awal.</p>
                
                <form action="<?= e(url('/admin/codes/generate')); ?>" method="post" class="mt-7 grid gap-3 sm:grid-cols-2">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
                    <button type="button" class="btn-ripple rounded-2xl border border-white/20 bg-white/10 px-5 py-3 font-bold text-white transition hover:bg-white hover:text-[#07172f]" data-close-generate-modal>Batal</button>
                    <button type="submit" class="btn-ripple rounded-2xl bg-red-600 hover:bg-red-500 px-5 py-3 font-black text-white transition">Ya, Reset & Buat</button>
                </form>
            </div>
        </div>

    </main>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>window.APP_BASE_URL = <?= json_encode(BASE_URL); ?>;</script>
    <script src="<?= e(url('/assets/js/main.js')); ?>"></script>
</body>
</html>
