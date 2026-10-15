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
    <title>Audience Voting - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
    <style>
        .hero-section { background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%); color: white; border-radius: 30px; padding: 60px 40px; margin-bottom: 40px; position: relative; overflow: hidden; }
        .hero-section::after { content: ''; position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; }
        .vote-card { border: none; border-radius: 24px; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); background: #fff; overflow: hidden; height: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.05); max-width: 340px; margin: 0 auto; }
        .vote-card:hover { transform: scale(1.03); box-shadow: 0 20px 50px rgba(0,0,0,0.1) !important; }
        .candidate-thumb { height: 240px; width: 100%; object-fit: contain; background: #f8fafc; }
        .vote-card:hover .candidate-thumb { transform: none; }
        .vote-btn { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; border-radius: 12px; padding: 12px; font-weight: 700; width: 100%; transition: all 0.3s; }
        .vote-btn:hover { background: linear-gradient(135deg, #059669 0%, #10b981 100%); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2); }
        .vote-count-badge { position: absolute; top: 20px; right: 20px; background: white; padding: 6px 15px; border-radius: 50px; font-weight: 800; color: #6366f1; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-size: 0.8rem; }
        .category-pill { background: #f1f5f9; color: #64748b; padding: 5px 15px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .search-voting { border-radius: 20px; padding: 15px 25px; border: 2px solid #f1f5f9; font-size: 1rem; width: 100%; transition: all 0.3s; }
        .search-voting:focus { border-color: #6366f1; outline: none; }
        .wallet-card { background: white; border-radius: 20px; transition: all 0.3s; border: 1px solid #f1f5f9; }
        .wallet-card:hover { border-color: #6366f1; transform: translateY(-5px); }
        .wallet-card.clickable { cursor: pointer; position: relative; }
        .wallet-card.clickable:hover { box-shadow: 0 10px 25px rgba(99, 102, 241, 0.15); border-color: #6366f1; }
        .wallet-card.clickable::after { content: '\ea5f'; font-family: 'tabler-icons'; position: absolute; top: 15px; right: 15px; font-size: 1.2rem; color: #6366f1; opacity: 0; transition: all 0.3s; }
        .wallet-card.clickable:hover::after { opacity: 1; transform: translateX(3px); }
        .vote-btn-outline { border: 2px solid #10b981; color: #10b981; background: transparent; border-radius: 12px; padding: 10px; font-weight: 700; width: 100%; transition: all 0.3s; }
        .vote-btn-outline:hover { background: #10b981; color: white; }
        .package-card { cursor: pointer; border: 2px solid #f1f5f9; border-radius: 20px; transition: all 0.3s; }
        .package-card:hover { border-color: #6366f1; background: #f8faff; }
        .package-card.selected { border-color: #6366f1; background: #eef2ff; }
        .guideline-item { display: flex; gap: 15px; margin-bottom: 20px; }
        .guideline-icon { width: 40px; height: 40px; border-radius: 10px; background: #eef2ff; color: #6366f1; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

        /* Custom Loader */
        .aurora-loader {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            position: relative;
            animation: rotate 1s linear infinite;
            margin: 0 auto 20px;
        }
        .aurora-loader::before {
            content: "";
            box-sizing: border-box;
            position: absolute;
            inset: 0px;
            border-radius: 50%;
            border: 5px solid #6366f1;
            animation: prixClipFix 2s linear infinite ;
        }
        @keyframes rotate { 100% {transform: rotate(360deg)} }
        @keyframes prixClipFix {
            0%   {clip-path:polygon(50% 50%,0 0,0 0,0 0,0 0,0 0)}
            25%  {clip-path:polygon(50% 50%,0 0,100% 0,100% 0,100% 0,100% 0)}
            50%  {clip-path:polygon(50% 50%,0 0,100% 0,100% 100%,100% 100%,100% 100%)}
            75%  {clip-path:polygon(50% 50%,0 0,100% 0,100% 100%,0 100%,0 100%)}
            100% {clip-path:polygon(50% 50%,0 0,100% 0,100% 100%,0 100%,0 0)}
        }

        /* Empty State */
        .empty-state-container {
            padding: 80px 40px;
            background: #f8fafc;
            border-radius: 30px;
            border: 2px dashed #e2e8f0;
            text-align: center;
            transition: all 0.3s;
            margin-top: 20px;
        }
        .empty-state-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 20px;
            display: block;
        }

        /* Enhanced Select */
        .premium-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1.5rem center;
            background-size: 1.2em;
            padding-right: 3.5rem !important;
            border: 2px solid #f1f5f9;
            border-radius: 20px;
            transition: all 0.3s;
            cursor: pointer;
            font-weight: 600;
            color: #1e293b;
        }
        .premium-select:hover { border-color: #6366f1; background-color: #f8faff; }
        .premium-select:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
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
                                    <h4 class="fw-semibold mb-8">Audience Voting Portal</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Public Voting</li>
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

                    <div class="hero-section text-center p-5">
                       <h1 class="display-4 fw-bolder mb-3 text-white text-uppercase">People's Choice Awards</h1>
                       <p class="fs-5 opacity-75 mb-4 mx-auto" style="max-width: 700px;">Vote for your favorite candidate! Each vote counts towards the final popularity results. Unlimited voting is enabled for this event.</p>
                       <div class="d-flex justify-content-center gap-3 flex-wrap mb-4">
                           <div class="bg-white bg-opacity-10 px-4 py-2 rounded-pill d-flex align-items-center gap-2 border border-white border-opacity-25 shadow-sm">
                               <i class="ti ti-alarm fs-5 text-warning"></i>
                               <span class="fw-bold">VOTING ENDS IN: <span id="eventCountdown">02d : 14h : 35m : 12s</span></span>
                           </div>
                       </div>
                       <div class="d-flex justify-content-center gap-3 flex-wrap">
                           <button class="btn btn-white bg-white text-primary fw-bold rounded-pill px-4 py-2 border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#votingGuidelinesModal">
                               <i class="ti ti-info-circle me-1"></i> Voting Guidelines
                           </button>
                           <button class="btn btn-warning fw-bold rounded-pill px-4 py-2 border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#buyVotesModal">
                               <i class="ti ti-shopping-cart me-1"></i> Buy Extra Votes
                           </button>
                       </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-4">
                            <div class="wallet-card p-4 shadow-sm text-center">
                                <span class="d-block text-muted small fw-bold text-uppercase mb-1">My Daily Free Vote</span>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <h2 class="fw-bolder mb-0" id="freeVotesCount">0</h2>
                                    <span class="badge bg-light-success text-success rounded-pill" id="freeVotesBadge">...</span>
                                </div>
                                <p class="small text-muted mb-0 mt-2">Resets in: <span class="fw-bold" id="resetTimer">12:34:56</span></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="wallet-card p-4 shadow-sm text-center">
                                <span class="d-block text-muted small fw-bold text-uppercase mb-1">My Extra Votes</span>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <h2 class="fw-bolder mb-0" id="extraVotesCount">0</h2>
                                    <i class="ti ti-bolt text-warning fs-6"></i>
                                </div>
                                <p class="small text-muted mb-0 mt-2">Purchased through packages</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="wallet-card p-4 shadow-sm text-center clickable" id="communityCard">
                                <span class="d-block text-muted small fw-bold text-uppercase mb-1">Community Activity</span>
                                <div class="avatar-group d-flex justify-content-center mb-1 mt-1" id="communityAvatars">
                                    <!-- Dynamic Avatars -->
                                    <div class="rounded-circle border border-white border-2 ms-n2 bg-primary d-flex align-items-center justify-content-center text-white small fw-bold" width="35" style="width:35px; height:35px;" id="communityVoterCount">0</div>
                                </div>
                                <p class="small text-muted mb-0 mt-1">Community Members Voted</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5 row justify-content-center">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="fw-bold mb-2 text-muted text-uppercase small ls-1">Competition Event</label>
                                <select class="form-select search-voting premium-select" id="competitionSelect">
                                    <option value="" disabled selected>Loading active competitions...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="position-relative">
                                <input type="text" class="search-voting" id="candidateSearch" placeholder="Search candidate by name or number...">
                                <i class="ti ti-search position-absolute end-0 top-50 translate-middle-y me-4 fs-6 text-muted"></i>
                            </div>
                        </div>
                    </div>

                    <div id="candidatesContainer" class="row g-4">
                        <!-- Initial Empty State -->
                        <div class="col-12">
                            <div class="empty-state-container">
                                <i class="ti ti-trophy empty-state-icon"></i>
                                <h3 class="fw-bolder text-dark mb-2">Ready to Vote?</h3>
                                <p class="text-muted fs-4 mb-0">Select a competition from the dropdown above to view the candidates and cast your votes!</p>
                            </div>
                        </div>
                    </div>

                    <!-- Modals Area -->
                    <!-- Voting Guidelines Modal -->
                    <div class="modal fade" id="votingGuidelinesModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4 shadow-lg">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bolder">Voting Guidelines</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="guideline-item">
                                        <div class="guideline-icon"><i class="ti ti-gift fs-5"></i></div>
                                        <div>
                                            <h6 class="fw-bold mb-1">Daily Free Vote</h6>
                                            <p class="small text-muted mb-0">Every registered user gets 1 free vote per day. Use it or lose it! Free votes do not stack.</p>
                                        </div>
                                    </div>
                                    <div class="guideline-item">
                                        <div class="guideline-icon"><i class="ti ti-bolt fs-5"></i></div>
                                        <div>
                                            <h6 class="fw-bold mb-1">Extra Voting Power</h6>
                                            <p class="small text-muted mb-0">Want more voice? You can purchase "Extra Votes" which can be used anytime before the event ends.</p>
                                        </div>
                                    </div>
                                    <div class="guideline-item">
                                        <div class="guideline-icon"><i class="ti ti-shield-check fs-5"></i></div>
                                        <div>
                                            <h6 class="fw-bold mb-1">Fair Play Guarantee</h6>
                                            <p class="small text-muted mb-0">Our system uses advanced bot detection. One account per person only. Suspected fraud will result in vote disqualification.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buy Votes Modal -->
                    <div class="modal fade" id="buyVotesModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0 rounded-4 shadow-lg">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bolder">Boost Your Candidate</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="mb-4 d-flex justify-content-between align-items-center">
                                        <p class="text-muted mb-0">Choose a package to increase your voting power.</p>
                                        <a href="javascript:void(0)" class="small fw-bold text-primary" data-bs-toggle="modal" data-bs-target="#buyGuidelineModal">Buy Guide?</a>
                                    </div>
                                    <div class="row g-3" id="votePackagesContainer">
                                        <!-- Dynamic packages will be loaded here -->
                                        <div class="col-12 text-center py-5">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2 text-muted">Loading packages...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buy Guideline Modal -->
                    <div class="modal fade" id="buyGuidelineModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4 shadow-lg text-white" style="background: #111;">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bolder text-white">Purchase Guidelines</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <ol class="list-group list-group-flush list-group-numbered bg-transparent">
                                        <li class="list-group-item bg-transparent text-white-50 border-white border-opacity-10 py-3">Select your preferred vote package.</li>
                                        <li class="list-group-item bg-transparent text-white-50 border-white border-opacity-10 py-3">Complete payment via GCash, PayMaya, or Credit Card.</li>
                                        <li class="list-group-item bg-transparent text-white-50 border-white border-opacity-10 py-3">Once payment is verified, extra votes will be added to your balance instantly.</li>
                                        <li class="list-group-item bg-transparent text-white-50 border-white border-opacity-0 py-3">Use your extra votes anytime before the final tabulation.</li>
                                    </ol>
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 mt-3">
                                        <small class="text-primary"><i class="ti ti-alert-circle me-1"></i> All purchases are final and non-refundable. Please ensure you are voting for the correct candidate.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Modal -->
                    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4 shadow-lg">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bolder">Complete Your Purchase</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded-3">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded-2">
                                            <i class="ti ti-package fs-6 text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0" id="paymentPkgName">Starter Pack</h6>
                                            <small class="text-muted" id="paymentPkgVotes">10 Votes</small>
                                        </div>
                                        <div class="ms-auto text-end">
                                            <h5 class="fw-bolder text-primary mb-0" id="paymentPkgPrice">₱50</h5>
                                        </div>
                                    </div>

                                    <div class="payment-details mb-4">
                                        <div class="text-center mb-3">
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/cb/GCash_logo.svg/1024px-GCash_logo.svg.png" alt="GCash" height="30" class="mb-2">
                                            <p class="text-muted small">Scan or Pay using the details below</p>
                                        </div>
                                        
                                        <div class="border rounded-3 p-3 bg-white mb-3">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted">Account Name</span>
                                                <span class="fw-bold">AURORA ADMIN</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">GCash Number</span>
                                                <span class="fw-bold text-primary fs-5">0912 345 6789</span>
                                            </div>
                                        </div>

                                        <div class="bg-light-info p-3 rounded-3">
                                            <p class="mb-0 small text-info">
                                                <i class="ti ti-info-circle me-1"></i>
                                                Please send the exact amount to the GCash number above. After sending, click <strong>Confirm Payment</strong> below to automatically verify and add votes to your wallet.
                                            </p>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm" id="btnConfirmPayment">
                                        CONFIRM PAYMENT
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Voter List Modal -->
                    <div class="modal fade" id="voterListModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content border-0 rounded-4 shadow-lg">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bolder">Community Participants</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Participant Name</th>
                                                    <th class="text-center">Total Contributions</th>
                                                    <th>Last Active</th>
                                                </tr>
                                            </thead>
                                            <tbody id="voterListTableBody">
                                                <tr>
                                                    <td colspan="3" class="text-center py-4">
                                                        <div class="aurora-loader" style="width: 40px; height: 40px;"></div>
                                                        <p class="text-muted mb-0">Fetching community data...</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
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
            let freeVotes = 1;
            let extraVotes = 25;
            let candidatesData = [];

            function updateUI() {
                $.ajax({
                    url: 'endpoints/voting_handler.php',
                    method: 'POST',
                    data: { action: 'get_user_info' },
                    success: function(response) {
                        if (response.status === 'success') {
                            freeVotes = response.data.free_vote_available ? 1 : 0;
                            extraVotes = response.data.extra_votes;
                            
                            $('#freeVotesCount').text(freeVotes);
                            $('#extraVotesCount').text(extraVotes.toLocaleString());
                            
                            // Update Community Avatars
                            const avatarGroup = $('#communityAvatars');
                            const voterSamples = response.data.voter_samples || [];
                            const totalVoters = response.data.total_community_voters;
                            
                            let avatarHtml = '';
                            voterSamples.forEach((name, index) => {
                                const marginClass = index > 0 ? 'ms-n2' : '';
                                avatarHtml += `<img src="https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random" class="rounded-circle border border-white border-2 ${marginClass}" width="35" alt="${name}">`;
                            });
                            
                            // Always add the count pill last
                            const countPillMargin = voterSamples.length > 0 ? 'ms-n2' : '';
                            avatarHtml += `<div class="rounded-circle border border-white border-2 ${countPillMargin} bg-primary d-flex align-items-center justify-content-center text-white small fw-bold" style="width:35px; height:35px;" id="communityVoterCount">${totalVoters.toLocaleString()}</div>`;
                            
                            avatarGroup.html(avatarHtml);
                            
                            if(freeVotes <= 0) {
                                $('.btn-vote-action[data-type="free"]').prop('disabled', true)
                                    .html('<i class="ti ti-check me-1"></i> Already Voted');
                                $('#freeVotesBadge').removeClass('bg-light-success text-success').addClass('bg-light-danger text-danger').text('Used');
                            } else {
                                $('.btn-vote-action[data-type="free"]').prop('disabled', false)
                                    .text('USE DAILY FREE VOTE');
                                $('#freeVotesBadge').removeClass('bg-light-danger text-danger').addClass('bg-light-success text-success').text('Available');
                            }
                        }
                    }
                });
            }

            function initVoting() {
                $.ajax({
                    url: 'endpoints/voting_handler.php',
                    method: 'POST',
                    data: { action: 'get_active_competitions' },
                    success: function(response) {
                        if (response.status === 'success') {
                            const select = $('#competitionSelect');
                            select.empty().append('<option value="" disabled selected>Select a competition</option>');
                            response.data.forEach(comp => {
                                select.append(`<option value="${comp.id}">${comp.title}</option>`);
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }

            let eventEndDate = null;
            let eventStartDate = null;

            function loadCandidates(compId) {
                const container = $('#candidatesContainer');
                container.html(`
                    <div class="col-12 text-center py-5">
                        <div class="aurora-loader"></div>
                        <h4 class="fw-bold text-primary mb-1">Fetching Talent...</h4>
                        <p class="text-muted">Loading candidate profiles for this event.</p>
                    </div>
                `);

                $.ajax({
                    url: 'endpoints/voting_handler.php',
                    method: 'POST',
                    data: { action: 'get_candidates', competition_id: compId },
                    success: function(response) {
                        if (response.status === 'success') {
                            candidatesData = response.data;
                            
                            // Update Hero Section
                            if (response.competition) {
                                $('.hero-section h1').text(response.competition.title);
                                $('.hero-section p.fs-5').text(response.competition.description || "Vote for your favorite candidate! Each vote counts towards the final popularity results.");
                                
                                if (response.competition.start_date && response.competition.start_time) {
                                    eventStartDate = new Date(`${response.competition.start_date}T${response.competition.start_time}`);
                                } else {
                                    eventStartDate = null;
                                }

                                if (response.competition.end_date && response.competition.end_time) {
                                    eventEndDate = new Date(`${response.competition.end_date}T${response.competition.end_time}`);
                                } else {
                                    eventEndDate = null;
                                }
                            }

                            renderCandidates(candidatesData);
                            updateVotingButtonsState(); // Initial state check
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            }

            let currentVotingStatus = 'active'; // track status to detect transitions

            function updateVotingButtonsState() {
                const now = new Date();
                const isEnded = eventStartDate && now > eventStartDate;
                
                let newStatus = 'active';
                if (isEnded) newStatus = 'ended';

                // Only update if status changed to avoid unnecessary DOM manipulation
                if (newStatus !== currentVotingStatus) {
                    currentVotingStatus = newStatus;
                    
                    $('.btn-vote-action').each(function() {
                        const btn = $(this);
                        if (newStatus === 'ended') {
                            btn.prop('disabled', true).removeClass('vote-btn vote-btn-outline btn-primary btn-outline-primary')
                               .addClass('btn-danger opacity-50').text('Voting Ended');
                        } else {
                            // Status is active
                            renderCandidates(candidatesData);
                        }
                    });

                    // Update parent container if needed
                    if (newStatus === 'ended') {
                        $('.vote-quantity-select').prop('disabled', true);
                    }
                }
            }

            function renderCandidates(data) {
                const container = $('#candidatesContainer');
                container.empty();

                if (data.length === 0) {
                    container.html(`
                        <div class="col-12 mt-4">
                            <div class="empty-state-container border-warning bg-light-warning bg-opacity-10">
                                <i class="ti ti-user-off empty-state-icon text-warning opacity-50"></i>
                                <h3 class="fw-bolder text-dark mb-2">No Candidates Found</h3>
                                <p class="text-muted fs-4 mb-0">It seems there are no candidates registered for this competition yet.</p>
                            </div>
                        </div>
                    `);
                    return;
                }

                // Group candidates by department
                const depts = {};
                data.forEach(cand => {
                    const dept = cand.department || 'Others';
                    if (!depts[dept]) depts[dept] = { male: [], female: [], other: [] };
                    if (cand.gender === 'male') depts[dept].male.push(cand);
                    else if (cand.gender === 'female') depts[dept].female.push(cand);
                    else depts[dept].other.push(cand);
                });

                Object.keys(depts).forEach(deptName => {
                    const dept = depts[deptName];
                    container.append(`<div class="col-12 mt-5 mb-2"><h3 class="fw-bolder text-primary border-bottom pb-2">${deptName}</h3></div>`);

                    const maxPairs = Math.max(dept.male.length, dept.female.length);
                    for (let i = 0; i < maxPairs; i++) {
                        const male = dept.male[i];
                        const female = dept.female[i];

                        let rowHtml = '<div class="row g-4 mb-4">';
                        if (male) rowHtml += `<div class="col-md-6">${generateCandidateHtml(male)}</div>`;
                        else rowHtml += '<div class="col-md-6 d-none d-md-block"></div>';

                        if (female) rowHtml += `<div class="col-md-6">${generateCandidateHtml(female)}</div>`;
                        else rowHtml += '<div class="col-md-6 d-none d-md-block"></div>';
                        
                        rowHtml += '</div>';
                        container.append(rowHtml);
                    }

                    // Render "other" genders if any
                    if (dept.other.length > 0) {
                        let otherHtml = '<div class="row g-4">';
                        dept.other.forEach(cand => {
                            otherHtml += `<div class="col-md-6">${generateCandidateHtml(cand)}</div>`;
                        });
                        otherHtml += '</div>';
                        container.append(otherHtml);
                    }
                });
            }

            function generateCandidateHtml(cand) {
                const now = new Date();
                const isEnded = eventStartDate && now > eventStartDate;
                const photo = cand.photo_url ? '../' + cand.photo_url : 'src/assets/images/profile/user-1.jpg';
                
                let voteSection = "";
                if (isEnded) {
                    voteSection = `<button class="btn btn-danger w-100 rounded-pill py-2 fw-bold opacity-50" disabled>Voting Ended</button>`;
                } else {
                    const freeBtnClass = freeVotes <= 0 ? 'vote-btn-outline' : 'vote-btn-outline'; // Keep original class
                    const freeBtnText = freeVotes <= 0 ? '<i class="ti ti-check me-1"></i> Already Voted' : 'USE DAILY FREE VOTE';
                    const freeBtnDisabled = freeVotes <= 0 ? 'disabled' : '';

                    voteSection = `
                        <div class="mb-3">
                            <label class="small text-muted fw-bold d-block mb-2 text-uppercase">Select Vote Quantity</label>
                            <select class="form-select form-select-sm rounded-pill border-light bg-light vote-quantity-select">
                                <option value="1">1 Vote</option>
                                <option value="5">5 Votes</option>
                                <option value="10">10 Votes</option>
                                <option value="25">25 Votes</option>
                                <option value="50">50 Votes</option>
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-12 mb-1">
                                <button class="${freeBtnClass} btn-vote-action" data-type="free" data-id="${cand.id}" data-name="${cand.first_name} ${cand.last_name}" ${freeBtnDisabled}>${freeBtnText}</button>
                            </div>
                            <div class="col-12">
                                <button class="vote-btn btn-vote-action" data-type="paid" data-id="${cand.id}" data-name="${cand.first_name} ${cand.last_name}">BOOST VOTE</button>
                            </div>
                        </div>
                    `;
                }

                const voteLabel = parseInt(cand.total_votes) === 1 ? 'Vote' : 'Votes';
                const g = (cand.gender || '').toLowerCase();

                return `
                    <div class="vote-card position-relative shadow-sm card" data-candidate-id="${cand.id}">
                        <span class="vote-count-badge">${parseFloat(cand.total_votes).toLocaleString()} ${voteLabel}</span>
                        <img src="${photo}" class="candidate-thumb" alt="${cand.first_name}" onerror="this.src='src/assets/images/profile/user-1.jpg'">
                        <div class="card-body p-3">
                            <div class="mb-3">
                                <div class="mb-2 d-flex align-items-center gap-2 flex-wrap">
                                    <span class="category-pill mb-0">Candidate #${cand.candidate_number}</span>
                                    ${g === 'male' ? '<span class="badge bg-primary-subtle text-primary rounded-pill border border-primary-subtle ms-auto" style="font-size:0.7rem;">♂ Male</span>' : ''}
                                    ${g === 'female' ? '<span class="badge bg-danger-subtle text-danger rounded-pill border border-danger-subtle ms-auto" style="font-size:0.7rem;">♀ Female</span>' : ''}
                                </div>
                                <h5 class="fw-bold mb-1">${cand.first_name} ${cand.last_name}</h5>
                                <p class="text-muted small mb-0">${cand.department || ''}</p>
                            </div>
                            ${voteSection}
                        </div>
                    </div>
                `;
            }

            updateUI();
            initVoting();

            $('#competitionSelect').change(function() {
                loadCandidates($(this).val());
            });

            $(document).on('click', '.btn-vote-action', function() {
                const candId = $(this).data('id');
                const name = $(this).data('name');
                const type = $(this).data('type');
                const quantity = parseInt($(this).closest('.card-body').find('.vote-quantity-select').val()) || 1;

                if (type === 'free' && freeVotes <= 0) {
                    Swal.fire('No Free Votes', 'You have already used your free vote for today. It resets at midnight!', 'warning');
                    return;
                }
                
                if (type === 'paid' && extraVotes < quantity) {
                    Swal.fire('Insufficient Votes', `You need ${quantity} votes for this boost, but only have ${extraVotes} left.`, 'warning');
                    $('#buyVotesModal').modal('show');
                    return;
                }

                const voteText = type === 'free' ? 'your daily free vote' : `${quantity} Boost Votes`;

                Swal.fire({
                    title: 'Confirm Vote',
                    text: `Are you sure you want to cast ${voteText} for ${name}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm Vote',
                    confirmButtonColor: '#10b981',
                }).then((result) => {
                    if (result.isConfirmed) {
                        const btn = $(this);
                        const originalHtml = btn.html();
                        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Casting...');
                        
                        $.ajax({
                            url: 'endpoints/voting_handler.php',
                            method: 'POST',
                            data: {
                                action: 'cast_vote',
                                candidate_id: candId,
                                type: type,
                                quantity: quantity
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    updateUI();
                                    Swal.fire('Success!', response.message, 'success');
                                    
                                    // Locally update the UI vote count for feedback
                                    const card = $(`.vote-card[data-candidate-id="${candId}"]`);
                                    const badge = card.find('.vote-count-badge');
                                    let currentVotes = parseInt(badge.text().replace(/,/g, '')) || 0;
                                    const total = currentVotes + (type === 'free' ? 1 : quantity);
                                    const label = total === 1 ? 'Vote' : 'Votes';
                                    badge.text(total.toLocaleString() + ' ' + label);
                                } else {
                                    Swal.fire('Voting Failed', response.message, 'error');
                                }
                                btn.prop('disabled', false).html(originalHtml);
                            },
                            error: function() {
                                Swal.fire('Error', 'Failed to connect to voting server.', 'error');
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        });
                    }
                });
            });

            function loadBuyablePackages() {
                const container = $('#votePackagesContainer');

                // Fetch fee settings first, then load packages
                $.ajax({
                    url: 'endpoints/voting_settings_handler.php',
                    method: 'POST',
                    dataType: 'json',
                    data: { action: 'get_payment_fee_info' },
                    success: function(feeRes) {
                        const feeActive  = feeRes.status === 'success' && feeRes.data.fee_passthrough;
                        const feeRate    = feeRes.status === 'success' ? parseFloat(feeRes.data.fee_rate) : 4;
                        const pmActive   = feeRes.status === 'success' && feeRes.data.active;

                        // Now fetch the packages
                        $.ajax({
                            url: 'endpoints/vote_package_handler.php',
                            method: 'GET',
                            data: { action: 'fetch' },
                            success: function(response) {
                                try {
                                    const res = JSON.parse(response);
                                    if (res.status === 'success') {
                                        const activePackages = res.data.filter(pkg => pkg.is_active);
                                        if (activePackages.length === 0) {
                                            container.html('<div class="col-12 text-center py-4 text-muted">No packages available at the moment.</div>');
                                            return;
                                        }

                                        let html = '';
                                        activePackages.forEach(pkg => {
                                            const isPopular = !!pkg.promo_label;
                                            const cardClass  = isPopular ? 'border-primary bg-primary-subtle border-2 position-relative overflow-hidden' : '';
                                            const badgeHtml  = isPopular ? `<div class="position-absolute top-0 end-0 bg-primary text-white small px-2 py-1 fw-bold">${pkg.promo_label.toUpperCase()}</div>` : '';

                                            let icon = 'bolt';
                                            if (pkg.votes_count >= 100) icon = 'award';
                                            if (pkg.votes_count >= 500) icon = 'diamond';

                                            const netPrice  = parseFloat(pkg.price);
                                            const fmtNet    = netPrice.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                                            // Fee breakdown (only shown when fee pass-through is ON)
                                            let priceBlock = '';
                                            if (feeActive && feeRate > 0) {
                                                // gross = ceil(net / (1 - fee%)) to nearest centavo
                                                const grossPrice = Math.ceil((netPrice / (1 - feeRate / 100)) * 100) / 100;
                                                const feeAmount  = +(grossPrice - netPrice).toFixed(2);
                                                const fmtGross   = grossPrice.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                                const fmtFee     = feeAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                                                priceBlock = `
                                                    <div class="border rounded-3 p-2 mb-3 text-start" style="font-size:0.82rem; background:#f8faff;">
                                                        <div class="d-flex justify-content-between text-muted mb-1">
                                                            <span>Package price</span>
                                                            <span>₱${fmtNet}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between text-muted mb-1">
                                                            <span>PayMongo fee (${feeRate}%)</span>
                                                            <span class="text-warning">+₱${fmtFee}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between fw-bold border-top pt-1 mt-1">
                                                            <span>You pay</span>
                                                            <span class="text-primary">₱${fmtGross}</span>
                                                        </div>
                                                    </div>`;
                                            } else {
                                                priceBlock = `<h3 class="fw-bold text-primary mb-3">₱${fmtNet}</h3>`;
                                            }

                                            html += `
                                                <div class="col-md-4">
                                                    <div class="package-card p-4 text-center ${cardClass}">
                                                        ${badgeHtml}
                                                        <div class="mb-3 text-warning">
                                                            <i class="ti ti-${icon} fs-7"></i>
                                                        </div>
                                                        <h4 class="fw-bolder mb-1">${pkg.votes_count.toLocaleString()} Votes</h4>
                                                        <p class="text-muted small mb-3">${pkg.name}</p>
                                                        ${priceBlock}
                                                        <button class="btn btn-primary w-100 rounded-pill btn-buy-votes"
                                                            data-votes="${pkg.votes_count}"
                                                            data-price="${pkg.price}"
                                                            data-id="${pkg.id}">
                                                            ${pmActive ? '<i class="ti ti-bolt me-1"></i> Buy Now' : 'Buy Now'}
                                                        </button>
                                                    </div>
                                                </div>
                                            `;
                                        });
                                        container.html(html);
                                    }
                                } catch (e) {
                                    container.html('<div class="col-12 text-center py-4 text-danger">Failed to load packages.</div>');
                                }
                            }
                        });
                    },
                    error: function() {
                        // If settings can't be fetched, load packages without fee info
                        container.html('<div class="col-12 text-center py-4 text-muted">Could not load payment info. Please refresh.</div>');
                    }
                });
            }

            $('#buyVotesModal').on('show.bs.modal', function() {
                loadBuyablePackages();
            });

            let selectedPackageId = null;

            $(document).on('click', '.btn-buy-votes', function() {
                const btn = $(this);
                const packageId = btn.data('id');
                const votes  = btn.data('votes');
                const price  = btn.data('price');
                const name   = btn.closest('.package-card').find('p.text-muted').text().trim() || 'Vote Package';

                const originalHtml = btn.html();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Creating Checkout...');

                $.ajax({
                    url: 'endpoints/paymongo_handler.php',
                    method: 'POST',
                    data: { action: 'create_checkout', package_id: packageId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success' && response.checkout_url) {
                            // Redirect to PayMongo hosted checkout page
                            window.location.href = response.checkout_url;
                        } else {
                            Swal.fire('Checkout Failed', response.message || 'Unable to create checkout session.', 'error');
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Failed to connect to payment server. Please try again.', 'error');
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });


            $('#btnConfirmPayment').click(function() {
                const btn = $(this);
                const originalHtml = btn.html();
                
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> VERIFYING PAYMENT...');
                
                $.ajax({
                    url: 'endpoints/voting_handler.php',
                    method: 'POST',
                    data: { 
                        action: 'process_purchase',
                        package_id: selectedPackageId
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            updateUI();
                            $('#paymentModal').modal('hide');
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#6366f1'
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                        btn.prop('disabled', false).html(originalHtml);
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to connect to server.', 'error');
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            $('#candidateSearch').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $(".vote-card").each(function() {
                    $(this).closest('.col-md-6').toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
                // Also hide/show the row if both candidates in it are hidden
                $('.row.g-4.mb-4').each(function() {
                    const visible = $(this).find('.col-md-6:visible').length > 0;
                    $(this).toggle(visible);
                });
            });

            function showVoterList() {
                const modal = $('#voterListModal');
                const tbody = $('#voterListTableBody');
                
                // Show loader while fetching
                tbody.html(`
                    <tr>
                        <td colspan="3" class="text-center py-4">
                            <div class="aurora-loader" style="width: 40px; height: 40px;"></div>
                            <p class="text-muted mb-0">Updating community ledger...</p>
                        </td>
                    </tr>
                `);
                
                modal.modal('show');
                
                $.ajax({
                    url: 'endpoints/voting_handler.php',
                    method: 'POST',
                    data: { action: 'get_voter_list' },
                    success: function(response) {
                        if (response.status === 'success') {
                            if (response.data.length === 0) {
                                tbody.html('<tr><td colspan="3" class="text-center py-4 text-muted">No voters yet. Be the first!</td></tr>');
                                return;
                            }
                            
                            let html = '';
                            response.data.forEach(voter => {
                                html += `
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(voter.name)}&background=random" class="rounded-circle" width="35">
                                                <span class="fw-bold text-dark">${voter.name}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary rounded-pill px-3">${voter.votes.toLocaleString()} Votes</span>
                                        </td>
                                        <td class="small text-muted">${voter.last_active}</td>
                                    </tr>
                                `;
                            });
                            tbody.html(html);
                        } else {
                            tbody.html(`<tr><td colspan="3" class="text-center text-danger py-4">${response.message}</td></tr>`);
                        }
                    },
                    error: function() {
                        tbody.html('<tr><td colspan="3" class="text-center text-danger py-4">Failed to fetch community data.</td></tr>');
                    }
                });
            }

            $('#communityCard').click(function() {
                showVoterList();
            });

            // Timers Logic
            setInterval(() => {
                const now = new Date();
                
                // Reset timer (Daily Free Vote)
                const night = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 0);
                const resetDiff = night - now;
                const rh = Math.floor(resetDiff / 3600000);
                const rm = Math.floor((resetDiff % 3600000) / 60000);
                const rs = Math.floor((resetDiff % 60000) / 1000);
                const timerStr = `${rh.toString().padStart(2, '0')}:${rm.toString().padStart(2, '0')}:${rs.toString().padStart(2, '0')}`;
                $('#resetTimer').text(timerStr);

                // Update Free Vote buttons if already used (Only if competition is still active)
                if (freeVotes <= 0 && currentVotingStatus !== 'ended') {
                    $('.btn-vote-action[data-type="free"]')
                        .prop('disabled', true)
                        .html(`<i class="ti ti-history me-1"></i> Used (${timerStr})`);
                }

                // Global Event Timer (Now targets eventStartDate as the deadline)
                if (eventStartDate) {
                    const eventDiff = eventStartDate - now;
                    if(eventDiff >= 0) {
                        const d = Math.floor(eventDiff / 86400000);
                        const h = Math.floor((eventDiff % 86400000) / 3600000);
                        const m = Math.floor((eventDiff % 3600000) / 60000);
                        const s = Math.floor((eventDiff % 60000) / 1000);
                        $('#eventCountdown').parent().contents().first().replaceWith("VOTING ENDS IN: ");
                        $('#eventCountdown').text(`${d}d : ${h.toString().padStart(2, '0')}h : ${m.toString().padStart(2, '0')}m : ${s.toString().padStart(2, '0')}s`);
                    } else {
                        $('#eventCountdown').parent().contents().first().replaceWith("STATUS: ");
                        $('#eventCountdown').text("EVENT CONCLUDED");
                    }
                } else {
                    $('#eventCountdown').text("SELECT COMPETITION");
                }

                // Reactive UI: Update button states if status shifted
                if (eventStartDate || eventEndDate) {
                    updateVotingButtonsState();
                }
            }, 1000);
        });
    </script>
</body>

</html>
