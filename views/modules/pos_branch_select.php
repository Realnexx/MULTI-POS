<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-xxl-6">
            <!-- Header Section -->
            <div class="text-center mb-5">
                <div class="d-inline-block p-4 rounded-circle bg-primary-subtle text-primary mb-4 shadow-sm">
                    <span class="material-symbols-outlined display-3">hub</span>
                </div>
                <h1 class="display-4 fw-black text-dark mb-2">POS Station Activation</h1>
                <p class="lead text-muted">Select a branch to initialize the point-of-sale terminal for this session.</p>
            </div>

            <!-- Branch Grid -->
            <div class="row g-4">
                <?php if (empty($branches)): ?>
                    <div class="col-12">
                        <div class="alert alert-warning border-0 rounded-4 p-5 text-center shadow-sm">
                            <span class="material-symbols-outlined display-4 mb-3 d-block">warning</span>
                            <h4 class="fw-bold">No Active Branches Found</h4>
                            <p class="mb-0 text-muted">Please activate at least one branch in the system settings before using the POS terminal.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($branches as $branch): ?>
                        <div class="col-md-6 col-lg-4">
                            <a href="?route=pos&select_branch=<?= $branch['id'] ?>" class="text-decoration-none h-100 d-block">
                                <div class="card h-100 border-0 rounded-4 shadow-sm hov-lift transition-all">
                                    <div class="card-body p-4 text-center">
                                        <div class="p-3 rounded-4 bg-light mb-3 d-inline-block">
                                            <span class="material-symbols-outlined text-primary fs-1">storefront</span>
                                        </div>
                                        <h4 class="fw-bold text-dark mb-2"><?= htmlspecialchars($branch['name']) ?></h4>
                                        <div class="badge bg-success-subtle text-success px-3 py-2 rounded-pill small fw-bold mb-3">
                                            Active Station
                                        </div>
                                        <p class="small text-muted mb-0">Initialize terminal for this location.</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Footer Meta -->
            <div class="mt-5 text-center pt-5 border-top border-light opacity-50">
                <p class="small text-uppercase tracking-widest fw-bold mb-0">Authorized Personnel Only • Secure Session</p>
            </div>
        </div>
    </div>
</div>

<style>
    .fw-black { font-weight: 900; }
    .tracking-widest { letter-spacing: 0.2em; }
    .hov-lift:hover {
        transform: translateY(-8px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.1) !important;
        background: #f8faff;
    }
    .transition-all {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>
