/**
 * AuroraTabs - Alert Utilities
 * Reusable wrapper around SweetAlert2 with consistent styling and branding.
 */

const AlertUtils = {
  // Theme Colors
  colors: {
    primary: '#4285F4',
    success: '#34A853',
    danger: '#EA4335',
    warning: '#FBBC05',
    info: '#4285F4'
  },

  /**
   * Show a temporary Toast notification
   * @param {string} icon - 'success', 'error', 'warning', 'info'
   * @param {string} title - The message to display
   */
  showToast: (icon, title) => {
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
      },
      customClass: {
        popup: 'colored-toast'
      }
    });

    return Toast.fire({
      icon: icon,
      title: title
    });
  },

  /**
   * Show a standard Alert modal
   * @param {string} icon - 'success', 'error', 'warning', 'info', 'question'
   * @param {string} title - Title of the alert
   * @param {string} text - Description text
   */
  showAlert: (icon, title, text) => {
    return Swal.fire({
      icon: icon,
      title: title,
      text: text,
      confirmButtonColor: AlertUtils.colors.primary,
      confirmButtonText: 'Okay, Got it!',
      buttonsStyling: false,
      customClass: {
        confirmButton: 'btn btn-primary px-4 py-2 rounded-3',
        popup: 'rounded-4 shadow-lg border-0'
      },
      showClass: {
        popup: 'animate__animated animate__fadeInUp animate__faster'
      },
      hideClass: {
        popup: 'animate__animated animate__fadeOutDown animate__faster'
      }
    });
  },

  /**
   * Show a Confirmation modal
   * @param {string} title - Title
   * @param {string} text - Question/Warning text
   * @param {string} confirmText - Text for confirm button
   * @param {function} onConfirm - Callback if confirmed
   */
  showConfirm: (title, text, confirmText, onConfirm) => {
    Swal.fire({
      title: title,
      text: text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: AlertUtils.colors.danger,
      cancelButtonColor: '#cbd5e1',
      confirmButtonText: confirmText || 'Yes, proceed',
      cancelButtonText: 'Cancel',
      buttonsStyling: false,
      customClass: {
        confirmButton: 'btn btn-danger px-4 py-2 rounded-3 me-2',
        cancelButton: 'btn btn-outline-secondary px-4 py-2 rounded-3',
        popup: 'rounded-4 shadow-lg border-0'
      }
    }).then((result) => {
      if (result.isConfirmed && typeof onConfirm === 'function') {
        onConfirm();
      }
    });
  }
};
