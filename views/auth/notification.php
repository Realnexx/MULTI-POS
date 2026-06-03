<?php
$title = htmlspecialchars($title ?? 'Notification | Stacey Halu Halu');
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= $title ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #edeeef 100%);
        }
        .font-headline {
            font-family: 'Montserrat', sans-serif;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.6);
        }
        .pulse-glow {
            animation: pulse-glow-keyframes 2s infinite alternate;
        }
        @keyframes pulse-glow-keyframes {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(182, 14, 61, 0.4);
            }
            100% {
                transform: scale(1.05);
                box-shadow: 0 0 20px 10px rgba(182, 14, 61, 0.1);
            }
        }
        .sparkle {
            animation: sparkle-anim 4s infinite linear;
        }
        @keyframes sparkle-anim {
            0% { transform: rotate(0deg) scale(0.9); opacity: 0.7; }
            50% { transform: rotate(180deg) scale(1.1); opacity: 1; }
            100% { transform: rotate(360deg) scale(0.9); opacity: 0.7; }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-margin-mobile relative overflow-hidden select-none bg-surface">

    <!-- Decorative background elements -->
    <div class="absolute top-[-20%] left-[-10%] w-[60vw] h-[60vw] rounded-full bg-gradient-to-tr from-primary/10 to-secondary/5 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-20%] right-[-10%] w-[60vw] h-[60vw] rounded-full bg-gradient-to-bl from-tertiary/10 to-primary/5 blur-[120px] pointer-events-none"></div>

    <!-- Confetti / Sparkle visual overlay -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <span class="material-symbols-outlined absolute text-[28px] text-primary/20 sparkle" style="top: 15%; left: 20%; animation-delay: 0.5s;">sparkles</span>
        <span class="material-symbols-outlined absolute text-[36px] text-secondary/20 sparkle" style="top: 25%; right: 25%; animation-delay: 1.2s;">favorite</span>
        <span class="material-symbols-outlined absolute text-[24px] text-tertiary/20 sparkle" style="bottom: 20%; left: 30%; animation-delay: 2s;">star</span>
        <span class="material-symbols-outlined absolute text-[30px] text-primary/20 sparkle" style="bottom: 15%; right: 15%; animation-delay: 0.1s;">filter_hdr</span>
    </div>

    <!-- Glassmorphic Card Container -->
    <div class="w-full max-w-[520px] glass-panel rounded-3xl p-lg md:p-xl shadow-[0_20px_60px_rgba(0,0,0,0.06)] relative z-10 text-center flex flex-col items-center gap-md" data-aos="zoom-in">
        
        <!-- Status Indicator Icon -->
        <div class="w-24 h-24 rounded-full flex items-center justify-center relative mb-sm">
            <?php if ($type === 'success'): ?>
                <!-- Success State -->
                <div class="absolute inset-0 bg-green-500/10 rounded-full pulse-glow" style="animation-duration: 2.5s;"></div>
                <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center text-white relative z-10 shadow-lg shadow-green-500/20">
                    <span class="material-symbols-outlined text-[48px] font-bold">check</span>
                </div>
            <?php elseif ($type === 'danger' || $type === 'error'): ?>
                <!-- Error State -->
                <div class="absolute inset-0 bg-error/10 rounded-full pulse-glow" style="animation-duration: 2.5s;"></div>
                <div class="w-20 h-20 bg-error rounded-full flex items-center justify-center text-white relative z-10 shadow-lg shadow-error/20">
                    <span class="material-symbols-outlined text-[48px] font-bold">close</span>
                </div>
            <?php else: ?>
                <!-- Default / Info State -->
                <div class="absolute inset-0 bg-primary/10 rounded-full pulse-glow" style="animation-duration: 2.5s;"></div>
                <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center text-white relative z-10 shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-[48px] font-bold">info</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Notification Typography -->
        <div class="flex flex-col gap-sm">
            <h1 class="font-headline text-[24px] md:text-[28px] font-black text-on-surface tracking-tight leading-tight">
                <?= htmlspecialchars($title) ?>
            </h1>
            <p class="font-body-md text-body-md text-on-surface-variant font-medium max-w-sm mx-auto leading-relaxed">
                <?= htmlspecialchars($message) ?>
            </p>
        </div>

        <!-- Custom Details Sub-Card (Visual Aether-Glass element) -->
        <div class="w-full bg-white/30 rounded-2xl p-md border border-white/50 text-left flex flex-col gap-xs my-sm">
            <div class="flex items-center gap-xs text-primary font-bold font-label-md text-label-md">
                <span class="material-symbols-outlined text-[18px]">verified_user</span>
                <span>Account Membership Status</span>
            </div>
            <div class="flex justify-between items-center mt-xs">
                <span class="text-on-surface-variant text-[14px]">Authorized Member</span>
                <span class="bg-primary/15 text-primary text-[12px] px-sm py-xs rounded-full font-black uppercase tracking-wider">Club Elite</span>
            </div>
        </div>

        <!-- Action Redirect Button -->
        <a href="<?= $redirect ?>" class="w-full bg-primary hover:bg-primary-container text-white py-sm rounded-full font-label-md text-label-md font-bold transition-all shadow-lg shadow-primary/20 hover:shadow-primary/45 active:scale-95 duration-200 flex items-center justify-center gap-xs">
            <span><?= htmlspecialchars($btn_text) ?></span>
            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
        </a>
    </div>

</body>
</html>
