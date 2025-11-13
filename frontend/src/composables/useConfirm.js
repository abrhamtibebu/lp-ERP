import { ref } from 'vue';

const confirmDialog = ref({
  isOpen: false,
  title: 'Confirm Action',
  message: '',
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  type: 'danger', // 'danger', 'warning', 'info'
  resolve: null,
  reject: null,
});

export function useConfirm() {
  const showConfirm = (options) => {
    return new Promise((resolve) => {
      confirmDialog.value = {
        isOpen: true,
        title: options.title || 'Confirm Action',
        message: options.message || options.text || '',
        confirmText: options.confirmText || 'Confirm',
        cancelText: options.cancelText || 'Cancel',
        type: options.type || 'danger',
        resolve,
        reject: null,
      };
    });
  };

  const confirm = (messageOrOptions, title = 'Confirm Action', type = 'danger') => {
    // Handle both object and positional arguments
    if (typeof messageOrOptions === 'object' && messageOrOptions !== null) {
      // Called with object: confirm({ title: '...', message: '...', type: '...' })
      return showConfirm(messageOrOptions);
    } else {
      // Called with positional args: confirm('message', 'title', 'type')
      return showConfirm({
        title,
        message: messageOrOptions,
        type,
      });
    }
  };

  const handleConfirm = () => {
    if (confirmDialog.value.resolve) {
      confirmDialog.value.resolve(true);
    }
    confirmDialog.value.isOpen = false;
    confirmDialog.value.resolve = null;
    confirmDialog.value.reject = null;
  };

  const handleCancel = () => {
    if (confirmDialog.value.resolve) {
      // Resolve with false instead of rejecting to avoid unhandled promise rejections
      confirmDialog.value.resolve(false);
    }
    confirmDialog.value.isOpen = false;
    confirmDialog.value.resolve = null;
    confirmDialog.value.reject = null;
  };

  return {
    confirmDialog,
    confirm,
    showConfirm,
    handleConfirm,
    handleCancel,
  };
}

