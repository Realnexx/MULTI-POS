<?php
// Ensure branches are available for the modal
if (!isset($branches) || empty($branches)) {
    require_once __DIR__ . '/../../models/Branch.php';
    $branches = Branch::get_all_active();
}
?>

<!-- Branch Selection Modal Overlay -->
<div id="branchModal" class="fixed inset-0 bg-black/75 backdrop-blur-md z-[70] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-surface-container-lowest w-full max-w-[600px] rounded-3xl border border-outline-variant border-opacity-30 shadow-2xl p-8 relative mx-4 text-center transform scale-95 transition-transform duration-300" id="branchModalContent">
        <!-- Close button for administrators who are just switching -->
        <button onclick="closeBranchModal()" class="absolute top-4 right-4 w-10 h-10 rounded-full flex items-center justify-center hover:bg-surface-container transition-colors border-0 cursor-pointer text-on-surface-variant">
            <span class="material-symbols-outlined">close</span>
        </button>

        <div class="w-20 h-20 bg-primary-fixed-dim/40 rounded-full flex items-center justify-center text-primary shadow-md mx-auto mb-6 border border-primary-fixed-dim">
            <span class="material-symbols-outlined text-4xl font-black animate-pulse">hub</span>
        </div>
        
        <h3 class="font-extrabold text-2xl text-on-surface mb-2 font-headline-md uppercase tracking-wide">Select POS Station</h3>
        <p class="font-label-sm text-sm text-on-surface-variant opacity-85 mb-8 px-4 leading-relaxed">
            Please choose a branch to initialize the point-of-sale terminal for this session.
        </p>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[400px] overflow-y-auto px-2 no-scrollbar">
            <?php if (empty($branches)): ?>
                <div class="col-span-full py-10 opacity-60">
                    <span class="material-symbols-outlined text-4xl mb-2">warning</span>
                    <p class="text-xs font-bold">No active branches found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($branches as $branch): ?>
                    <a href="?route=pos&select_branch=<?= $branch['id'] ?>" class="text-decoration-none group">
                        <div class="p-6 rounded-2xl bg-surface-container-low border border-outline-variant border-opacity-30 hover:border-primary hover:bg-primary-fixed-dim/20 transition-all text-center">
                            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-primary shadow-sm mx-auto mb-3 group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined text-2xl">storefront</span>
                            </div>
                            <h4 class="font-bold text-base text-on-surface mb-1"><?= htmlspecialchars($branch['name']) ?></h4>
                            <p class="text-[10px] text-on-surface-variant opacity-60 uppercase font-black tracking-widest leading-none">Activate Terminal</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function openBranchModal() {
    const modal = document.getElementById('branchModal');
    const content = document.getElementById('branchModalContent');
    if (!modal || !content) return;
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.add('opacity-100');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }, 10);
}

function closeBranchModal() {
    const modal = document.getElementById('branchModal');
    const content = document.getElementById('branchModalContent');
    if (!modal || !content) return;

    modal.classList.remove('opacity-100');
    content.classList.remove('scale-100');
    content.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}
</script>
