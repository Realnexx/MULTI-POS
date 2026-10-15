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
    <title>Pricing Plans - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
    <style>
        .pricing-card {
            border: none;
            transition: all 0.3s ease;
            overflow: hidden;
            border-radius: 15px;
        }
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,.155)!important;
        }
        .pricing-card.popular {
            border: 2px solid #5d87ff;
            position: relative;
        }
        .popular-badge {
            position: absolute;
            top: 20px;
            right: -35px;
            background: #5d87ff;
            color: white;
            padding: 5px 40px;
            transform: rotate(45deg);
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .price-value {
            font-size: 3rem;
            font-weight: 700;
            color: #2a3547;
        }
        .price-symbol {
            font-size: 1.5rem;
            vertical-align: super;
        }
        .feature-list li {
            padding: 10px 0;
            border-bottom: 1px dashed #e5eaef;
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .comparison-icon {
            font-size: 1.25rem;
        }
    </style>
</head>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <!-- Sidebar Start -->
        <?php include 'components/sidebar.php'; ?>
        <!--  Sidebar End -->
        <!--  Main wrapper -->
        <div class="body-wrapper">

            <div class="body-wrapper-inner">
                <div class="container-fluid">
                    <!--  Header Start -->
                    <?php include 'components/header.php'; ?>
                    <!--  Header End -->

                    <div class="top-part card bg-light-info shadow-none position-relative overflow-hidden mb-4">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-semibold mb-8">Pricing & Plans</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Pricing Plans</li>
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

                    <div class="row mb-5">
                        <!-- Starter Plan -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card pricing-card h-100 shadow-sm">
                                <div class="card-body text-center p-5">
                                    <h5 class="fw-semibold text-uppercase mb-4">Starter</h5>
                                    <div class="mb-4">
                                        <span class="price-symbol">₱</span>
                                        <span class="price-value">10,000</span>
                                        <span class="text-muted">/event</span>
                                    </div>
                                    <p class="text-muted mb-4">Complete package for standard local competitions.</p>
                                    <ul class="list-unstyled feature-list text-start mb-5">
                                        <li><i class="ti ti-check text-primary me-2"></i> Up to 25 Candidates</li>
                                        <li><i class="ti ti-check text-primary me-2"></i> 5 Judges Max</li>
                                        <li><i class="ti ti-check text-primary me-2"></i> Real-time Results Dashboard</li>
                                        <li><i class="ti ti-check text-primary me-2"></i> Standard Email Support</li>
                                        <li><i class="ti ti-check text-primary me-2"></i> Basic Result Reports</li>
                                        <li class="text-muted"><i class="ti ti-x text-danger me-2"></i> QR Code Public Voting</li>
                                    </ul>
                                    <button class="btn btn-outline-primary w-100 py-2 rounded-pill select-plan" data-plan="Starter" data-bs-toggle="modal" data-bs-target="#activatePlanModal">Choose Starter</button>
                                </div>
                            </div>
                        </div>

                        <!-- Pro Plan -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card pricing-card popular h-100 shadow-sm">
                                <div class="popular-badge">Most Popular</div>
                                <div class="card-body text-center p-5">
                                    <h5 class="fw-semibold text-uppercase text-primary mb-4">Pro</h5>
                                    <div class="mb-4">
                                        <span class="price-symbol">₱</span>
                                        <span class="price-value">25,000</span>
                                        <span class="text-muted">/event</span>
                                    </div>
                                    <p class="text-muted mb-4">Professional features for major regional pageants.</p>
                                    <ul class="list-unstyled feature-list text-start mb-5">
                                        <li><i class="ti ti-check text-primary me-2"></i> Unlimited Candidates</li>
                                        <li><i class="ti ti-check text-primary me-2"></i> 15 Judges Max</li>
                                        <li><i class="ti ti-check text-primary me-2"></i> Advanced Analytics & Insights</li>
                                        <li><i class="ti ti-check text-primary me-2"></i> QR Code & Web SMS Voting</li>
                                        <li><i class="ti ti-check text-primary me-2"></i> Live TV Display Export (Full HD)</li>
                                        <li><i class="ti ti-check text-primary me-2"></i> 24/7 Priority Support</li>
                                    </ul>
                                    <button class="btn btn-primary w-100 py-2 rounded-pill select-plan" data-plan="Pro" data-bs-toggle="modal" data-bs-target="#activatePlanModal">Choose Pro</button>
                                </div>
                            </div>
                        </div>

                        <!-- Enterprise Plan -->
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card pricing-card h-100 shadow-sm">
                                <div class="card-body text-center p-5">
                                    <h5 class="fw-semibold text-uppercase mb-4">Enterprise</h5>
                                    <div class="mb-4">
                                        <span class="price-symbol">₱</span>
                                        <span class="price-value">15,000</span>
                                        <span class="text-muted">/month</span>
                                    </div>
                                    <p class="text-muted mb-4">For agencies and venues with multiple events.</p>
                                    <ul class="list-unstyled feature-list text-start mb-5">
                                        <li><i class="ti ti-check text-primary me-2"></i> Unlimited Everything</li>
                                        <li><i class="ti ti-check text-primary me-2"></i> White-label Branding</li>
                                        <li><i class="ti ti-check text-primary me-2"></i> Priority 24/7 Support</li>
                                        <li><i class="ti ti-check text-primary me-2"></i> API Access & Webhooks</li>
                                        <li><i class="ti ti-check text-primary me-2"></i> On-site Technical Help</li>
                                        <li><i class="ti ti-check text-primary me-2"></i> Custom Formula Builder</li>
                                    </ul>
                                    <button class="btn btn-dark w-100 py-2 rounded-pill select-plan" data-plan="Enterprise" data-bs-toggle="modal" data-bs-target="#activatePlanModal">Contact Sales</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title fw-semibold mb-4 text-center">Plan Comparison Detail</h5>
                            <div class="table-responsive">
                                <table class="table align-middle table-bordered mb-0 table-responsive-stack">
                                    <thead class="bg-light">
                                        <tr class="text-center">
                                            <th class="text-start">Feature</th>
                                            <th width="150">Starter</th>
                                            <th width="150" class="text-primary bg-light-primary border-primary">Pro</th>
                                            <th width="150">Enterprise</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold" data-label="Feature">Max Candidates</td>
                                            <td class="text-center" data-label="Starter">25</td>
                                            <td class="text-center text-primary bg-light-primary" data-label="Pro">Unlimited</td>
                                            <td class="text-center" data-label="Enterprise">Unlimited</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold" data-label="Feature">Max Judges</td>
                                            <td class="text-center" data-label="Starter">5</td>
                                            <td class="text-center text-primary bg-light-primary" data-label="Pro">15</td>
                                            <td class="text-center" data-label="Enterprise">Unlimited</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold" data-label="Feature">Live TV Results</td>
                                            <td class="text-center text-danger" data-label="Starter">Basic</td>
                                            <td class="text-center text-primary bg-light-primary" data-label="Pro"><i class="ti ti-check fs-5"></i></td>
                                            <td class="text-center" data-label="Enterprise"><i class="ti ti-check fs-5"></i></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold" data-label="Feature">Advanced Statistics</td>
                                            <td class="text-center text-danger" data-label="Starter"><i class="ti ti-x fs-5"></i></td>
                                            <td class="text-center text-primary bg-light-primary" data-label="Pro"><i class="ti ti-check fs-5"></i></td>
                                            <td class="text-center" data-label="Enterprise"><i class="ti ti-check fs-5"></i></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold" data-label="Feature">White-label Branding</td>
                                            <td class="text-center text-danger" data-label="Starter"><i class="ti ti-x fs-5"></i></td>
                                            <td class="text-center text-primary bg-light-primary text-danger" data-label="Pro"><i class="ti ti-x fs-5"></i></td>
                                            <td class="text-center" data-label="Enterprise"><i class="ti ti-check fs-5"></i></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Add-ons Section -->
                    <div class="row mt-5">
                        <div class="col-12 text-center mb-4">
                            <h4 class="fw-semibold">Power Up Your Event with Add-ons</h4>
                            <p class="text-muted">Need a little extra? Scale your plan with these modules.</p>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card bg-light-primary border-0 shadow-none">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="ti ti-users fs-7 text-primary me-2"></i>
                                        <h5 class="card-title fw-semibold mb-0">+5 Candidates</h5>
                                    </div>
                                    <p class="small mb-3">Increase your candidate pool for any base plan.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold fs-5">₱250</span>
                                        <button class="btn btn-sm btn-primary">Add</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card bg-light-info border-0 shadow-none">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="ti ti-shield-search fs-7 text-info me-2"></i>
                                        <h5 class="card-title fw-semibold mb-0">Audit Expert</h5>
                                    </div>
                                    <p class="small mb-3">Deep-dive logs and per-judge deviation analysis.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold fs-5">₱500</span>
                                        <button class="btn btn-sm btn-info text-white">Add</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card bg-light-success border-0 shadow-none">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="ti ti-cloud-cog fs-7 text-success me-2"></i>
                                        <h5 class="card-title fw-semibold mb-0">API Access</h5>
                                    </div>
                                    <p class="small mb-3">Integrate results directly with your own website.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold fs-5">₱1,200</span>
                                        <button class="btn btn-sm btn-success">Add</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Section -->
                    <div class="row mt-4 mb-5">
                        <div class="col-lg-8 mx-auto">
                            <h5 class="card-title fw-semibold mb-4 text-center">Frequently Asked Questions</h5>
                            <div class="accordion" id="pricingFaq">
                                <div class="accordion-item shadow-none border-bottom">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                            How do I upgrade an existing competition?
                                        </button>
                                    </h2>
                                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#pricingFaq">
                                        <div class="accordion-body text-muted">
                                            Select a plan above, and in the popup, choose the specific competition you wish to upgrade. Once payment is verified (for Pro), features unlock instantly.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item shadow-none border-bottom">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                            Can I change plans mid-event?
                                        </button>
                                    </h2>
                                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#pricingFaq">
                                        <div class="accordion-body text-muted">
                                            Yes! You can upgrade from Starter to Pro at any time. The system will automatically accommodate the expanded candidate and judge limits.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item shadow-none border-0">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                            What happens after the event ends?
                                        </button>
                                    </h2>
                                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#pricingFaq">
                                        <div class="accordion-body text-muted">
                                            You will still have 30 days to export all reports and analytics. After that, the event data moves to your archive.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activate Plan Modal -->
                    <div class="modal fade" id="activatePlanModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content border-0">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title text-white">Activate <span id="selectedPlanName"></span> Plan</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-light-info border-info d-flex align-items-center" role="alert">
                                        <i class="ti ti-info-circle fs-6 me-2 text-info"></i>
                                        <div>Select the competition you want to bind to this license.</div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Select Competition</label>
                                        <select class="form-select">
                                            <option value="">-- Choose Competition --</option>
                                            <option value="1">Miss Universe 2026 (Active)</option>
                                            <option value="2">Mr. World Aurora (Draft)</option>
                                            <option value="3">Local Talent Night (Active)</option>
                                        </select>
                                    </div>
                                    <div class="pricing-summary p-3 bg-light rounded">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Tier Cost:</span>
                                            <span class="fw-bold" id="planPrice">₱0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Processing Fee:</span>
                                            <span class="text-success">FREE</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold text-dark">Total Due:</span>
                                            <span class="fw-bolder text-primary fs-5" id="totalDue">₱0.00</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary px-4">Confirm Activation</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Plan Management Interface -->
                    <div class="card mt-5">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="card-title fw-semibold mb-1">Pricing Plan Management</h5>
                                    <p class="text-muted small mb-0">Manage the tiers available to organizers during competition creation.</p>
                                </div>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                                    <i class="ti ti-plus me-1"></i> Add New Plan
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle table-hover table-responsive-stack" id="adminPlansTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Plan Name</th>
                                            <th>Base Price</th>
                                            <th>Candidate Limit</th>
                                            <th>Judge Limit</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td data-label="Plan Name"><span class="fw-bold">Starter</span></td>
                                            <td data-label="Base Price">₱10,000</td>
                                            <td data-label="Candidate Limit">25</td>
                                            <td data-label="Judge Limit">5</td>
                                            <td data-label="Status"><span class="badge bg-light-success text-success">Active</span></td>
                                            <td data-label="Actions" class="text-end">
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button class="btn btn-sm btn-outline-info" title="Edit Plan"><i class="ti ti-edit"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" title="Delete Plan"><i class="ti ti-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-label="Plan Name"><span class="fw-bold">Pro</span></td>
                                            <td data-label="Base Price">₱25,000</td>
                                            <td data-label="Candidate Limit">Unlimited</td>
                                            <td data-label="Judge Limit">15</td>
                                            <td data-label="Status"><span class="badge bg-light-success text-success">Active</span></td>
                                            <td data-label="Actions" class="text-end">
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button class="btn btn-sm btn-outline-info" title="Edit Plan"><i class="ti ti-edit"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" title="Delete Plan"><i class="ti ti-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Inclusions Management Interface -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="card-title fw-semibold mb-1">Feature & Inclusion Matrix</h5>
                                    <p class="text-muted small mb-0">Define the features that appear in the comparison detail table.</p>
                                </div>
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addInclusionModal">
                                    <i class="ti ti-list-check me-1"></i> Add Inclusion
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle table-hover table-responsive-stack" id="adminInclusionsTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Feature Name</th>
                                            <th>Internal Key</th>
                                            <th>Show in Comparison</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td data-label="Feature Name">Live TV Results</td>
                                            <td data-label="Internal Key"><code>live_tv_export</code></td>
                                            <td data-label="Show in Comparison"><span class="badge badge-dot bg-success me-1"></span> Yes</td>
                                            <td data-label="Actions" class="text-end">
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button class="btn btn-sm btn-outline-info" title="Edit Feature"><i class="ti ti-edit"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" title="Delete Feature"><i class="ti ti-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-label="Feature Name">White-label Branding</td>
                                            <td data-label="Internal Key"><code>branding_removal</code></td>
                                            <td data-label="Show in Comparison"><span class="badge badge-dot bg-success me-1"></span> Yes</td>
                                            <td data-label="Actions" class="text-end">
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <button class="btn btn-sm btn-outline-info" title="Edit Feature"><i class="ti ti-edit"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" title="Delete Feature"><i class="ti ti-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Add/Edit Plan Modal -->
                    <div class="modal fade" id="addPlanModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Manage Pricing Plan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="planForm">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">Plan Name</label>
                                                <input type="text" class="form-control" placeholder="e.g. Platinum">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">Base Price (₱)</label>
                                                <input type="number" class="form-control" placeholder="50000">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-semibold">Candidate Limit</label>
                                                <input type="text" class="form-control" placeholder="e.g. 50 or Unlimited">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-semibold">Judge Limit</label>
                                                <input type="text" class="form-control" placeholder="e.g. 10 or Unlimited">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-semibold">Status</label>
                                                <select class="form-select">
                                                    <option value="active">Active</option>
                                                    <option value="inactive">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Brief Description</label>
                                            <textarea class="form-control" rows="2"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold d-block">Enable Features (Inclusions)</label>
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <div class="form-check border rounded p-2">
                                                        <input class="form-check-input ms-0" type="checkbox" id="feat1">
                                                        <label class="form-check-label ms-1" for="feat1">Live TV Export</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check border rounded p-2">
                                                        <input class="form-check-input ms-0" type="checkbox" id="feat2">
                                                        <label class="form-check-label ms-1" for="feat2">QR Code Voting</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check border rounded p-2">
                                                        <input class="form-check-input ms-0" type="checkbox" id="feat3">
                                                        <label class="form-check-label ms-1" for="feat3">White-label</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" form="planForm" class="btn btn-primary">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add/Edit Inclusion Modal -->
                    <div class="modal fade" id="addInclusionModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Manage Feature Inclusion</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="inclusionForm">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Feature Name</label>
                                            <input type="text" class="form-control" placeholder="e.g. Dedicated Support Agent">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Internal Identity (API Key)</label>
                                            <input type="text" class="form-control" placeholder="e.g. dedicated_agent">
                                            <div class="form-text">System-level identifier, no spaces.</div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="showComp" checked>
                                                <label class="form-check-label fw-semibold" for="showComp">Display in Comparison Table</label>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" form="inclusionForm" class="btn btn-primary">Save Feature</button>
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
            // Initialize Management Tables
            $('#adminPlansTable').DataTable({
                "pageLength": 5,
                "ordering": true,
                "info": false,
                "language": {
                    "search": "Filter Plans:",
                }
            });

            $('#adminInclusionsTable').DataTable({
                "pageLength": 10,
                "ordering": true,
                "info": false,
                "language": {
                    "search": "Filter Features:",
                }
            });

            $('.select-plan').on('click', function() {
                var plan = $(this).data('plan');
                var price = "";
                var total = "";

                if(plan == "Starter") {
                    price = "₱10,000.00";
                    total = "₱10,000.00";
                } else if(plan == "Pro") {
                    price = "₱25,000.00";
                    total = "₱25,000.00";
                } else {
                    price = "Custom Quote";
                    total = "TBD";
                }

                $('#selectedPlanName').text(plan);
                $('#planPrice').text(price);
                $('#totalDue').text(total);
            });
        });
    </script>
</body>

</html>
