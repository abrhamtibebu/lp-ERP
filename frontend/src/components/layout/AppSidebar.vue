<template>
    <aside
        :class="[
            'fixed left-0 top-14 z-30 h-[calc(100vh-3.5rem)] w-64 border-r bg-background transition-transform duration-300',
            isCollapsed
                ? '-translate-x-full lg:translate-x-0 lg:w-16'
                : 'translate-x-0',
            isMobile && !isOpen ? '-translate-x-full' : 'translate-x-0',
        ]"
    >
        <nav class="flex h-full flex-col p-4 space-y-2">
            <!-- Main Navigation -->
            <div class="space-y-1">
                <router-link
                    v-for="item in menuItems"
                    :key="item.path"
                    :to="item.path"
                    @click="handleNavigation(item.path, $event)"
                    class="flex items-center space-x-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground cursor-pointer"
                    :class="{
                        'bg-accent text-accent-foreground': isActiveRoute(
                            item.path
                        ),
                    }"
                >
                    <component :is="item.icon" class="h-5 w-5 flex-shrink-0" />
                    <span v-if="!isCollapsed" class="flex-1">{{
                        item.name
                    }}</span>
                </router-link>
            </div>

            <!-- Separator -->
            <Separator class="my-4" />

            <!-- Module Groups -->
            <div
                v-for="group in menuGroups"
                :key="group.name"
                class="space-y-1"
            >
                <div
                    v-if="!isCollapsed && group.items.length > 0"
                    class="px-3 py-2 text-xs font-semibold text-muted-foreground uppercase"
                >
                    {{ group.name }}
                </div>
                <router-link
                    v-for="item in group.items"
                    :key="item.path"
                    :to="item.path"
                    @click="handleNavigation(item.path, $event)"
                    class="flex items-center space-x-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground cursor-pointer"
                    :class="{
                        'bg-accent text-accent-foreground': isActiveRoute(
                            item.path
                        ),
                    }"
                >
                    <component :is="item.icon" class="h-5 w-5 flex-shrink-0" />
                    <span v-if="!isCollapsed" class="flex-1">{{
                        item.name
                    }}</span>
                </router-link>
            </div>
        </nav>
    </aside>
</template>

<script setup>
import { computed, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import {
    LayoutDashboard,
    Users,
    Truck,
    Building2,
    Package,
    Box,
    ShoppingCart,
    ClipboardList,
    DollarSign,
    FileText,
    BarChart3,
    Folder,
    Shield,
} from "lucide-vue-next";
import Separator from "../ui/Separator.vue";

const props = defineProps({
    isCollapsed: {
        type: Boolean,
        default: false,
    },
    isOpen: {
        type: Boolean,
        default: true,
    },
    isMobile: {
        type: Boolean,
        default: false,
    },
});

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// Handle navigation explicitly to ensure it works from dashboard
// This function ensures navigation happens even if router-link has issues
const handleNavigation = (path, event) => {
    const targetPath = path.startsWith("/") ? path : `/${path}`;

    // Immediately trigger navigation using router.push
    // This ensures navigation happens regardless of router-link behavior
    nextTick(() => {
        router.push(targetPath).catch((err) => {
            // Ignore expected navigation errors
            if (
                err.name === "NavigationDuplicated" ||
                err.name === "NavigationCancelled"
            ) {
                return;
            }
            // Log unexpected errors for debugging
            console.warn("Navigation error from sidebar:", err);
        });
    });
};

// Permission mapping for each menu item
const menuItemPermissions = {
    "/": null, // Dashboard - no permission required
    "/employees": "employees.view",
    "/fixed-assets": "inventory.manage",
    "/suppliers": "inventory.manage",
    "/inventory/leather": "inventory.manage",
    "/inventory/accessories": "inventory.manage",
    "/products": "inventory.manage",
    "/inventory/finished-goods": "inventory.manage",
    "/production/orders": "production.manage",
    "/production/batches": "production.manage",
    "/finance/product-costs": "finance.product_cost",
    "/finance/expenses": "finance.expenses",
    "/finance/revenues": "finance.revenue",
    "/finance/miscellaneous-costs": "finance.expenses",
    "/commercial-invoices": "logistics.invoices",
    "/admin/role-assignment": "employees.create",
    "/reports": "reports.view",
};

const hasPermissionForPath = (path) => {
    const permission = menuItemPermissions[path];
    if (permission === null || permission === undefined) {
        return true; // No permission required (e.g., Dashboard)
    }
    return authStore.hasPermission(permission);
};

// Helper function to check if a route is active
const isActiveRoute = (path) => {
    if (path === "/") {
        return route.path === "/" || route.path === "";
    }
    return route.path === path || route.path.startsWith(path + "/");
};

const menuItems = computed(() => {
    const items = [{ name: "Dashboard", path: "/", icon: LayoutDashboard }];
    return items.filter((item) => hasPermissionForPath(item.path));
});

const menuGroups = computed(() => {
    const allGroups = [
        {
            name: "Registration",
            items: [
                {
                    name: "Employee Registration",
                    path: "/employees",
                    icon: Users,
                    permission: "employees.view",
                },
                {
                    name: "Fixed Asset Registration",
                    path: "/fixed-assets",
                    icon: Building2,
                    permission: "inventory.manage",
                },
            ],
        },
        {
            name: "Core",
            items: [
                {
                    name: "Suppliers",
                    path: "/suppliers",
                    icon: Truck,
                    permission: "inventory.manage",
                },
            ],
        },
        {
            name: "Inventory",
            items: [
                {
                    name: "Leather",
                    path: "/inventory/leather",
                    icon: Package,
                    permission: "inventory.manage",
                },
                {
                    name: "Accessories",
                    path: "/inventory/accessories",
                    icon: Box,
                    permission: "inventory.manage",
                },
                {
                    name: "Products",
                    path: "/products",
                    icon: ShoppingCart,
                    permission: "inventory.manage",
                },
                {
                    name: "Finished Goods",
                    path: "/inventory/finished-goods",
                    icon: Box,
                    permission: "inventory.manage",
                },
            ],
        },
        {
            name: "Production",
            items: [
                {
                    name: "Orders",
                    path: "/production/orders",
                    icon: ClipboardList,
                    permission: "production.manage",
                },
                {
                    name: "Batches",
                    path: "/production/batches",
                    icon: Folder,
                    permission: "production.manage",
                },
            ],
        },
        {
            name: "Finance",
            items: [
                {
                    name: "Product Costs",
                    path: "/finance/product-costs",
                    icon: DollarSign,
                    permission: "finance.product_cost",
                },
                {
                    name: "Expenses",
                    path: "/finance/expenses",
                    icon: FileText,
                    permission: "finance.expenses",
                },
                {
                    name: "Revenues",
                    path: "/finance/revenues",
                    icon: DollarSign,
                    permission: "finance.revenue",
                },
                {
                    name: "Miscellaneous Costs",
                    path: "/finance/miscellaneous-costs",
                    icon: FileText,
                    permission: "finance.expenses",
                },
            ],
        },
        {
            name: "Admin",
            items: [
                {
                    name: "Role Assignment",
                    path: "/admin/role-assignment",
                    icon: Shield,
                    permission: "employees.create",
                },
            ],
        },
        {
            name: "Reports",
            items: [
                {
                    name: "Analytics",
                    path: "/reports",
                    icon: BarChart3,
                    permission: "reports.view",
                },
            ],
        },
    ];

    // Filter items within each group and remove groups with no visible items
    return allGroups
        .map((group) => ({
            ...group,
            items: group.items.filter((item) =>
                hasPermissionForPath(item.path)
            ),
        }))
        .filter((group) => group.items.length > 0);
});
</script>
