<?php
    session_start();
    
    // Auth check - redirect to login if not authenticated
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>People's Choice Rankings - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
    <style>
        .ranking-card { border: none; border-radius: 20px; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); overflow: hidden; }
        .ranking-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; }
        .podium-container { display: flex; align-items: flex-end; justify-content: center; gap: 20px; padding: 40px 0; margin-bottom: 30px; }
        .podium-item { text-align: center; width: 200px; }
        .podium-rank { width: 100%; border-radius: 15px 15px 0 0; position: relative; padding-top: 20px; }
        .podium-1 { height: 220px; background: linear-gradient(to top, #fcd34d, #fbbf24); color: #92400e; }
        .podium-2 { height: 180px; background: linear-gradient(to top, #e2e8f0, #cbd5e1); color: #475569; }
        .podium-3 { height: 150px; background: linear-gradient(to top, #fdba74, #f97316); color: #9a3412; }
        .cand-avatar { width: 100px; height: 100px; border-radius: 50%; border: 5px solid white; object-fit: cover; margin-bottom: -50px; position: relative; z-index: 2; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .rank-number { position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); font-size: 3rem; font-weight: 900; opacity: 0.3; }
        .table-ranking img { width: 45px; height: 45px; border-radius: 10px; object-fit: cover; margin-right: 15px; }
        .vote-badge-podium { background: rgba(255,255,255,0.9); padding: 5px 15px; border-radius: 50px; font-weight: 800; font-size: 1.1rem; display: inline-block; margin-top: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .live-dot { width: 8px; height: 8px; background: #ef4444; border-radius: 50%; display: inline-block; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.3); } }
        .rank-badge-1 { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: white; }
        .rank-badge-2 { background: linear-gradient(135deg, #94a3b8, #64748b); color: white; }
        .rank-badge-3 { background: linear-gradient(135deg, #fb923c, #f97316); color: white; }
        #podiumContainer .podium-item { animation: slideUp 0.5s ease-out both; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .empty-state-rankings { padding: 80px 0; text-align: center; color: #94a3b8; }
        @media (max-width: 768px) {
            .podium-container { flex-direction: column; align-items: center; gap: 60px; padding-top: 50px; }
            .podium-rank { height: auto !important; padding-bottom: 30px; border-radius: 15px; }
            /* Mobile Table Stacking */
            .table-responsive-stack thead { display: none; }
            .table-responsive-stack tr { display: flex; flex-direction: column; border-bottom: 2px solid #f1f5f9; padding: 15px 0; }
            .table-responsive-stack td { display: flex; justify-content: space-between; align-items: center; padding: 10px 15px !important; border: none !important; width: 100% !important; text-align: right; }
            .table-responsive-stack td::before { content: attr(data-label); font-weight: bold; color: #64748b; text-align: left; padding-right: 15px; flex-shrink: 0; }
            .table-responsive-stack td .d-flex { justify-content: flex-end; width: 100%; text-align: right; }
            .table-responsive-stack td .d-flex .text-start { text-align: right !important; }
        }
    </style>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'components/sidebar.php'; ?>
        
        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid">
                    <?php include 'components/header.php'; ?>

                    <div class="top-part card bg-light-info shadow-none position-relative overflow-hidden mb-4">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-semibold mb-8">People's Choice Rankings</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Public Rankings</li>
                                        </ol>
                                    </nav>
                                </div>
                                <div class="col-3">
                                    <div class="text-center mb-n5">
                                        <img src="src/assets/images/backgrounds/rocket.png" alt="" class="img-fluid mb-n4" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                <span class="live-dot"></span>
                                <span class="badge bg-danger-subtle text-danger fw-bolder px-3 py-2 rounded-pill">LIVE LEADERBOARD</span>
                            </div>
                            <h2 class="fw-bolder" id="competitionNameText">Select a Competition</h2>
                            <p class="text-muted mb-4">Real-time ranking of candidates based on audience votes.</p>
                            
                            <div class="row justify-content-center">
                                <div class="col-md-6 col-lg-4">
                                    <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                                        <span class="input-group-text bg-white border-0 ps-3">
                                            <i class="ti ti-trophy text-primary"></i>
                                        </span>
                                        <select class="form-select border-0 py-2" id="competitionSelect" style="box-shadow: none;">
                                            <option value="" disabled selected>Loading competitions...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Podium Section -->
                    <div id="podiumContainer" class="podium-container">
                        <div class="empty-state-rankings w-100">
                            <i class="ti ti-trophy" style="font-size: 4rem; opacity: 0.2;"></i>
                            <p class="mt-3">Select a competition to see the live podium</p>
                        </div>
                    </div>

                    <div class="card w-100 shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                                <h5 class="card-title fw-semibold mb-0">Full Ranking List</h5>
                                <div class="d-flex flex-column flex-md-row gap-2 w-100 w-md-auto align-items-center">
                                    <small class="text-muted" id="lastRefreshedText"></small>
                                    <button class="btn btn-outline-primary btn-sm rounded-pill w-100 w-md-auto" id="btnRefresh">
                                        <i class="ti ti-refresh me-1"></i> Refresh
                                    </button>
                                    <button class="btn btn-primary btn-sm rounded-pill w-100 w-md-auto" id="btnPrint">
                                        <i class="ti ti-printer me-1"></i> Print
                                    </button>
                                </div>
                            </div>
                            <div id="rankingsTableWrapper">
                                <div class="empty-state-rankings">
                                    <i class="ti ti-list" style="font-size: 3rem; opacity: 0.2;"></i>
                                    <p class="mt-3">Select a competition to view rankings</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php include 'components/footer.php'; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'components/scripts.php'; ?>
    <script>
        $(document).ready(function() {
            let currentCompetitionId = null;
            let autoRefreshTimer = null;

            // ─── Load Competition Dropdown ───────────────────────────────────
            function loadCompetitions() {
                $.ajax({
                    url: 'endpoints/voting_handler.php',
                    method: 'POST',
                    data: { action: 'get_active_competitions' },
                    success: function(response) {
                        const select = $('#competitionSelect');
                        select.empty().append('<option value="" disabled selected>Select a competition...</option>');
                        if (response.status === 'success' && response.data.length > 0) {
                            response.data.forEach(comp => {
                                select.append(`<option value="${comp.id}">${comp.title}</option>`);
                            });
                        } else {
                            select.append('<option value="" disabled>No active competitions found</option>');
                        }
                    }
                });
            }

            // ─── Load Rankings ────────────────────────────────────────────────
            function loadRankings(compId) {
                if (!compId) return;

                $.ajax({
                    url: 'endpoints/voting_handler.php',
                    method: 'POST',
                    data: { action: 'get_rankings', competition_id: compId },
                    success: function(response) {
                        if (response.status === 'success') {
                            renderPodium(response.data);
                            renderTable(response.data);
                            const now = new Date();
                            $('#lastRefreshedText').text('Last updated: ' + now.toLocaleTimeString());
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        $('#rankingsTableWrapper').html('<div class="empty-state-rankings"><i class="ti ti-wifi-off" style="font-size:3rem;opacity:0.2"></i><p class="mt-2">Failed to load data</p></div>');
                    }
                });
            }

            // ─── Photo URL Helper ─────────────────────────────────────────────
            function getPhotoUrl(c) {
                if (!c) return '';
                const fallback = `https://ui-avatars.com/api/?name=${encodeURIComponent(c.first_name + '+' + c.last_name)}&background=6366f1&color=fff`;
                if (!c.photo_url) return fallback;
                if (c.photo_url.startsWith('http')) return c.photo_url;
                // Photos live in app/uploads/, page is app/admin/ => go up one level
                return '../uploads/' + c.photo_url.replace(/^uploads\//, '');
            }

            // ─── Build Podium HTML for a group ──────────────────────────────────
            function buildPodium(candidates, label) {
                if (!candidates.length) return '';
                const caption = label ? `<h6 class="fw-bold text-primary mb-3"><i class="ti ti-trophy me-1"></i>${label}</h6>` : '';
                const top3 = candidates.slice(0, 3);
                const p = i => getPhotoUrl(top3[i]);
                const n = c => c ? `${c.first_name} ${c.last_name}` : '';
                const v = c => c ? c.total_votes.toLocaleString() : '0';
                let html = `<div class="text-center mb-2">${caption}</div><div class="podium-container justify-content-center flex-wrap">`;
                if (top3[1]) html += `
                    <div class="podium-item" style="animation-delay:0.1s">
                        <img src="${p(1)}" class="cand-avatar" alt="${n(top3[1])}">
                        <div class="podium-rank podium-2 shadow-sm">
                            <div class="p-3 mt-5"><h5 class="fw-bold mb-0 fs-6">${n(top3[1])}</h5>
                            <div class="vote-badge-podium">${v(top3[1])} <small>Votes</small></div></div>
                            <div class="rank-number text-dark">2</div>
                        </div>
                    </div>`;
                if (top3[0]) html += `
                    <div class="podium-item order-first order-md-0" style="animation-delay:0s">
                        <img src="${p(0)}" class="cand-avatar" alt="${n(top3[0])}" style="width:120px;height:120px;margin-bottom:-60px;">
                        <div class="podium-rank podium-1 shadow-lg">
                            <div class="p-3 mt-5 pt-4"><i class="ti ti-crown fs-7 mb-2"></i>
                            <h4 class="fw-bold mb-0 fs-6">${n(top3[0])}</h4>
                            <div class="vote-badge-podium text-primary">${v(top3[0])} <small>Votes</small></div></div>
                            <div class="rank-number">1</div>
                        </div>
                    </div>`;
                if (top3[2]) html += `
                    <div class="podium-item" style="animation-delay:0.2s">
                        <img src="${p(2)}" class="cand-avatar" alt="${n(top3[2])}">
                        <div class="podium-rank podium-3 shadow-sm">
                            <div class="p-3 mt-5"><h5 class="fw-bold mb-0 fs-6">${n(top3[2])}</h5>
                            <div class="vote-badge-podium">${v(top3[2])} <small>Votes</small></div></div>
                            <div class="rank-number text-dark">3</div>
                        </div>
                    </div>`;
                html += '</div>';
                return html;
            }

            // ─── Render Podium (gender-aware) ─────────────────────────────────
            function renderPodium(candidates) {
                const container = $('#podiumContainer');
                if (!candidates.length) {
                    container.html('<div class="empty-state-rankings w-100"><i class="ti ti-trophy" style="font-size:4rem;opacity:0.2"></i><p class="mt-3">No votes cast yet</p></div>');
                    return;
                }

                const female = candidates.filter(c => (c.gender || '').toLowerCase() === 'female');
                const male   = candidates.filter(c => (c.gender || '').toLowerCase() === 'male');
                const mixed  = candidates.filter(c => !['female','male'].includes((c.gender || '').toLowerCase()));

                const hasBoth = female.length > 0 && male.length > 0;

                if (hasBoth) {
                    container.html(`
                        <div class="row w-100 gx-3">
                            <div class="col-md-6 border-end">${buildPodium(female, '♀ Female Rankings')}</div>
                            <div class="col-md-6">${buildPodium(male, '♂ Male Rankings')}</div>
                        </div>`);
                } else {
                    container.html(buildPodium([...female, ...male, ...mixed], null));
                }
            }

            // ─── Build Rankings Table for a group ────────────────────────────
            function buildTable(candidates, label) {
                if (!candidates.length) return '';
                const caption = label ? `<h6 class="fw-bold text-primary mb-3">${label}</h6>` : '';
                let rows = '';
                candidates.forEach((c, i) => {
                    const rank = i + 1;
                    const photo = getPhotoUrl(c);
                    let badgeClass = 'bg-light text-muted';
                    if (rank === 1) badgeClass = 'rank-badge-1';
                    else if (rank === 2) badgeClass = 'rank-badge-2';
                    else if (rank === 3) badgeClass = 'rank-badge-3';
                    const popularity = c.popularity || 0;
                    const barColor = rank === 1 ? 'bg-warning' : rank === 2 ? 'bg-secondary' : rank === 3 ? 'bg-danger' : 'bg-primary';
                    rows += `
                        <tr>
                            <td class="ps-4" data-label="Rank"><span class="badge ${badgeClass} rounded-pill px-3 py-2">${rank}</span></td>
                            <td data-label="Candidate">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex flex-column text-start d-md-none me-2">
                                        <h6 class="fw-bold mb-0">#${String(c.candidate_number).padStart(2,'0')} ${c.first_name} ${c.last_name}</h6>
                                        <span class="text-muted small">${c.department || '—'}</span>
                                    </div>
                                    <img src="${photo}" alt="${c.first_name}" style="width:45px;height:45px;border-radius:10px;object-fit:cover;">
                                    <div class="d-none d-md-block">
                                        <h6 class="fw-bold mb-0">#${String(c.candidate_number).padStart(2,'0')} ${c.first_name} ${c.last_name}</h6>
                                        <span class="text-muted small">${c.department || '—'}</span>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Total Votes"><h6 class="fw-bold mb-0">${c.total_votes.toLocaleString()}</h6></td>
                            <td data-label="Popularity">
                                <div class="d-flex align-items-center gap-2" style="min-width:140px">
                                    <div class="progress flex-grow-1" style="height:6px">
                                        <div class="progress-bar ${barColor}" style="width:${popularity}%"></div>
                                    </div>
                                    <span class="small fw-bold">${popularity}%</span>
                                </div>
                            </td>
                            <td class="text-muted small" data-label="Last Vote">${c.last_vote_at}</td>
                        </tr>`;
                });
                return `
                    ${caption}
                    <div class="table-responsive mb-4">
                        <table class="table align-middle mb-0 table-hover table-ranking table-responsive-stack">
                            <thead class="bg-light text-muted">
                                <tr><th class="ps-4">Rank</th><th>Candidate</th><th>Total Votes</th><th>Popularity</th><th>Last Vote</th></tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>`;
            }

            // ─── Render Table (gender-aware) ─────────────────────────────────
            function renderTable(candidates) {
                if (!candidates.length) {
                    $('#rankingsTableWrapper').html('<div class="empty-state-rankings"><p class="mt-3">No candidates found.</p></div>');
                    return;
                }

                const female = candidates.filter(c => (c.gender || '').toLowerCase() === 'female');
                const male   = candidates.filter(c => (c.gender || '').toLowerCase() === 'male');
                const mixed  = candidates.filter(c => !['female','male'].includes((c.gender || '').toLowerCase()));
                const hasBoth = female.length > 0 && male.length > 0;

                let html = '';
                if (hasBoth) {
                    html = buildTable(female, '♀ Female Division') + buildTable(male, '♂ Male Division');
                } else {
                    html = buildTable([...female, ...male, ...mixed], null);
                }
                $('#rankingsTableWrapper').html(html);
            }

            // ─── Event Handlers ───────────────────────────────────────────────
            $('#competitionSelect').on('change', function() {
                currentCompetitionId = $(this).val();
                const name = $(this).find('option:selected').text();
                $('#competitionNameText').fadeOut(150, function() {
                    $(this).text(name).fadeIn(150);
                });
                document.title = `${name} - Rankings`;

                // Clear existing auto-refresh
                if (autoRefreshTimer) clearInterval(autoRefreshTimer);

                loadRankings(currentCompetitionId);

                // Auto-refresh every 30 seconds
                autoRefreshTimer = setInterval(() => loadRankings(currentCompetitionId), 30000);
            });

            $('#btnRefresh').on('click', function() {
                if (!currentCompetitionId) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Please select a competition first', showConfirmButton: false, timer: 2000 });
                    return;
                }
                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Refreshing...');
                loadRankings(currentCompetitionId);
                setTimeout(() => btn.prop('disabled', false).html('<i class="ti ti-refresh me-1"></i> Refresh'), 1000);
            });

            $('#btnPrint').on('click', function() {
                if (!currentCompetitionId) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Please select a competition first', showConfirmButton: false, timer: 2000 });
                    return;
                }
                const url = `print-audience-rankings.php?competition_id=${currentCompetitionId}`;
                window.open(url, '_blank');
            });

            // ─── Init ─────────────────────────────────────────────────────────
            loadCompetitions();
        });
    </script>
</body>

</html>
