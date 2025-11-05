import { getCurrentInstance } from 'vue';

export function useToast() {
  const instance = getCurrentInstance();
  const toast = instance?.appContext?.config?.globalProperties?.$toast;

  return {
    toast: {
      success: (title, description, duration = 3000) => {
        toast?.addToast({ type: 'success', title, description, duration });
      },
      error: (title, description, duration = 5000) => {
        toast?.addToast({ type: 'error', title, description, duration });
      },
      warning: (title, description, duration = 4000) => {
        toast?.addToast({ type: 'warning', title, description, duration });
      },
      info: (title, description, duration = 3000) => {
        toast?.addToast({ type: 'info', title, description, duration });
      },
    },
  };
}

