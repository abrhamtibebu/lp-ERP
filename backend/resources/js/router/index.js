import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/',
    component: () => import('../layouts/DashboardLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue')
      },
      {
        path: 'employees',
        name: 'Employees',
        component: () => import('../views/Employees/Index.vue'),
        meta: { permission: 'employees.view' }
      },
      {
        path: 'suppliers',
        name: 'Suppliers',
        component: () => import('../views/Suppliers/Index.vue')
      },
      {
        path: 'fixed-assets',
        name: 'FixedAssets',
        component: () => import('../views/FixedAssets/Index.vue')
      },
      {
        path: 'leather-inventory',
        name: 'LeatherInventory',
        component: () => import('../views/Inventory/Leather.vue')
      },
      {
        path: 'accessories-inventory',
        name: 'AccessoriesInventory',
        component: () => import('../views/Inventory/Accessories.vue')
      },
      {
        path: 'products',
        name: 'Products',
        component: () => import('../views/Products/Index.vue')
      },
      {
        path: 'orders',
        name: 'Orders',
        component: () => import('../views/Production/Orders.vue')
      },
      {
        path: 'batches',
        name: 'Batches',
        component: () => import('../views/Production/Batches.vue')
      },
      {
        path: 'batches/:id',
        name: 'BatchDetail',
        component: () => import('../views/Production/BatchDetail.vue')
      },
      {
        path: 'product-costs',
        name: 'ProductCosts',
        component: () => import('../views/Finance/ProductCosts.vue'),
        meta: { permission: 'finance.product_cost' }
      },
      {
        path: 'expenses',
        name: 'Expenses',
        component: () => import('../views/Finance/Expenses.vue')
      },
      {
        path: 'revenues',
        name: 'Revenues',
        component: () => import('../views/Finance/Revenues.vue')
      },
      {
        path: 'commercial-invoices',
        name: 'CommercialInvoices',
        component: () => import('../views/Logistics/Invoices.vue')
      },
      {
        path: 'reports',
        name: 'Reports',
        component: () => import('../views/Reports/Index.vue')
      }
    ]
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login');
  } else if (to.path === '/login' && authStore.isAuthenticated) {
    next('/');
  } else {
    // Check permissions if specified
    if (to.meta.permission && !authStore.hasPermission(to.meta.permission)) {
      next('/');
    } else {
      next();
    }
  }
});

export default router;

