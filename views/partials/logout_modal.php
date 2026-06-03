<!-- Generic Logout Modal (Aether-Glass Design) -->
<style>
    #globalLogoutModal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 99999;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(8px);
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    #globalLogoutModal.show {
        display: flex;
        opacity: 1;
    }
    .logout-modal-content {
        background: var(--md-sys-color-surface-container, #ffffff);
        color: var(--md-sys-color-on-surface, #1C1B1F);
        padding: 32px;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        max-width: 400px;
        width: 90%;
        text-align: center;
        transform: scale(0.95);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    #globalLogoutModal.show .logout-modal-content {
        transform: scale(1);
    }
    .logout-modal-icon {
        font-size: 48px;
        color: var(--md-sys-color-error, #B3261E);
        margin-bottom: 16px;
    }
    .logout-modal-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .logout-modal-body {
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        color: var(--md-sys-color-on-surface-variant, #49454F);
        margin-bottom: 24px;
    }
    .logout-modal-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
    }
    .logout-btn-cancel {
        padding: 12px 24px;
        border-radius: 999px;
        border: 1px solid var(--md-sys-color-outline, #79747E);
        background: transparent;
        color: var(--md-sys-color-on-surface, #1C1B1F);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .logout-btn-cancel:hover {
        background: var(--md-sys-color-surface-container-highest, #E6E0E9);
    }
    .logout-btn-confirm {
        padding: 12px 24px;
        border-radius: 999px;
        border: none;
        background: var(--md-sys-color-error, #B3261E);
        color: #ffffff;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .logout-btn-confirm:hover {
        background: var(--md-sys-color-error-container, #F9DEDC);
        color: var(--md-sys-color-on-error-container, #410E0B);
    }

    /* Dark Mode Overrides if --md-sys-color variables are not defined */
    @media (prefers-color-scheme: dark) {
        .logout-modal-content {
            background: var(--md-sys-color-surface-container, #2B2930);
            color: var(--md-sys-color-on-surface, #E6E1E5);
        }
        .logout-modal-body {
            color: var(--md-sys-color-on-surface-variant, #CAC4D0);
        }
        .logout-btn-cancel {
            color: var(--md-sys-color-on-surface, #E6E1E5);
        }
        .logout-btn-cancel:hover {
            background: var(--md-sys-color-surface-container-highest, #36343B);
        }
    }
</style>

<div id="globalLogoutModal">
    <div class="logout-modal-content">
        <i class="fa-solid fa-right-from-bracket logout-modal-icon"></i>
        <div class="logout-modal-title">Ready to Leave?</div>
        <div class="logout-modal-body">Are you sure you want to log out of your account? Your status will be marked as offline.</div>
        <div class="logout-modal-actions">
            <button class="logout-btn-cancel" onclick="closeLogoutModal()">Cancel</button>
            <a href="<?= url('?route=logout') ?>" class="logout-btn-confirm">Log Out</a>
        </div>
    </div>
</div>

<script>
    function openLogoutModal(e) {
        if(e) e.preventDefault();
        const modal = document.getElementById('globalLogoutModal');
        modal.style.display = 'flex';
        // Small delay to allow display:flex to apply before setting opacity for transition
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }

    function closeLogoutModal() {
        const modal = document.getElementById('globalLogoutModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300); // Wait for transition to finish
    }

    // Attach to all links with href pointing to logout
    document.addEventListener('DOMContentLoaded', () => {
        const logoutLinks = document.querySelectorAll('a[href*="?route=logout"]');
        logoutLinks.forEach(link => {
            // Check if it's actually the logout button inside the modal to prevent overriding it
            if(!link.classList.contains('logout-btn-confirm')) {
                link.addEventListener('click', openLogoutModal);
            }
        });
    });
</script>
