import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useTenantStore = defineStore('tenant', () => {
  const currentTenant = ref(null);
  const tenants = ref([]);

  function setTenant(tenant) {
    currentTenant.value = tenant;
  }

  function setTenants(tenantList) {
    tenants.value = tenantList;
  }

  return {
    currentTenant,
    tenants,
    setTenant,
    setTenants
  };
});

