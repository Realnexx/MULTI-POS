<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= $title ?? 'POS Cashier | Stacey Halu Halu' ?></title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-container-lowest": "#ffffff",
                        "on-secondary": "#ffffff",
                        "on-surface": "#191c1d",
                        "tertiary-fixed-dim": "#ffba27",
                        "secondary-fixed-dim": "#e0b6ff",
                        "background": "#f8f9fa",
                        "on-background": "#191c1d",
                        "surface-tint": "#ba1340",
                        "surface-container": "#edeeef",
                        "secondary-fixed": "#f2daff",
                        "on-secondary-fixed-variant": "#6b00af",
                        "inverse-on-surface": "#f0f1f2",
                        "on-secondary-fixed": "#2e004e",
                        "on-primary-fixed": "#40000f",
                        "on-primary": "#ffffff",
                        "inverse-surface": "#2e3132",
                        "outline-variant": "#e2bec0",
                        "tertiary-container": "#996c00",
                        "surface": "#f8f9fa",
                        "surface-variant": "#e1e3e4",
                        "primary-fixed": "#ffdadb",
                        "primary-fixed-dim": "#ffb2b8",
                        "outline": "#8e6f71",
                        "inverse-primary": "#ffb2b8",
                        "error": "#ba1a1a",
                        "error-container": "#ffdad6",
                        "on-tertiary": "#ffffff",
                        "surface-container-high": "#e7e8e9",
                        "tertiary-fixed": "#ffdea9",
                        "surface-dim": "#d9dadb",
                        "on-secondary-container": "#fffbff",
                        "secondary-container": "#9e49e3",
                        "primary": "#b60e3d",
                        "secondary": "#8429c8",
                        "surface-container-highest": "#e1e3e4",
                        "on-error": "#ffffff",
                        "tertiary": "#7a5500",
                        "on-primary-container": "#fffbff",
                        "on-surface-variant": "#5a4042",
                        "surface-container-low": "#f3f4f5",
                        "on-tertiary-fixed-variant": "#5e4100",
                        "on-primary-fixed-variant": "#91002d"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px",
                        "pill": "9999px"
                    },
                    "spacing": {
                        "margin-mobile": "16px",
                        "margin-desktop": "32px",
                        "base": "8px",
                        "md": "24px",
                        "gutter": "24px",
                        "sm": "12px",
                        "lg": "40px",
                        "xs": "4px",
                        "xl": "64px"
                    },
                    "fontFamily": {
                        "headline-lg": ["Montserrat"],
                        "label-md": ["Inter"],
                        "headline-md": ["Montserrat"],
                        "body-lg": ["Inter"],
                        "label-sm": ["Inter"],
                        "display-lg": ["Montserrat"],
                        "body-md": ["Inter"]
                    },
                    "fontSize": {
                        "headline-lg": ["32px", {"lineHeight": "1.3", "fontWeight": "600"}],
                        "label-md": ["14px", {"lineHeight": "1", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "headline-md": ["24px", {"lineHeight": "1.4", "fontWeight": "600"}],
                        "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "label-sm": ["12px", {"lineHeight": "1", "fontWeight": "500"}],
                        "display-lg": ["48px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "body-md": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}]
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .custom-shadow { box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .raised-shadow { box-shadow: 0 12px 30px rgba(182,14,61,0.12); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Branch modal styles */
        #branchModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.72);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 16px;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        #branchModal.is-open {
            display: flex;
            opacity: 1;
        }
        #branchModalInner {
            background: #fff;
            width: 100%;
            max-width: 580px;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.25);
            padding: 40px 32px;
            position: relative;
            text-align: center;
            transform: scale(0.95);
            transition: transform 0.25s ease;
        }
        #branchModal.is-open #branchModalInner {
            transform: scale(1);
        }

        #branchModal.is-open #branchModalInner {
            transform: scale(1);
        }
    </style>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="<?= url('assets/js/main.js') ?>?v=1.5"></script>

    <script>
    // Defined in <head> — guaranteed to exist before any click is possible
    function openBranchModal() {
        var m = document.getElementById('branchModal');
        if (!m) { console.error('[POS] #branchModal not found in DOM'); return; }
        m.classList.add('is-open');
    }
    function closeBranchModal() {
        var m = document.getElementById('branchModal');
        if (!m) return;
        m.classList.remove('is-open');
    }

    function closeBranchModal() {
        var m = document.getElementById('branchModal');
        if (!m) return;
        m.classList.remove('is-open');
    }
    </script>
</head>
<body class="bg-background text-on-background font-body-md h-screen flex flex-col overflow-hidden">
<?php require_once __DIR__ . '/../partials/toast.php'; ?>
<?php
// Always resolve admin status first — needed for both the Switch button and modal data
$layout_is_admin = User::has_permission($_SESSION['user']['id'], 'manage_users');
if (!isset($branches)) {
    $branches = [];
    if ($layout_is_admin) {
        require_once __DIR__ . '/../../models/Branch.php';
        $branches = Branch::get_all_active();
    }
}
?>

<!-- ═══ BRANCH SELECTION MODAL ══════════════════════════════════════════════ -->
<div id="branchModal">
    <div id="branchModalInner">
        <?php if (!empty($_SESSION['pos_branch_id'])): ?>
        <button onclick="closeBranchModal()"
                style="position:absolute;top:12px;right:12px;width:36px;height:36px;border-radius:50%;border:none;background:#f3f4f5;cursor:pointer;display:flex;align-items:center;justify-content:center;">
            <span class="material-symbols-outlined" style="font-size:18px;color:#5a4042;">close</span>
        </button>
        <?php endif; ?>

        <div style="width:72px;height:72px;background:#fff0f1;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;border:2px solid #ffb2b8;">
            <span class="material-symbols-outlined" style="font-size:36px;color:#b60e3d;">hub</span>
        </div>

        <h3 style="font-size:20px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin:0 0 8px;color:#191c1d;">Select POS Station</h3>
        <p style="font-size:13px;color:#5a4042;margin:0 0 28px;">Choose a branch to initialize the point-of-sale terminal for this session.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;max-height:380px;overflow-y:auto;">
            <?php if (empty($branches)): ?>
                <div style="grid-column:1/-1;padding:40px 0;opacity:.6;">
                    <span class="material-symbols-outlined" style="font-size:36px;display:block;margin-bottom:8px;">warning</span>
                    <p style="font-size:12px;font-weight:700;margin:0;">No active branches found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($branches as $branch): ?>
                    <a href="<?= url('?route=pos&select_branch=' . (int)$branch['id']) ?>" style="text-decoration:none;">
                        <div style="padding:20px 12px;border-radius:14px;background:#f8f9fa;border:2px solid #e2bec0;text-align:center;transition:all .2s;cursor:pointer;"
                             onmouseover="this.style.borderColor='#b60e3d';this.style.background='#fff0f1';"
                             onmouseout="this.style.borderColor='#e2bec0';this.style.background='#f8f9fa';">
                            <span class="material-symbols-outlined" style="font-size:26px;color:#b60e3d;display:block;margin-bottom:6px;">storefront</span>
                            <h4 style="font-size:13px;font-weight:700;color:#191c1d;margin:0 0 3px;"><?= htmlspecialchars($branch['name']) ?></h4>
                            <p style="font-size:9px;color:#5a4042;text-transform:uppercase;letter-spacing:.1em;margin:0;">Activate Terminal</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- ════════════════════════════════════════════════════════════════════════ -->

<!-- Top Navigation / Header -->
<header class="h-20 flex items-center justify-between px-margin-desktop bg-surface border-b border-outline-variant border-opacity-35 shrink-0 z-20">
    <div class="flex items-center gap-md">
        <a class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-surface-container hover:bg-primary-fixed text-primary font-bold text-xs text-decoration-none transition-all duration-300 border border-outline-variant border-opacity-25 shadow-xs cursor-pointer" href="<?= url('?route=dashboard') ?>">
            <span class="material-symbols-outlined text-[16px] font-bold">arrow_back</span>
            <span>Dashboard</span>
        </a>
        <div class="flex flex-col">
            <h1 class="font-bold text-lg text-primary leading-tight m-0 font-headline-md"><?= htmlspecialchars($store_name ?? 'Stacey Halu') ?></h1>
            <div class="flex items-center gap-1.5 mt-0.5">
                <?php
                $branch_name = 'Main Station';
                if (!empty($_SESSION['pos_branch_id'])) {
                    require_once __DIR__ . '/../../models/Branch.php';
                    $b = Branch::find_by_id($_SESSION['pos_branch_id']);
                    $branch_name = $b['name'] ?? 'Main Station';
                } elseif (!empty($_SESSION['user']['branch'])) {
                    $branch_name = $_SESSION['user']['branch'];
                }
                ?>
                <div class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-primary/5 border border-primary/10">
                    <span class="material-symbols-outlined text-[12px] text-primary font-bold">storefront</span>
                    <span class="font-label-sm text-[9px] text-on-surface-variant font-bold uppercase tracking-widest"><?= htmlspecialchars($branch_name) ?></span>
                </div>
                <?php if ($layout_is_admin): ?>
                    <button onclick="openBranchModal()"
                            class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-container-high hover:bg-primary-fixed hover:text-primary transition-all border-0 cursor-pointer text-on-surface-variant">
                        <span class="material-symbols-outlined text-[12px]">sync_alt</span>
                        <span class="font-label-sm text-[9px] font-bold uppercase tracking-widest">Switch</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-md">
        <!-- Live Clock Widget -->
        <div class="hidden lg:flex items-center gap-2 bg-surface-container-low px-4 py-2 rounded-full border border-outline-variant border-opacity-35 text-on-surface-variant font-label-md text-xs">
            <span class="material-symbols-outlined text-[16px] text-primary animate-pulse">schedule</span>
            <span id="liveClock" class="font-bold text-[11px] opacity-80">Loading clock...</span>
        </div>

        <div class="relative w-60 xl:w-72">
            <form action="<?= url('') ?>" method="GET" class="mb-0">
                <input type="hidden" name="route" value="pos">
                <?php if (!empty($_GET['category_id'])): ?>
                    <input type="hidden" name="category_id" value="<?= htmlspecialchars($_GET['category_id']) ?>">
                <?php endif; ?>
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] opacity-65">search</span>
                <input class="w-full bg-surface-container-low border border-outline-variant border-opacity-40 rounded-full pl-10 pr-4 py-2 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-xs text-on-surface font-body-md shadow-none transition-all duration-300"
                       placeholder="Search menu..." type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"/>
            </form>
        </div>

        <div class="h-8 w-px bg-outline-variant opacity-40 mx-2"></div>

        <div class="flex items-center gap-sm pl-2">
            <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-on-primary font-bold text-xs"><?= strtoupper(substr($_SESSION['user']['name'], 0, 2)) ?></div>
            <span class="font-label-md text-xs text-on-surface hidden sm:inline font-bold"><?= htmlspecialchars($_SESSION['user']['name']) ?></span>
        </div>
    </div>
</header>

<!-- Main Content Canvas -->
<?= $content ?? '' ?>

<script>
// Live clock
(function() {
    function tick() {
        var el = document.getElementById('liveClock');
        if (!el) return;
        el.textContent = new Date().toLocaleDateString('en-US', {
            weekday:'short', year:'numeric', month:'short', day:'numeric',
            hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true
        });
    }
    setInterval(tick, 1000);
    tick();
})();

<?php if (!empty($layout_is_admin) && empty($_SESSION['pos_branch_id'])): ?>
// Auto-open modal for admin with no branch selected
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(openBranchModal, 400);
});
<?php endif; ?>
</script>

</body>
</html>
