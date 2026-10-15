$(function () {
  "use strict";

  // Auto-close sidebar on mobile when a link is clicked
  $(".sidebar-link").on("click", function (e) {
    if (window.innerWidth < 1199) {
      // If this is a dropdown toggle (has-arrow), do NOT close the sidebar
      if ($(this).hasClass("has-arrow")) {
          return; 
      }

      // Check if sidebar is currently open
      if ($("#main-wrapper").hasClass("show-sidebar")) {
          $(".sidebartoggler").trigger("click");
      }
    }
  });

  // --- AuroraLoader Reusable Utility ---
  window.AuroraLoader = {
    // Show a glassmorphic overlay fixed over the entire viewport
    showOverlay: function(selector, message = 'Loading...') {
      if ($('#aurora-global-overlay').length) return;
      
      const overlay = $(`
        <div id="aurora-global-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;z-index:99999;background:rgba(255,255,255,0.75);backdrop-filter:blur(6px);display:flex;flex-direction:column;align-items:center;justify-content:center;">
          <div class="aurora-spinner" style="margin-bottom:16px;"></div>
          <div class="fw-bold text-primary fs-5">${message}</div>
        </div>
      `);
      $('body').append(overlay);
      setTimeout(() => overlay.css('opacity', 1), 10);
    },

    hideOverlay: function(selector) {
      const overlay = $('#aurora-global-overlay');
      overlay.fadeOut(300, function() { overlay.remove(); });
    },

    // Get HTML for a skeleton candidate card
    getSkeletonCard: function() {
      return `
        <div class="col-xl-4 col-md-6 mb-4">
          <div class="card candidate-card border-0 shadow-sm h-100">
            <div class="skeleton skeleton-img h-100" style="min-height: 350px;"></div>
            <div class="card-body p-4">
               <div class="skeleton skeleton-title"></div>
               <div class="skeleton skeleton-text"></div>
               <div class="skeleton skeleton-text" style="width: 80%"></div>
               <div class="mt-4">
                  <div class="skeleton skeleton-title" style="height: 3rem; width: 100%; border-radius: 12px;"></div>
               </div>
            </div>
          </div>
        </div>
      `;
    }
  };

  // Reusable Global Header Profile Loader
  window.refreshHeaderProfile = function() {
      if ($('#headerProfileImage').length > 0 && window.APP_CONFIG) {
          $.post(window.APP_CONFIG.profile_handler, { action: 'get_profile' }, function(response) {
              if (response.status === 'success' && response.data.user.avatar_url) {
                  // Prepend admin base to Ensure path is absolute from web root
                  const avatarUrl = window.APP_CONFIG.admin_base + response.data.user.avatar_url;
                  $('#headerProfileImage').attr('src', avatarUrl);
                  
                  // If we're on the profile page, also update those elements
                  if ($('#profileImageDisplay').length > 0) {
                      $('#profileImageDisplay').attr('src', avatarUrl);
                  }
                  if ($('#avatarPreview').length > 0) {
                      $('#avatarPreview').attr('src', avatarUrl);
                  }
              }
          }, 'json');
      }
  };

  // Initial load
  refreshHeaderProfile();
});

