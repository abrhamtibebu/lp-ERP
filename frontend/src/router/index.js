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
        path: 'inventory',
        redirect: '/inventory/leather'
      },
      {
        path: 'inventory/leather',
        name: 'LeatherInventory',
        component: () => import('../views/Inventory/Leather.vue')
      },
      {
        path: 'inventory/accessories',
        name: 'AccessoriesInventory',
        component: () => import('../views/Inventory/Accessories.vue')
      },
      {
        path: 'products',
        name: 'Products',
        component: () => import('../views/Products/Index.vue')
      },
      {
        path: 'production',
        redirect: '/production/orders'
      },
      {
        path: 'production/orders',
        name: 'Orders',
        component: () => import('../views/Production/Orders.vue')
      },
      {
        path: 'production/batches',
        name: 'Batches',
        component: () => import('../views/Production/Batches.vue')
      },
      {
        path: 'production/batches/:id',
        name: 'BatchDetail',
        component: () => import('../views/Production/BatchDetail.vue')
      },
      {
        path: 'finance',
        redirect: '/finance/product-costs'
      },
      {
        path: 'finance/product-costs',
        name: 'ProductCosts',
        component: () => import('../views/Finance/ProductCosts.vue'),
        meta: { permission: 'finance.product_cost' }
      },
      {
        path: 'finance/expenses',
        name: 'Expenses',
        component: () => import('../views/Finance/Expenses.vue')
      },
      {
        path: 'finance/revenues',
        name: 'Revenues',
        component: () => import('../views/Finance/Revenues.vue')
      },
      {
        path: 'commercial-invoices',
        name: 'CommercialInvoices',
        component: () => import('../views/Logistics/Invoices.vue')
      },
      {
        path: 'inventory/finished-goods',
        name: 'FinishedGoods',
        component: () => import('../views/Inventory/FinishedGoods.vue')
      },
      {
        path: 'finance/miscellaneous-costs',
        name: 'MiscellaneousCosts',
        component: () => import('../views/Finance/MiscellaneousCosts.vue')
      },
      {
        path: 'admin/role-assignment',
        name: 'RoleAssignment',
        component: () => import('../views/Admin/RoleAssignment.vue'),
        meta: { permission: 'employees.create' }
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

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  // If we have a token but no user, check authentication first (refresh scenario)
  if (authStore.token && !authStore.user && to.path !== '/login') {
    const authValid = await authStore.checkAuth();
    if (!authValid) {
      // If auth check fails, redirect to login
      // checkAuth already clears the token and user
      next('/login');
      return;
    }
  }
  
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

