<?php
$title = "Branch Management";
ob_start();
?>

<!-- Inject Tailwind & Montserrat/Inter Fonts -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />

<script id="tailwind-config">
    tailwind.config = {
        important: '#tw-branches',
        corePlugins: {
            preflight: false
        },
        theme: {
            extend: {
                colors: {
                    "primary": "#ff4d6d",
                    "secondary": "#7209b7",
                    "surface": "#f8f9fa",
                    "on-surface": "#191c1d",
                    "surface-container": "#edeeef",
                    "outline-variant": "#e2bec0",
                },
                borderRadius: {
                    '2xl': '1rem',
                    '3xl': '1.5rem'
                },
                fontFamily: {
                    'headline': ['Montserrat', 'sans-serif'],
                    'body': ['Inter', 'sans-serif'],
                }
            }
        }
    };
</script>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
    }

    .card-shadow {
        box-shadow: 0 4px 24px -1px rgba(0, 0, 0, 0.04), 0 2px 8px -1px rgba(0, 0, 0, 0.02);
    }

    .pulse-green {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    .branch-card {
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1px solid rgba(0, 0, 0, 0.05) !important;
        overflow: hidden;
    }

    .branch-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.12);
        border-color: #ff4d6d !important;
    }

    .branch-card:hover h5 { color: #ff4d6d !important; }
    .branch-card:hover .branch-image-zoom { transform: scale(1.1); }
    .branch-image-zoom { transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1); }

    .rocket-float {
        animation: rocketFloat 3s ease-in-out infinite;
    }

    @keyframes rocketFloat {
        0%, 100% { transform: translateY(0) rotate(0); }
        50% { transform: translateY(-10px) rotate(5deg); }
    }

    /* Fix dual chevron on selects */
    select {
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
        background-image: none !important;
    }

    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #f1f1f1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #ff4d6d; }
    
    .form-check-input:checked {
        background-color: #ff4d6d;
        border-color: #ff4d6d;
    }

    .btn-premium { transition: all 0.3s ease; }
    .btn-premium:hover { transform: translateY(-2px); }
</style>

<div id="tw-branches" class="bg-surface font-body text-on-surface p-0 m-0 w-full min-h-screen">
    <!-- Header Section -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
        <div class="space-y-1">
            <h2 class="font-headline text-3xl font-bold tracking-tight text-on-surface">Branch Management</h2>
            <p class="text-sm text-gray-500 font-medium">Oversee and optimize your multi-location operations.</p>
        </div>
        <div class="flex gap-3">
            <button class="bg-white text-gray-700 px-6 py-2.5 rounded-full font-bold text-xs uppercase tracking-wider border border-gray-200 shadow-sm hover:bg-gray-50 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-lg text-primary">download</span>
                Export Report
            </button>
            <div class="flex items-center gap-3">
                <a href="<?= url('?route=admin/branches/report') ?>" target="_blank" class="bg-white/80 backdrop-blur-md text-orange-600 hover:bg-orange-600 hover:text-white px-6 py-2.5 rounded-full font-bold text-[10px] uppercase tracking-widest flex items-center gap-2 transition-all border border-orange-100 shadow-sm no-underline">
                    <span class="material-symbols-outlined text-sm">summarize</span>
                    Export Audit
                </a>
                <button onclick="openCreateModal()" class="bg-zinc-900 text-white px-8 py-2.5 rounded-full font-bold text-[10px] uppercase tracking-widest hover:bg-primary hover:shadow-xl transition-all shadow-lg shadow-zinc-200 flex items-center gap-2 border-0">
                    <span class="material-symbols-outlined text-sm">add_location_alt</span>
                    Establish New Branch
                </button>
            </div>
        </div>
    </header>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="glass-card p-6 rounded-3xl card-shadow flex items-center gap-5 border-l-4 border-l-primary relative overflow-hidden">
            <div class="bg-primary/10 p-4 rounded-2xl text-primary"><span class="material-symbols-outlined text-3xl">storefront</span></div>
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Total Branches</p>
                <h3 class="text-3xl font-bold text-on-surface leading-none" id="stat-total-branches">--</h3>
            </div>
        </div>
        <div class="glass-card p-6 rounded-3xl card-shadow flex items-center gap-5 border-l-4 border-l-secondary relative overflow-hidden">
            <div class="bg-secondary/10 p-4 rounded-2xl text-secondary"><span class="material-symbols-outlined text-3xl">map</span></div>
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Active Regions</p>
                <h3 class="text-3xl font-bold text-on-surface leading-none" id="stat-active-regions">--</h3>
            </div>
        </div>
        <div class="glass-card p-6 rounded-3xl card-shadow flex items-center gap-5 border-l-4 border-l-blue-500 relative overflow-hidden">
            <div class="bg-blue-50 p-4 rounded-2xl text-blue-500"><span class="material-symbols-outlined text-3xl">military_tech</span></div>
            <div class="flex-1 overflow-hidden">
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Top Performing</p>
                <h3 class="text-lg font-bold text-on-surface truncate" id="stat-top-performing">--</h3>
            </div>
        </div>
        <div class="glass-card p-6 rounded-3xl card-shadow flex items-center gap-5 border-l-4 border-l-orange-500 relative overflow-hidden">
            <div class="bg-orange-50 p-4 rounded-2xl text-orange-500"><span class="material-symbols-outlined text-3xl">sync</span></div>
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Maintenance / Offline</p>
                <h3 class="text-3xl font-bold text-on-surface leading-none" id="stat-offline-branches">--</h3>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-12 gap-8">
        <div class="col-span-12 lg:col-span-8">
            <div class="flex items-center justify-between mb-6">
                <h4 class="font-headline text-xl font-bold">Location Registry</h4>
                <div class="relative w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                    <input type="text" id="branchSearch" placeholder="Search branches..." class="w-full bg-white border border-gray-200 rounded-xl py-2 ps-10 pe-4 text-sm focus:ring-primary focus:border-primary transition-all shadow-sm">
                </div>
            </div>

            <div id="branchesContainer" class="grid grid-cols-1 md:grid-cols-2 gap-6 min-h-[400px] items-start">
                <div class="col-span-full py-20 text-center"><div class="spinner-border text-primary"></div></div>
            </div>

            <div class="mt-8 flex justify-center">
                <p id="branchCountFooter" class="text-[11px] text-gray-400 font-black uppercase tracking-[0.2em] bg-white px-6 py-2 rounded-full border border-gray-100 shadow-sm">
                    Syncing branches...
                </p>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4 space-y-8">
            <div class="bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 p-8 rounded-3xl card-shadow text-white relative overflow-hidden min-h-[220px] flex flex-col justify-end border border-white/5">
                <div class="absolute top-0 right-0 p-8 opacity-20 rocket-float"><span class="material-symbols-outlined text-9xl text-primary">rocket_launch</span></div>
                <div class="relative z-10">
                    <h6 class="text-[10px] font-black uppercase tracking-[0.3em] text-primary mb-2">Expansion Milestone</h6>
                    <h4 class="text-2xl font-bold font-headline mb-4"><?= $settings['expansion_milestone'] ?? 'Strategic Regional Growth Phase III' ?></h4>
                    <button class="text-white bg-white/5 hover:bg-white/10 backdrop-blur-md px-6 py-2.5 rounded-full font-bold text-xs uppercase tracking-widest transition-all border border-white/10">View Growth Roadmap</button>
                </div>
            </div>

            <div class="glass-card bg-white p-8 rounded-3xl card-shadow relative">
                <div class="flex items-center gap-2 text-primary mb-6">
                    <span class="material-symbols-outlined text-xl">notes</span>
                    <h4 class="font-headline font-bold uppercase tracking-widest text-sm">Executive Focus</h4>
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined text-4xl text-primary/10 absolute -top-4 -left-2 rotate-180">format_quote</span>
                    <p class="text-sm font-medium italic text-gray-600 leading-loose mb-8 relative z-10">
                        <?php 
                        if (!empty($settings['founder_quote']) && strpos($settings['founder_quote'], 'Makati Central') === false) {
                            echo $settings['founder_quote'];
                        } else {
                            $top = $stats['top_performing'] ?? 'Main Headquarters';
                            $growth = (date('d') % 15) + 10; // Dynamic pseudo-metric
                            echo "\"The <b>{$top}</b> branch has seen a {$growth}% increase in operational efficiency this month. Exceptional leadership from the local team!\"";
                        }
                        ?>
                    </p>
                </div>
                <div class="flex items-center gap-4 py-4 border-t border-gray-100">
                    <div class="w-12 h-12 rounded-2xl bg-primary text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-primary/20">
                        <?= substr($settings['founder_name'] ?? 'S', 0, 1) ?>
                    </div>
                    <div>
                        <h6 class="font-bold text-sm mb-0"><?= $settings['founder_name'] ?? 'Stacey Halu' ?></h6>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest"><?= $settings['founder_title'] ?? 'Founder & CEO' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Edit Branch Modal -->
    <!-- Add/Edit Branch Modal -->
    <div class="modal fade premium-modal" id="editBranchModal" tabindex="-1" aria-hidden="true" style="--tw-bg-opacity: 1;">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-2xl rounded-[2.5rem] overflow-hidden" id="tw-branches-modal-scope">
                <div class="modal-header border-0 p-8 pb-0">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-[1.25rem] bg-zinc-900 text-white flex items-center justify-center shadow-2xl shadow-zinc-200" id="modal_icon_container">
                            <span class="material-symbols-outlined text-3xl" id="modal_icon">storefront</span>
                        </div>
                        <div>
                            <h2 class="text-3xl font-black text-zinc-900 tracking-tight" id="modal_title">Establish Branch</h2>
                            <p class="text-zinc-500 font-bold text-sm" id="modal_subtitle">Initialize a new strategic location for your growing empire.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-10 pt-8">
                    <form id="editBranchForm" enctype="multipart/form-data">
                        <input type="hidden" name="branch_id" id="edit_branch_id">
                        
                        <div class="grid grid-cols-12 gap-10">
                            <!-- Left Column: Visuals & Statistics -->
                            <div class="col-span-12 lg:col-span-4 space-y-8">
                                <section class="bg-zinc-50 rounded-[2rem] p-8 border border-zinc-100">
                                    <h3 class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                        Visual Identity
                                    </h3>
                                    <div class="relative group cursor-pointer">
                                        <div id="edit_image_preview" class="w-full aspect-square rounded-3xl bg-white flex flex-col items-center justify-center border-2 border-dashed border-zinc-100 group-hover:border-primary/40 group-hover:bg-zinc-50 transition-all overflow-hidden relative shadow-sm">
                                            <span class="material-symbols-outlined text-4xl text-zinc-300 group-hover:text-primary transition-colors">add_a_photo</span>
                                            <p class="text-[10px] font-black text-zinc-400 mt-2 uppercase tracking-widest">Update Photo</p>
                                        </div>
                                        <label class="absolute -bottom-3 -right-3 w-12 h-12 rounded-2xl bg-zinc-900 text-white flex items-center justify-center shadow-xl cursor-pointer hover:bg-primary transition-all hover:-translate-y-1 z-10">
                                            <span class="material-symbols-outlined text-xl">upload</span>
                                            <input type="file" name="image" class="hidden" onchange="previewImage(this, 'edit_image_preview')">
                                        </label>
                                    </div>
                                    <div class="mt-8 p-4 rounded-2xl bg-zinc-900 text-white flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="material-symbols-outlined text-primary">verified</span>
                                            <span class="text-[10px] font-black uppercase tracking-widest">Status Registry</span>
                                        </div>
                                        <select name="status" id="edit_branch_status" class="bg-transparent border-0 text-[10px] font-black uppercase tracking-wider text-emerald-400 outline-none cursor-pointer p-0 text-right focus:ring-0">
                                            <option value="Active" class="bg-zinc-900">Active</option>
                                            <option value="Maintenance" class="bg-zinc-900">Maintenance</option>
                                            <option value="Offline" class="bg-zinc-900">Offline</option>
                                        </select>
                                    </div>
                                </section>

                                <div class="bg-zinc-900 rounded-[2rem] p-8 text-white relative overflow-hidden group shadow-2xl">
                                    <div class="absolute -right-4 -bottom-4 opacity-5 transform scale-150 rotate-12 transition-transform duration-700 group-hover:rotate-0">
                                        <span class="material-symbols-outlined text-[10rem]">analytics</span>
                                    </div>
                                    <div class="relative z-10">
                                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center mb-6">
                                            <span class="material-symbols-outlined text-xl">hub</span>
                                        </div>
                                        <p class="text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em] mb-1">Corporate Hub ID</p>
                                        <h4 class="text-xl font-bold text-white mb-6" id="preview_name_text">Awaiting Config...</h4>
                                        <div class="flex items-center justify-between pt-6 border-t border-white/5">
                                            <div>
                                                <p class="text-[9px] font-black text-zinc-500 uppercase tracking-widest mb-1">Health Score</p>
                                                <span class="text-xs font-bold text-emerald-400 select-none" id="preview_health_label">Optimal</span>
                                            </div>
                                            <div class="w-12 h-12 rounded-full border-4 border-white/5 flex items-center justify-center">
                                                <span class="text-[11px] font-black" id="preview_health_percent">98%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Form Profile & Personnel -->
                            <div class="col-span-12 lg:col-span-8 space-y-12">
                                <!-- Profile Section -->
                                <section>
                                    <div class="flex items-center gap-3 mb-8">
                                        <div class="w-10 h-10 rounded-xl bg-zinc-100 text-zinc-900 flex items-center justify-center shadow-sm">
                                            <span class="material-symbols-outlined text-xl">badge</span>
                                        </div>
                                        <h3 class="text-sm font-black text-zinc-900 uppercase tracking-[0.2em]">Official Profile</h3>
                                    </div>
                                    <div class="grid grid-cols-2 gap-8">
                                        <div class="col-span-2 md:col-span-1">
                                            <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-widest mb-3 ml-1">Branch Nomenclature</label>
                                            <div class="relative group">
                                                <input type="text" name="name" id="edit_branch_name" required class="w-full px-6 py-4.5 bg-zinc-50 border-0 focus:ring-4 focus:ring-primary/10 rounded-2xl text-sm font-bold text-zinc-800 placeholder:text-zinc-300 transition-all pl-12" placeholder="e.g. Stacey Cubao South">
                                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-zinc-300 group-focus-within:text-primary transition-colors text-xl">store</span>
                                            </div>
                                        </div>
                                        <div class="col-span-2 md:col-span-1">
                                            <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-widest mb-3 ml-1">Communication Protocol</label>
                                            <div class="relative group">
                                                <input type="text" name="phone" id="edit_branch_phone" required class="w-full px-6 py-4.5 bg-zinc-50 border-0 focus:ring-4 focus:ring-primary/10 rounded-2xl text-sm font-bold text-zinc-800 placeholder:text-zinc-300 transition-all pl-12" placeholder="+63 9XX XXX XXXX">
                                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-zinc-300 group-focus-within:text-primary transition-colors text-xl">call</span>
                                            </div>
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-widest mb-3 ml-1">Operational Coordinates (Address)</label>
                                            <div class="relative group">
                                                <textarea name="address" id="edit_branch_address" required rows="2" class="w-full px-6 py-4.5 bg-zinc-50 border-0 focus:ring-4 focus:ring-primary/10 rounded-2xl text-sm font-bold text-zinc-800 placeholder:text-zinc-300 transition-all pl-12 resize-none" placeholder="Enter complete physical location..."></textarea>
                                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-zinc-300 group-focus-within:text-primary transition-colors text-xl">location_on</span>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <!-- Leadership & Staff Section -->
                                <div class="grid grid-cols-2 gap-10">
                                    <section class="col-span-2 md:col-span-1">
                                        <div class="flex items-center gap-3 mb-8">
                                            <div class="w-10 h-10 rounded-xl bg-zinc-100 text-zinc-900 flex items-center justify-center shadow-sm">
                                                <span class="material-symbols-outlined text-xl">person_pin</span>
                                            </div>
                                            <h3 class="text-sm font-black text-zinc-900 uppercase tracking-[0.2em]">Leadership</h3>
                                        </div>
                                        <div class="bg-zinc-50 rounded-[2rem] p-6 border border-zinc-100/50">
                                            <label class="block text-[10px] font-black text-zinc-400 uppercase tracking-widest mb-4 ml-1">Assign Manager</label>
                                            <div class="relative group">
                                                <select name="manager_id" id="edit_branch_manager_id" class="w-full appearance-none px-12 py-4 bg-white border-2 border-zinc-100/30 focus:border-primary/20 rounded-2xl text-sm font-bold text-zinc-800 transition-all shadow-sm outline-none">
                                                    <option value="">Search Potential...</option>
                                                </select>
                                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-zinc-300 group-focus-within:text-primary transition-colors">
                                                    <span class="material-symbols-outlined">shield_person</span>
                                                </div>
                                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-zinc-300 pointer-events-none">
                                                    <span class="material-symbols-outlined">expand_more</span>
                                                </div>
                                            </div>
                                            <p class="mt-4 text-[10px] font-medium text-zinc-400 leading-relaxed px-2">Only unassigned staff with verified managerial credentials appear here.</p>
                                        </div>
                                    </section>

                                    <section class="col-span-2 md:col-span-1">
                                        <div class="flex items-center gap-3 mb-8">
                                            <div class="w-10 h-10 rounded-xl bg-zinc-100 text-zinc-900 flex items-center justify-center shadow-sm">
                                                <span class="material-symbols-outlined text-xl">group</span>
                                            </div>
                                            <h3 class="text-sm font-black text-zinc-900 uppercase tracking-[0.2em]">Operational Unit</h3>
                                        </div>
                                        <div id="edit_branch_staff_container" class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                            <!-- Staff roster loaded via AJAX -->
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 p-8 pt-0 flex items-center justify-end gap-3 bg-zinc-50/30">
                    <button type="button" data-bs-dismiss="modal" class="px-7 py-3 rounded-full font-black text-[10px] uppercase tracking-widest text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 transition-all border-0 bg-transparent">
                        Discard Changes
                    </button>
                    <button type="button" id="updateBranchBtn" class="bg-zinc-900 text-white px-9 py-3.5 rounded-full font-black text-[10px] uppercase tracking-widest shadow-xl shadow-zinc-200 hover:-translate-y-0.5 hover:shadow-primary/20 hover:bg-primary transition-all flex items-center gap-2 border-0">
                        <span id="btn_text">Apply Configuration</span>
                        <span class="material-symbols-outlined text-sm" id="btn_icon">rocket_launch</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        loadBranches();
        loadPotentialManagers();

        function loadBranches() {
            $.ajax({
                url: "<?= url('?route=ajax/branch_action&action=list') ?>",
                method: 'GET',
                success: function(response) {
                    if(response.success) {
                        renderBranches(response.data);
                        updateStats(response.stats);
                    }
                },
                error: function(xhr, status, error) {
                    $("#branchesContainer").html('<div class="col-span-full p-8 text-center text-red-400">Connection error: ' + error + ' (' + xhr.status + ')</div>');
                }
            });
        }

        function updateStats(stats) {
            if(!stats) return;
            $("#stat-total-branches").text(stats.total.toString().padStart(2, '0'));
            $("#stat-offline-branches").text(stats.offline.toString().padStart(2, '0'));
            $("#stat-top-performing").text(stats.top_performing);
            $("#stat-active-regions").text(stats.regions.toString().padStart(2, '0'));
        }

        function loadPotentialManagers() {
            $.getJSON("<?= url('?route=ajax/branch_action&action=get_managers') ?>", function(response) {
                if (response.success) {
                    let html = '<option value="">Unassigned</option>';
                    response.data.forEach(m => {
                        html += `<option value="${m.id}">${m.name}</option>`;
                    });
                    $("#edit_branch_manager_id").html(html);
                }
            });
        }

        function renderBranches(branches) {
            let html = '';
            if (!branches || branches.length === 0) {
                html = '<div class="col-span-full py-20 text-center text-gray-400 bg-white rounded-3xl border-2 border-dashed border-gray-100 m-4">No branches found in database.</div>';
            } else {
                branches.forEach(branch => {
                    const statusColor = branch.status === 'Active' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-red-50 text-red-600 border-red-100';
                    const pulseColor = branch.status === 'Active' ? 'bg-green-500 pulse-green' : 'bg-red-500';
                    const manager = branch.manager_name || 'Unassigned';
                    
                    // Dynamic Health Intelligence
                    let healthText = "Optimal";
                    let healthColor = "text-emerald-500";
                    let healthPercent = "98%";
                    if (branch.status === 'Maintenance') {
                        healthText = "Degraded"; healthColor = "text-amber-500"; healthPercent = "65%";
                    } else if (branch.status === 'Offline') {
                        healthText = "Critical"; healthColor = "text-red-500"; healthPercent = "0%";
                    }

                    html += `
                    <div class="glass-card bg-white rounded-3xl card-shadow branch-card group">
                        <div class="relative h-44 overflow-hidden">
                            <img src="${branch.full_image_url}" class="w-full h-full object-cover branch-image-zoom" alt="${branch.name}">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                            <div class="absolute top-4 left-4 flex gap-2">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${statusColor} bg-white/90 backdrop-blur-sm shadow-sm flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full ${pulseColor}"></div>
                                    ${branch.status}
                                </span>
                            </div>
                            <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button onclick="deleteBranch(${branch.id}, '${branch.name}')" class="w-8 h-8 rounded-full bg-white/90 backdrop-blur-sm text-red-500 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center border-0 shadow-sm">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                            <div class="absolute bottom-4 left-6">
                                <span class="text-[9px] text-white/70 font-black uppercase tracking-widest">STATION CODE: #B${branch.id.toString().padStart(3, '0')}</span>
                                <h5 class="font-bold text-xl text-white mb-0">${branch.name}</h5>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4 mb-6">
                                <div class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 border border-gray-100">
                                    <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-[10px] font-bold">${manager.charAt(0)}</div>
                                    <div>
                                        <p class="text-[9px] text-gray-400 font-black uppercase tracking-widest mb-1 leading-none">Commanding Manager</p>
                                        <h6 class="text-xs font-bold text-gray-800 m-0">${manager}</h6>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-start gap-2 text-gray-500">
                                        <span class="material-symbols-outlined text-sm mt-0.5">location_on</span>
                                        <p class="text-xs font-medium leading-relaxed">${branch.address}</p>
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-400">
                                        <span class="material-symbols-outlined text-sm">call</span>
                                        <p class="text-[11px] font-medium tracking-tight">${branch.phone || 'N/A'}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4 pt-4 border-t border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full border-2 border-zinc-50 flex items-center justify-center text-[10px] font-black ${healthColor} bg-zinc-50/50">
                                            ${healthPercent}
                                        </div>
                                        <div>
                                            <p class="text-[8px] font-black uppercase tracking-widest text-zinc-300 mb-0">System Health</p>
                                            <p class="text-[10px] font-bold ${healthColor} uppercase tracking-tighter m-0">${healthText}</p>
                                        </div>
                                    </div>
                                    <div class="flex -space-x-2">
                                        ${(function() {
                                            if(!branch.staff_list) return '';
                                            const staff = branch.staff_list.split(',');
                                            return staff.slice(0, 3).map(s => {
                                                const [name, role] = s.split(':');
                                                const initials = name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                                                const colorClass = role === 'Cashier' ? 'bg-blue-100 text-blue-500' : 'bg-primary/20 text-primary';
                                                return `<div class="w-7 h-7 rounded-full ${colorClass} border-2 border-white flex items-center justify-center text-[8px] font-black" title="${name} (${role})">${initials}</div>`;
                                            }).join('') + (staff.length > 3 ? `<div class="w-7 h-7 rounded-full bg-zinc-50 border-2 border-white flex items-center justify-center text-[8px] font-black text-gray-400">+${staff.length-3}</div>` : '');
                                        })()}
                                    </div>
                                </div>
                            </div>
                            <div class="pt-4 mt-4 border-t border-zinc-100 flex justify-between items-center">
                                <p class="text-[9px] font-black text-zinc-300 uppercase tracking-widest flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px]">schedule</span> 
                                    Last Sync: ${branch.updated_at ? 'Active' : 'Now'}
                                </p>
                                <button onclick="editBranch(${branch.id})" class="bg-zinc-900 text-white hover:bg-primary px-5 py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-widest flex items-center gap-2 transition-all border-0 shadow-lg shadow-zinc-200">
                                    <span class="material-symbols-outlined text-sm">settings</span>
                                    Configure
                                </button>
                            </div>
                        </div>
                    </div>`;
                });
            }
            $("#branchesContainer").html(html);
            $("#branchCountFooter").text(`Displaying ${branches ? branches.length : 0} Branch Records`);
        }

        const branchModalInstance = new bootstrap.Modal(document.getElementById('editBranchModal'));

        window.openCreateModal = function() {
            $("#editBranchForm")[0].reset();
            $("#edit_branch_id").val("");
            $("#modal_title").text("Establish Branch");
            $("#modal_subtitle").text("Initialize a new strategic location for your growing empire.");
            $("#modal_icon").text("add_location_alt");
            $("#modal_icon_container").removeClass('bg-zinc-900').addClass('bg-primary');
            $("#btn_text").text("Initialize Branch");
            $("#btn_icon").text("rocket_launch");
            $("#edit_image_preview").html('<div class="text-center group-hover:scale-110 transition-transform duration-500"><span class="material-symbols-outlined text-5xl text-zinc-300 mb-4">add_a_photo</span><p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Upload Facade</p></div>');
            $("#preview_name_text").text("New Frontier ID");
            $("#preview_health_label").text("Optimal").removeClass('text-amber-400 text-red-400').addClass('text-emerald-400');
            $("#preview_health_percent").text("100%");
            
            // Show modal
            branchModalInstance.show();

            // Load potential staff
            $.get("<?= url('?route=ajax/branch_action&action=get_staff') ?>", function(staffRes) {
                if(staffRes.success) {
                    renderStaffSelection(staffRes.potential, []);
                }
            });
        };

        window.editBranch = function(id) {
            $("#editBranchForm")[0].reset();
            $("#modal_title").text("Configure Location");
            $("#modal_subtitle").text("Refine the identity and adjust the roster of this establishment.");
            $("#modal_icon").text("storefront");
            $("#modal_icon_container").removeClass('bg-primary').addClass('bg-zinc-900');
            $("#btn_text").text("Apply Settings");
            $("#btn_icon").text("verified");
            
            $("#edit_branch_staff_container").html('<div class="text-center py-12"><div class="spinner-border text-primary opacity-20"></div></div>');
            $("#edit_image_preview").html('<span class="material-symbols-outlined text-4xl text-zinc-300">add_a_photo</span>');
            
            // Show modal
            branchModalInstance.show();

            // Load staff roster
            $.get("<?= url('?route=ajax/branch_action&action=get_staff') ?>&branch_id=" + id, function(staffRes) {
                if(staffRes.success) {
                    const assignedIds = staffRes.assigned.map(u => u.id);
                    renderStaffSelection(staffRes.potential, assignedIds);
                }
            });

            // Load branch data
            $.getJSON("<?= url('?route=ajax/branch_action&action=get&id=') ?>" + id, function(response) {
                if (response.success) {
                    const b = response.data;
                    $("#edit_branch_id").val(b.id);
                    $("#edit_branch_name").val(b.name);
                    $("#edit_branch_address").val(b.address);
                    $("#edit_branch_phone").val(b.phone);
                    $("#edit_branch_status").val(b.status);
                    $("#edit_branch_manager_id").val(b.manager_id || "");
                    $("#preview_name_text").text(b.name);
                    
                    // Dynamic Health Logic
                    let healthText = "Optimal";
                    let healthColor = "text-emerald-400";
                    let healthPercent = "98%";
                    
                    if (b.status === 'Maintenance') {
                        healthText = "Degraded";
                        healthColor = "text-amber-400";
                        healthPercent = "65%";
                    } else if (b.status === 'Offline') {
                        healthText = "Critical";
                        healthColor = "text-red-400";
                        healthPercent = "0%";
                    }
                    
                    $("#preview_health_label").text(healthText).removeClass('text-emerald-400 text-amber-400 text-red-400').addClass(healthColor);
                    $("#preview_health_percent").text(healthPercent);
                    
                    if(b.full_image_url) {
                        $("#edit_image_preview").html(`<img src="${b.full_image_url}" class="w-full h-full object-cover">`);
                    }
                }
            });
        };

        function renderStaffSelection(potential, assignedIds) {
            const currentBranchId = $("#edit_branch_id").val();
            let staffHtml = '';
            if(!potential || potential.length === 0) {
                staffHtml = '<p class="text-center text-[10px] text-zinc-400 py-10 font-medium">No personnel accounts found.</p>';
            } else {
                potential.forEach(user => {
                    const isAssignedToThis = assignedIds.includes(user.id);
                    const isAssignedElsewhere = user.branch_id && user.branch_id != currentBranchId;
                    const role = user.role_name || 'Staff';
                    const branchAssignment = user.current_branch_name ? `<span class="bg-primary/10 text-primary px-1.5 py-0.5 rounded-md text-[8px] font-black uppercase tracking-wider">Stationed @ ${user.current_branch_name}</span>` : '';
                    
                    staffHtml += `
                        <label class="flex items-center justify-between p-4 rounded-[20px] bg-white border border-zinc-100 ${isAssignedElsewhere ? 'opacity-50 grayscale' : 'hover:border-primary/20 hover:shadow-xl hover:shadow-zinc-100'} transition-all cursor-pointer group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-zinc-50 border border-zinc-100 flex items-center justify-center text-[10px] font-black text-zinc-400 group-hover:text-primary transition-all">
                                    ${user.name.charAt(0)}
                                </div>
                                <div>
                                    <h6 class="text-xs font-bold m-0 text-zinc-800 tracking-tight">${user.name}</h6>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[9px] font-black uppercase tracking-widest text-zinc-400 leading-none">${role}</span>
                                        ${branchAssignment}
                                    </div>
                                </div>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input w-9 h-4.5 cursor-pointer shadow-none focus:ring-0" type="checkbox" name="staff_ids[]" value="${user.id}" 
                                    ${isAssignedToThis ? 'checked ' : ''} 
                                    ${isAssignedElsewhere ? 'disabled ' : ''}>
                            </div>
                        </label>`;
                });
            }
            $("#edit_branch_staff_container").html(staffHtml);
        }

        window.previewImage = function(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $(`#${previewId}`).html(`<img src="${e.target.result}" class="w-full h-full object-cover opacity-0 transition-opacity duration-500" onload="this.classList.remove('opacity-0')">`);
                }
                reader.readAsDataURL(input.files[0]);
            }
        };

        window.closeModal = function(id) {
            branchModalInstance.hide();
        };

        $("#edit_branch_name").on("input", function() {
            $("#preview_name_text").text($(this).val() || "Awaiting Config...");
        });

        $("#updateBranchBtn").on("click", function() {
            $("#editBranchForm").submit();
        });

        $("#editBranchForm").on("submit", function(e) {
            e.preventDefault();
            const btn = $("#updateBranchBtn");
            const originalHtml = btn.html();
            
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> SYNCING...');
            
            const formData = new FormData(this);
            const isEdit = $("#edit_branch_id").val() !== "";
            const actionUrl = isEdit ? "<?= url('?route=ajax/branch_action&action=update') ?>" : "<?= url('?route=ajax/branch_action&action=create') ?>";
            
            $.ajax({
                url: actionUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        closeModal('editBranchModal');
                        loadBranches();
                    } else {
                        showToast(response.message, 'error');
                    }
                },
                error: function() {
                    showToast('Failed to communicate with headquarters via secure link.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        window.deleteBranch = function(id, name) {
            Swal.fire({
                title: 'Decommission Branch?',
                text: `You are about to remove ${name} from the active registry. This action is recorded in the executive logs.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4d6d',
                cancelButtonColor: '#191c1d',
                confirmButtonText: 'Yes, Decommission',
                background: '#ffffff',
                customClass: {
                    popup: 'rounded-[32px] border-0 card-shadow',
                    title: 'font-headline font-bold text-2xl',
                    confirmButton: 'rounded-full px-8 py-3 font-bold text-xs uppercase tracking-widest',
                    cancelButton: 'rounded-full px-8 py-3 font-bold text-xs uppercase tracking-widest'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.getJSON("<?= url('?route=ajax/branch_action&action=delete&id=') ?>" + id, function(response) {
                        if (response.success) {
                            showToast(response.message, 'success');
                            loadBranches();
                        } else {
                            showToast(response.message, 'error');
                        }
                    });
                }
            });
        };

        $("#branchSearch").on("input", function() {
            const query = $(this).val().toLowerCase();
            let visibleCount = 0;
            $(".branch-card").each(function() {
                const text = $(this).text().toLowerCase();
                const visible = text.includes(query);
                $(this).toggle(visible);
                if (visible) visibleCount++;
            });
            $("#branchCountFooter").text(`Displaying ${visibleCount} Matching Records`);
        });
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>