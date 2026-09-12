document.addEventListener('DOMContentLoaded', () => {
    if (window.AOS) {
        AOS.init({ duration: 700, once: true, offset: 80 });
    }

    document.querySelectorAll('.btn-ripple').forEach((button) => {
        button.addEventListener('click', (event) => {
            const circle = document.createElement('span');
            const diameter = Math.max(button.clientWidth, button.clientHeight);
            const radius = diameter / 2;
            const rect = button.getBoundingClientRect();

            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.left = `${event.clientX - rect.left - radius}px`;
            circle.style.top = `${event.clientY - rect.top - radius}px`;
            circle.classList.add('ripple');

            const ripple = button.querySelector('.ripple');
            if (ripple) ripple.remove();

            button.appendChild(circle);
        });
    });

    initVotingPage();
    initDashboard();
    initAdminControls();
});

function initVotingPage() {
    const form = document.querySelector('[data-vote-form]');
    if (!form) return;

    const cards = document.querySelectorAll('[data-candidate-card]');
    const chosenInput = document.querySelector('[name="candidate_id"]');
    const chooseButton = document.querySelector('[data-open-confirm]');
    const modal = document.querySelector('[data-confirm-modal]');
    const closeButtons = document.querySelectorAll('[data-close-confirm]');
    const candidateName = document.querySelector('[data-confirm-name]');

    cards.forEach((card) => {
        card.addEventListener('click', () => {
            cards.forEach((item) => item.classList.remove('is-selected'));
            card.classList.add('is-selected');
            chosenInput.value = card.dataset.candidateId;
            chooseButton.disabled = false;
            chooseButton.classList.remove('opacity-50', 'cursor-not-allowed');
            candidateName.textContent = card.dataset.candidateName;
        });
    });

    chooseButton.addEventListener('click', () => {
        if (!chosenInput.value) return;
        modal.classList.add('is-open');
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => modal.classList.remove('is-open'));
    });
}

function initDashboard() {
    const dashboard = document.querySelector('[data-admin-dashboard]');
    if (!dashboard) return;

    const canvas = document.querySelector('#voteChart');
    const context = canvas.getContext('2d');

    async function loadStats() {
        try {
            const response = await fetch('/api/stats', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });

            if (!response.ok) throw new Error('Gagal mengambil data.');

            const data = await response.json();
            renderStats(data);
            drawChart(context, canvas, data.candidates);
        } catch (error) {
            const status = document.querySelector('[data-refresh-status]');
            if (status) status.textContent = 'Data belum bisa diperbarui.';
        }
    }

    function renderStats(data) {
        setText('[data-total-voters]', data.total_voters);
        setText('[data-voted]', data.voted);
        setText('[data-not-voted]', data.not_voted);
        setText('[data-participation]', `${data.participation}%`);
        setText('[data-refresh-status]', `Diperbarui ${data.updated_at}`);

        const list = document.querySelector('[data-candidate-results]');
        if (list) {
            list.innerHTML = '';
            data.candidates.forEach((candidate, index) => {
                const item = document.createElement('div');
                item.className = 'rounded-2xl bg-white p-5 text-slate-900 shadow-lg';
                item.innerHTML = `
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">Nomor ${candidate.number}</p>
                            <h3 class="text-lg font-bold">${escapeHtml(candidate.chair_name)}</h3>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-black text-[#07172f]">${candidate.total_votes}</p>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">suara</p>
                        </div>
                    </div>
                    <div class="mt-4 h-3 overflow-hidden rounded-full progress-track">
                        <div class="h-full rounded-full progress-fill" style="width: ${candidate.percentage}%"></div>
                    </div>
                    <p class="mt-2 text-sm font-semibold text-slate-600">${candidate.percentage}% dari suara masuk</p>
                `;
                list.appendChild(item);
            });
        }
    }

    loadStats();
    setInterval(loadStats, 5000);
}

function initAdminControls() {
    const dashboard = document.querySelector('[data-admin-dashboard]');
    if (!dashboard) return;

    // 1. Tab switching
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    if (tabButtons.length > 0) {
        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                tabButtons.forEach(b => {
                    b.classList.remove('active', 'border-b-2', 'border-[#f6c85f]', 'text-white');
                    b.classList.add('text-slate-400');
                });
                btn.classList.add('active', 'border-b-2', 'border-[#f6c85f]', 'text-white');
                btn.classList.remove('text-slate-400');

                const tabId = btn.dataset.tab;
                tabContents.forEach(content => {
                    if (content.id === `tab-${tabId}`) {
                        content.classList.remove('hidden');
                        content.classList.add('block');
                    } else {
                        content.classList.remove('block');
                        content.classList.add('hidden');
                    }
                });
            });
        });
    }

    // Settings trigger button from header
    const settingsTrigger = document.querySelector('[data-settings-trigger]');
    if (settingsTrigger) {
        settingsTrigger.addEventListener('click', () => {
            const settingsTabBtn = document.querySelector('[data-tab="settings"]');
            if (settingsTabBtn) {
                settingsTabBtn.click();
            }
        });
    }

    // Auto-activate settings tab if hash is present
    if (window.location.hash === '#settings') {
        const settingsTabBtn = document.querySelector('[data-tab="settings"]');
        if (settingsTabBtn) {
            settingsTabBtn.click();
        }
    }

    // 2. Edit Candidate Modal
    const editModal = document.querySelector('#edit-candidate-modal');
    const editBtns = document.querySelectorAll('[data-edit-candidate-btn]');
    const closeEditBtn = document.querySelector('[data-close-edit-modal]');

    if (editModal && editBtns.length > 0) {
        editBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelector('#edit-id').value = btn.dataset.candidateId;
                document.querySelector('#edit-number-badge').textContent = btn.dataset.candidateNumber;
                document.querySelector('#edit-chair-name').value = btn.dataset.candidateName;
                document.querySelector('#edit-vision').value = btn.dataset.candidateVision;
                document.querySelector('#edit-mission').value = btn.dataset.candidateMission;
                editModal.classList.add('is-open');
            });
        });

        if (closeEditBtn) {
            closeEditBtn.addEventListener('click', () => {
                editModal.classList.remove('is-open');
            });
        }
    }

    // 3. Generate Modal
    const generateModal = document.querySelector('#generate-confirm-modal');
    const openGenerateBtn = document.querySelector('[data-open-generate-modal]');
    const closeGenerateBtn = document.querySelector('[data-close-generate-modal]');

    if (generateModal && openGenerateBtn) {
        openGenerateBtn.addEventListener('click', () => {
            generateModal.classList.add('is-open');
        });

        if (closeGenerateBtn) {
            closeGenerateBtn.addEventListener('click', () => {
                generateModal.classList.remove('is-open');
            });
        }
    }

    // 4. File upload label change
    const excelInput = document.querySelector('#excel-file-input');
    const fileLabel = document.querySelector('#file-label');
    if (excelInput && fileLabel) {
        excelInput.addEventListener('change', () => {
            if (excelInput.files && excelInput.files[0]) {
                fileLabel.textContent = excelInput.files[0].name;
                fileLabel.classList.add('text-[#54d6ff]');
            }
        });
    }

    // 5. Codes Table Loading
    const codesTableBody = document.querySelector('#codes-table-body');
    const searchCodeInput = document.querySelector('#search-code');
    const filterStatusSelect = document.querySelector('#filter-status');
    const prevPageBtn = document.querySelector('#prev-page');
    const nextPageBtn = document.querySelector('#next-page');
    const paginationInfo = document.querySelector('#pagination-info');

    if (codesTableBody) {
        let currentPage = 1;
        const limit = 10;
        let search = '';
        let status = '';

        async function loadCodes() {
            try {
                codesTableBody.innerHTML = '<tr><td colspan="4" class="px-5 py-8 text-center text-slate-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>';

                const url = `/admin/codes/list?page=${currentPage}&limit=${limit}&search=${encodeURIComponent(search)}&status=${status}`;
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error('Failed to fetch');

                const result = await response.json();
                renderCodesTable(result.data, (currentPage - 1) * limit);
                updatePaginationControls(result.current_page, result.total_pages, result.total_records);
            } catch (err) {
                codesTableBody.innerHTML = '<tr><td colspan="4" class="px-5 py-8 text-center text-red-400">Gagal mengambil data kode peserta.</td></tr>';
            }
        }

        function renderCodesTable(data, startNum) {
            codesTableBody.innerHTML = '';
            if (data.length === 0) {
                codesTableBody.innerHTML = '<tr><td colspan="4" class="px-5 py-8 text-center text-slate-400">Tidak ada kode peserta yang ditemukan.</td></tr>';
                return;
            }

            data.forEach((row, index) => {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-white/5 hover:bg-white/5 transition';

                const num = startNum + index + 1;
                const statusBadge = row.status_vote === 'sudah'
                    ? '<span class="rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-bold text-emerald-400 border border-emerald-500/20"><i class="fa-solid fa-check-circle mr-1"></i>Sudah Memilih</span>'
                    : '<span class="rounded-full bg-yellow-500/20 px-3 py-1 text-xs font-bold text-yellow-400 border border-yellow-500/20"><i class="fa-solid fa-clock mr-1"></i>Belum Memilih</span>';

                // Format Date
                const dateObj = new Date(row.created_at);
                const formattedDate = dateObj.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                tr.innerHTML = `
                    <td class="px-5 py-4 font-semibold text-slate-400">${num}</td>
                    <td class="px-5 py-4 font-mono font-bold text-white tracking-wider">${escapeHtml(row.username)}</td>
                    <td class="px-5 py-4">${statusBadge}</td>
                    <td class="px-5 py-4 text-xs font-semibold text-slate-400">${formattedDate}</td>
                `;
                codesTableBody.appendChild(tr);
            });
        }

        function updatePaginationControls(current, total, totalRecords) {
            currentPage = current;

            const startRange = totalRecords > 0 ? (current - 1) * limit + 1 : 0;
            const endRange = Math.min(current * limit, totalRecords);
            paginationInfo.textContent = `Menampilkan ${startRange} - ${endRange} dari ${totalRecords}`;

            prevPageBtn.disabled = current <= 1;
            if (current <= 1) {
                prevPageBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                prevPageBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }

            nextPageBtn.disabled = current >= total;
            if (current >= total) {
                nextPageBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                nextPageBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        // Search and filter listeners
        let searchTimeout;
        searchCodeInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                search = searchCodeInput.value.trim();
                currentPage = 1;
                loadCodes();
            }, 300);
        });

        filterStatusSelect.addEventListener('change', () => {
            status = filterStatusSelect.value;
            currentPage = 1;
            loadCodes();
        });

        prevPageBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                loadCodes();
            }
        });

        nextPageBtn.addEventListener('click', () => {
            currentPage++;
            loadCodes();
        });

        // Trigger load on codes tab button click
        const codesTabBtn = document.querySelector('[data-tab="codes"]');
        if (codesTabBtn) {
            codesTabBtn.addEventListener('click', () => {
                loadCodes();
            });
        }
    }
}

function setText(selector, value) {
    const element = document.querySelector(selector);
    if (element) element.textContent = value;
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value;
    return div.innerHTML;
}

function drawChart(context, canvas, candidates) {
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    context.setTransform(dpr, 0, 0, dpr, 0, 0);
    context.clearRect(0, 0, rect.width, rect.height);

    const totalSuara = candidates.reduce(
        (sum, candidate) => sum + Number(candidate.total_votes),
        0
    );
    const totalUntukGrafik = totalSuara || 1;
    const colors = ['#54d6ff', '#f6c85f', '#8bffb0'];
    let start = -Math.PI / 2;
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    const radius = Math.min(rect.width, rect.height) * 0.34;

    candidates.forEach((candidate, index) => {
        const slice = (Number(candidate.total_votes) / totalUntukGrafik) * Math.PI * 2;
        context.beginPath();
        context.moveTo(centerX, centerY);
        context.arc(centerX, centerY, radius, start, start + slice);
        context.closePath();
        context.fillStyle = colors[index % colors.length];
        context.fill();
        start += slice;
    });

    context.beginPath();
    context.arc(centerX, centerY, radius * 0.58, 0, Math.PI * 2);
    context.fillStyle = '#ffffff';
    context.fill();

    context.fillStyle = '#07172f';
    context.font = '700 22px Poppins, sans-serif';
    context.textAlign = 'center';
    context.fillText(String(totalSuara), centerX, centerY - 4);
    context.font = '600 12px Poppins, sans-serif';
    context.fillStyle = '#64748b';
    context.fillText('Suara Masuk', centerX, centerY + 18);
}