<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$flash = get_flash();
if ($flash):
    $type = $flash['type'] ?? 'success';
    $message = $flash['message'] ?? '';
    
    // Choose theme colors based on type
    $accentColor = '#10b981'; // Success green
    $iconName = 'check_circle';
    $bgStyle = 'background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #065f46;';
    
    if ($type === 'danger' || $type === 'error') {
        $accentColor = '#ef4444'; // Error red
        $iconName = 'cancel';
        $bgStyle = 'background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #991b1b;';
    } elseif ($type === 'warning' || $type === 'info') {
        $accentColor = '#f59e0b'; // Warning amber
        $iconName = 'warning';
        $bgStyle = 'background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); color: #78350f;';
    }
?>
<!-- Premium Aether-Glass Toast Notification -->
<div id="premium-glass-toast" class="fixed top-6 right-6 z-[9999] max-w-sm w-full bg-white/40 backdrop-blur-xl border border-white/50 rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.08)] flex items-start gap-md p-md transition-all duration-500 ease-out translate-y-[-20px] opacity-0 pointer-events-none select-none">
    
    <!-- Left Accent Glowing Bar -->
    <div class="absolute left-0 top-3 bottom-3 w-[4px] rounded-r-full" style="background-color: <?= $accentColor ?>; box-shadow: 0 0 10px 2px <?= $accentColor ?>40;"></div>
    
    <!-- Status Icon -->
    <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center" style="<?= $bgStyle ?>">
        <span class="material-symbols-outlined text-[24px] font-bold"><?= $iconName ?></span>
    </div>
    
    <!-- Toast Content -->
    <div class="flex-grow flex flex-col gap-xs pt-1 pr-6">
        <h4 class="font-headline font-bold text-[14px] uppercase tracking-wider" style="color: <?= $accentColor ?>;">
            <?= ucfirst($type) ?> Message
        </h4>
        <p class="font-body-md text-body-md text-on-surface font-medium leading-relaxed">
            <?= htmlspecialchars($message) ?>
        </p>
    </div>
    
    <!-- Close Icon -->
    <button onclick="dismissGlassToast()" class="absolute top-md right-md text-on-surface-variant hover:text-primary transition-colors hover:scale-115 active:scale-95 duration-150">
        <span class="material-symbols-outlined text-[18px]">close</span>
    </button>
</div>

<style>
    /* Premium Slide-in and glow animations */
    .glass-toast-visible {
        transform: translateY(0) !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toast = document.getElementById("premium-glass-toast");
        if (toast) {
            // Trigger animation frame for CSS transitions
            requestAnimationFrame(() => {
                toast.classList.add("glass-toast-visible");
            });

            // Automatically hide the toast after 5.5 seconds
            setTimeout(() => {
                dismissGlassToast();
            }, 5500);
        }
    });

    function dismissGlassToast() {
        const toast = document.getElementById("premium-glass-toast");
        if (toast) {
            toast.classList.remove("glass-toast-visible");
            // Remove from DOM after transition completes
            setTimeout(() => {
                toast.remove();
            }, 500);
        }
    }
</script>
<?php endif; ?>
