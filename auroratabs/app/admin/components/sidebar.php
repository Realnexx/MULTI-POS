<aside class="left-sidebar">
<?php
  require_once '../auth/helpers/security_helper.php';
  require_once '../auth/config/db_connection.php';
  
  // Pass $conn explicitly to avoid scope issues
  $dashboard_view = has_permission('Dashboard', 'view', $conn);
  $can_view_competitions = has_permission('Competitions', 'view', $conn);
  $can_manage_competitions = has_permission('Competitions', 'manage', $conn);
  $can_view_categories = has_permission('Categories', 'view', $conn);
  $can_view_criteria = has_permission('Criteria', 'view', $conn);

  $can_view_candidates = has_permission('Candidates', 'view', $conn);
  $can_view_assign_candidates = has_permission('Assign Candidates', 'view', $conn);
  $can_view_deleted_candidates = has_permission('Deleted Candidates', 'view', $conn);

  $can_view_judging = has_permission('Judging', 'view', $conn);
  $can_view_judge_assignments = has_permission('Judge Assignments', 'view', $conn);
  $can_view_judge_locks = has_permission('Judge Locks', 'view', $conn);
  $can_view_judge_reports = has_permission('Judge Reports', 'view', $conn);
  $can_view_judge_scoring = has_permission('Judge Scoring', 'view', $conn);
  $can_view_judge_submissions = has_permission('Judge Submissions', 'view', $conn);


  $can_view_formula_management = has_permission('Formula Management', 'view', $conn);
  $can_view_formula_builder = has_permission('Formula Builder', 'view', $conn);
  $can_view_criteria_weights = has_permission('Criteria Weights', 'view', $conn);
  $can_view_recalculate_results = has_permission('Recalculate Results', 'view', $conn);
  $can_view_tabulators = has_permission('Tabulators', 'view', $conn);

  $view_people_choice = has_permission('People Choice Settings', 'view', $conn);
  $view_vote_packages = has_permission('Vote Packages/Tiers', 'view', $conn);
  $view_voting_on_off = has_permission('Vote ON/OFF', 'view', $conn);
  $view_vote_logs = has_permission('Vote Logs', 'view', $conn);

  $view_live_results = has_permission('Live Results', 'view', $conn);
  $view_final_results = has_permission('Final Results', 'view', $conn);
  $view_rankings = has_permission('Rankings', 'view', $conn);

  $can_view_audience_voting = has_permission('Audience Voting', 'view', $conn);
  $can_view_audience_rankings = has_permission('Audience Rankings', 'view', $conn);

  $can_view_display_screens = has_permission('Display Screens', 'view', $conn);
  $can_view_display_settings = has_permission('Display Settings', 'view', $conn);

  $can_view_reports = has_permission('Voting Reports', 'view', $conn);
  $can_view_competition_summary = has_permission('Competition Summary', 'view', $conn);
  $can_view_export_data = has_permission('Export PDF/Excel', 'view', $conn);

  $can_view_organization = has_permission('Organization', 'view', $conn);

  $can_view_users = has_permission('Users', 'view', $conn);
  $can_view_roles_permissions = has_permission('Roles and Permissions', 'view', $conn);
  $can_view_judges = has_permission('Judges', 'view', $conn);

  $can_view_general_settings = has_permission('General Settings', 'view', $conn);
  $can_view_security_settings = has_permission('Security Settings', 'view', $conn);
  $can_view_activity_logs = has_permission('Activity Logs', 'view', $conn);

  $can_view_pricing_plans = has_permission('Pricing Plans', 'view', $conn);
  $can_view_vote_purchases = has_permission('Vote Purchases', 'view', $conn);
  $can_view_transactions = has_permission('Transactions', 'view', $conn);

  $can_view_account = has_permission('Account', 'view', $conn);

  $can_view_manage_students = has_permission('Manage Students', 'view', $conn);
  $can_view_student_attendance = has_permission('Student Attendance', 'view', $conn);
  $can_view_hours_summary = has_permission('Hours Summary', 'view', $conn);
  $can_view_ojt_student_registry = has_permission('OJT Student Registry', 'view', $conn);
?>
      <!-- Sidebar scroll-->
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="index.php" class="logo-wrap">
            <img 
              src="src/assets/images/logos/aurora-logo.png" 
              alt="Aurora Tabs Logo"
              class="logo-img"
            />
            <span class="sitename">Aurora Tabs</span>
          </a>

          <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
          </div>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
          <ul id="sidebarnav">
            <?php if ($dashboard_view): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">🏠 DASHBOARD</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="index.php" aria-expanded="false">
                <iconify-icon icon="solar:widget-add-line-duotone"></iconify-icon>
                <span class="hide-menu">Overview</span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_competitions || $can_view_categories || $can_view_criteria): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">🏆 COMPETITIONS</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg has-arrow" href="javascript:void(0)" aria-expanded="false">
                <iconify-icon icon="solar:medal-ribbons-star-line-duotone"></iconify-icon>
                <span class="hide-menu">Competitions </span>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <?php if ($can_view_competitions): ?>
                <li class="sidebar-item"><a href="competitions.php" class="sidebar-link"><span class="icon-small"></span>All Competitions <span class="badge bg-success">Done</span></a></li>
                <?php endif; ?>
                <?php if ($can_manage_competitions): ?>
                <li class="sidebar-item"><a href="create-competition.php" class="sidebar-link"><span class="icon-small"></span>Create Competition <span class="badge bg-success">Done</span></a></li>
                <?php endif; ?>
                <?php if ($can_view_categories): ?>
                <li class="sidebar-item"><a href="categories.php" class="sidebar-link"><span class="icon-small"></span>Categories <span class="badge bg-success">Done</span></a></li>
                <?php endif; ?>
                <?php if ($can_view_criteria): ?>
                <li class="sidebar-item"><a href="criteria.php" class="sidebar-link"><span class="icon-small"></span>Criteria <span class="badge bg-success">Done</span></a></li>
                <?php endif; ?>
              </ul>
            </li>
            <?php endif; ?>
            <?php if ($can_view_candidates || $can_view_assign_candidates || $can_view_deleted_candidates): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">🧑‍🎤 CANDIDATES</span>
            </li>
            <?php if ($can_view_candidates): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="candidates.php" aria-expanded="false">
                <iconify-icon icon="solar:users-group-two-rounded-line-duotone"></iconify-icon>
                <span class="hide-menu">All Candidates <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_assign_candidates): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="assign-candidates.php" aria-expanded="false">
                <iconify-icon icon="solar:user-plus-rounded-line-duotone"></iconify-icon>
                <span class="hide-menu">Candidate Assignment <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_deleted_candidates): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="deleted-candidates.php" aria-expanded="false">
                <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                <span class="hide-menu">Deleted Candidates <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>
           <?php endif; ?>

           <?php if ($can_view_judging || $can_view_judge_assignments || $can_view_judge_locks || $can_view_judge_reports || $can_view_judge_scoring || $can_view_judge_submissions): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">🧑‍⚖️ JUDGING</span>
            </li>
            <?php if ($can_view_judge_scoring): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="judge-scoring.php" aria-expanded="false">
                <iconify-icon icon="solar:star-fall-line-duotone"></iconify-icon>
                <span class="hide-menu">Live Scoring</span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_judge_assignments): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="judge-assignments.php" aria-expanded="false">
                <iconify-icon icon="solar:user-plus-line-duotone"></iconify-icon>
                <span class="hide-menu">Judge Assignment <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_judge_submissions): ?>  
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="judge-submissions.php" aria-expanded="false">
                <iconify-icon icon="solar:check-read-line-duotone"></iconify-icon>
                <span class="hide-menu">Judge Submissions <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_judge_locks): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="judge-locks.php" aria-expanded="false">
                <iconify-icon icon="solar:lock-password-line-duotone"></iconify-icon>
                <span class="hide-menu">Judge Locks <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($can_view_formula_management || $can_view_formula_builder || $can_view_criteria_weights || $can_view_recalculate_results): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">🧮 TABULATION & FORMULAS</span>
            </li>

            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg has-arrow" href="javascript:void(0)" aria-expanded="false">
                <iconify-icon icon="solar:calculator-minimalistic-line-duotone"></iconify-icon>
                <span class="hide-menu">Tabulation</span>
              </a>
              
              <ul aria-expanded="false" class="collapse first-level">
                <?php if ($can_view_formula_management): ?>
                <li class="sidebar-item"><a href="formula-management.php" class="sidebar-link"><span class="icon-small"></span>Formula Management <span class="badge bg-success">Done</span></a></li>
                <?php endif; ?>
                <?php if ($can_view_formula_builder): ?>
                <li class="sidebar-item"><a href="formula-builder.php" class="sidebar-link"><span class="icon-small"></span>Formula Builder <span class="badge bg-success">Done</span></a></li>
                <?php endif; ?>
                <?php if ($can_view_criteria_weights): ?>
                <li class="sidebar-item"><a href="criteria-weights.php" class="sidebar-link"><span class="icon-small"></span>Criteria Weights <span class="badge bg-success">Done</span></a></li>
                <?php endif; ?>
                <?php if ($can_view_recalculate_results): ?>
                <li class="sidebar-item"><a href="recalculate-results.php" class="sidebar-link"><span class="icon-small"></span>Recalculate Results <span class="badge bg-success">Done</span></a></li>
                <?php endif; ?>
                <?php if ($can_view_tabulators): ?>
                <li class="sidebar-item"><a href="tabulators.php" class="sidebar-link"><span class="icon-small"></span>Tabulators</a></li>
                <?php endif; ?>
              </ul>
            </li>
            <?php endif; ?>

            <?php if ($view_people_choice || $view_vote_packages || $view_voting_on_off || $view_vote_logs): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">🗳️ VOTING MANAGEMENT</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg has-arrow" href="javascript:void(0)" aria-expanded="false">
                <iconify-icon icon="solar:box-line-duotone"></iconify-icon>
                <span class="hide-menu">Voting</span>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <?php if ($view_people_choice): ?>
                <li class="sidebar-item"><a href="voting-settings.php" class="sidebar-link"><span class="icon-small"></span>People’s Choice Settings</a></li>
                <?php endif; ?>
                <?php if ($view_vote_packages): ?>
                <li class="sidebar-item"><a href="vote-packages.php" class="sidebar-link"><span class="icon-small"></span>Vote Packages / Tiers</a></li>
                <?php endif; ?>
                <?php if ($view_voting_on_off): ?>
                <li class="sidebar-item"><a href="voting-toggle.php" class="sidebar-link"><span class="icon-small"></span>Voting ON / OFF</a></li>
                <?php endif; ?>
                <?php if ($view_vote_logs): ?>
                <li class="sidebar-item"><a href="vote-logs.php" class="sidebar-link"><span class="icon-small"></span>Vote Logs</a></li>
                <?php endif; ?>
              </ul>
            </li>
            <?php endif; ?>

            <?php if ($view_live_results || $view_final_results || $view_rankings): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">📊 RESULTS</span>
            </li>
            <?php if ($view_live_results): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="live-results.php" aria-expanded="false">
                <iconify-icon icon="solar:graph-up-line-duotone"></iconify-icon>
                <span class="hide-menu">Live Results <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($view_final_results): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="final-results.php" aria-expanded="false">
                <iconify-icon icon="solar:cup-first-line-duotone"></iconify-icon>
                <span class="hide-menu">Final Results <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($view_rankings): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="rankings.php" aria-expanded="false">
                <iconify-icon icon="solar:list-arrow-down-line-duotone"></iconify-icon>
                <span class="hide-menu">Rankings <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($can_view_audience_voting || $can_view_audience_rankings): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">👨‍👩‍👧‍👦 PUBLIC PORTAL</span>
            </li>
            <?php if ($can_view_audience_voting): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="audience-voting.php" aria-expanded="false">
                <iconify-icon icon="solar:gamepad-old-line-duotone"></iconify-icon>
                <span class="hide-menu">Audience Voting</span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_audience_rankings): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="audience-rankings.php" aria-expanded="false">
                <iconify-icon icon="solar:chart-square-line-duotone"></iconify-icon>
                <span class="hide-menu">Voting Rankings</span>
              </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($can_view_display_screens || $can_view_display_settings): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">📺 LIVE DISPLAY</span>
            </li>
            <?php if ($can_view_display_screens): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="display-screens.php" aria-expanded="false">
                <iconify-icon icon="solar:monitor-line-duotone"></iconify-icon>
                <span class="hide-menu">Display Screens</span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_display_settings): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="display-settings.php" aria-expanded="false">
                <iconify-icon icon="solar:settings-bold-duotone"></iconify-icon>
                <span class="hide-menu">Display Settings</span>
              </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($can_view_reports || $can_view_competition_summary || $can_view_export_data): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">📈 REPORTS</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg has-arrow" href="javascript:void(0)" aria-expanded="false">
                <iconify-icon icon="solar:document-add-line-duotone"></iconify-icon>
                <span class="hide-menu">Reports</span>
              </a>
              <ul aria-expanded="false" class="collapse first-level">
                <?php if($can_view_reports): ?>
                <li class="sidebar-item"><a href="voting-reports.php" class="sidebar-link"><span class="icon-small"></span>Voting Reports</a></li>
                <?php endif; ?>
                <?php if($can_view_judge_reports): ?>
                <li class="sidebar-item"><a href="judge-reports.php" class="sidebar-link"><span class="icon-small"></span>Judge Reports</a></li>
                <?php endif; ?>
                <?php if($can_view_competition_summary): ?>
                <li class="sidebar-item"><a href="competition-summary.php" class="sidebar-link"><span class="icon-small"></span>Competition Summary</a></li>
                <?php endif; ?>
                <?php if($can_view_export_data): ?>
                <li class="sidebar-item"><a href="export-data.php" class="sidebar-link"><span class="icon-small"></span>Export (PDF / Excel)</a></li>
                <?php endif; ?>
               
              </ul>
            </li>
            <?php endif; ?>
            <?php if ($can_view_organization): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">🏢 MANAGEMENT</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="organizations.php" aria-expanded="false">
                <iconify-icon icon="solar:globus-line-duotone"></iconify-icon>
                <span class="hide-menu">Organizations <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>

            <?php if ($can_view_users || $can_view_roles_permissions || $can_view_judges): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">👥 USER MANAGEMENT</span>
            </li>
            <?php if ($can_view_users): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="admin-users.php" aria-expanded="false">
                <iconify-icon icon="solar:user-hands-line-duotone"></iconify-icon>
                <span class="hide-menu">User Management <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_judges): ?>
             <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="judges.php" aria-expanded="false">
                <iconify-icon icon="solar:user-speak-line-duotone"></iconify-icon>
                <span class="hide-menu">Judges <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_roles_permissions): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="roles-permissions.php" aria-expanded="false">
                <iconify-icon icon="solar:shield-keyhole-line-duotone"></iconify-icon>
                <span class="hide-menu">Roles & Permissions <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>
            <?php if ($can_view_manage_students || $can_view_student_attendance || $can_view_hours_summary || $can_view_ojt_student_registry): ?>
            <li class="nav-small-cap">
            <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
            <span class="hide-menu">OJT MANAGEMENT</span>
          </li>
          <?php if ($can_view_manage_students): ?>
          <li class="sidebar-item">
            <a class="sidebar-link primary-hover-bg" href="ojt_students_local.php" aria-expanded="false">
              <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="fs-6"></iconify-icon>
              <span class="hide-menu">Managed Students</span>
            </a>
          </li>
          <?php endif; ?>
          <?php if ($can_view_student_attendance): ?>
          <li class="sidebar-item">
            <a class="sidebar-link primary-hover-bg" href="ojt_attendance.php" aria-expanded="false">
              <iconify-icon icon="solar:calendar-date-line-duotone" class="fs-6"></iconify-icon>
              <span class="hide-menu">Student Attendance</span>
            </a>
          </li>
          <?php endif; ?>
          <?php if ($can_view_hours_summary): ?>
          <li class="sidebar-item">
            <a class="sidebar-link primary-hover-bg" href="ojt_summary.php" aria-expanded="false">
              <iconify-icon icon="solar:chart-line-duotone" class="fs-6"></iconify-icon>
              <span class="hide-menu">Hours Summary</span>
            </a>
          </li>
          <?php endif; ?>
           <?php if ($can_view_ojt_student_registry): ?>
                <li class="sidebar-item"><a href="ojt_students_all.php" class="sidebar-link">
                  <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="fs-6"></iconify-icon>
                  <span class="hide-menu">All OJT Students </span></a></li>
                <?php endif; ?>
           <?php endif; ?>
           
            <?php if ($can_view_general_settings || $can_view_security_settings || $can_view_activity_logs): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">🔐 SYSTEM SETTINGS</span>
            </li>
            <?php if ($can_view_general_settings): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="general-settings.php" aria-expanded="false">
                <iconify-icon icon="solar:settings-minimalistic-line-duotone"></iconify-icon>
                <span class="hide-menu">General Settings</span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_security_settings): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="security-settings.php" aria-expanded="false">
                <iconify-icon icon="solar:shield-check-line-duotone"></iconify-icon>
                <span class="hide-menu">Security Settings</span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_activity_logs): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="activity-logs.php" aria-expanded="false">
                <iconify-icon icon="solar:history-line-duotone"></iconify-icon>
                <span class="hide-menu"> Activity Logs <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($can_view_pricing_plans || $can_view_vote_purchases || $can_view_transactions): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">💳 BILLING</span>
            </li>
            <?php if ($can_view_pricing_plans): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="pricing-plans.php" aria-expanded="false">
                <iconify-icon icon="solar:card-line-duotone"></iconify-icon>
                <span class="hide-menu">Pricing Plans</span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_vote_purchases): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="vote-purchases.php" aria-expanded="false">
                <iconify-icon icon="solar:wallet-line-duotone"></iconify-icon>
                <span class="hide-menu">Vote Purchases</span>
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_transactions): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="transactions.php" aria-expanded="false">
                <iconify-icon icon="solar:banknote-line-duotone"></iconify-icon>
                <span class="hide-menu">Transactions <span class="badge bg-success">Done</span></span>
              </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <li class="nav-small-cap">
              <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">🚪 ACCOUNT</span>
            </li>
            <?php if ($can_view_account): ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="profile.php" aria-expanded="false">
                <iconify-icon icon="solar:user-circle-line-duotone"></iconify-icon>
                <span class="hide-menu">My Account</span>
              </a>
            </li>
            <?php endif; ?>
            <li class="sidebar-item">
              <a class="sidebar-link primary-hover-bg" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#logoutModal" aria-expanded="false">
                <iconify-icon icon="solar:logout-line-duotone"></iconify-icon>
                <span class="hide-menu">Logout</span>
              </a>
            </li>
          </ul>
        </nav>
        <!-- End Sidebar navigation -->
      </div>
      <!-- End Sidebar scroll-->
    </aside>