<template>
    <div class="min-h-screen bg-background">
        <AppHeader @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />
        <!-- Mobile sidebar overlay -->
        <div
            v-if="isMobile && isSidebarOpen"
            class="fixed inset-0 bg-black/50 z-20 lg:hidden"
            @click="isSidebarOpen = false"
        ></div>
        <div class="flex">
            <AppSidebar
                :is-open="isSidebarOpen"
                :is-mobile="isMobile"
                :is-collapsed="isSidebarCollapsed"
            />
            <main
                :class="[
                    'flex-1 transition-all duration-300',
                    isSidebarCollapsed && !isMobile ? 'lg:ml-16' : 'lg:ml-64',
                    'ml-0', // No margin on mobile
                ]"
            >
                <div class="min-h-[calc(100vh-3.5rem)]">
                    <AppBreadcrumb />
                    <div class="p-4 sm:p-6">
                        <router-view />
                    </div>
                </div>
            </main>
        </div>
        <Toast ref="toastRef" />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, provide } from "vue";
import { useWindowSize } from "@vueuse/core";
import AppHeader from "@/components/layout/AppHeader.vue";
import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppBreadcrumb from "@/components/layout/AppBreadcrumb.vue";
import Toast from "@/components/ui/Toast.vue";

const isSidebarOpen = ref(true);
const isSidebarCollapsed = ref(false);
const toastRef = ref(null);

const { width } = useWindowSize();
const isMobile = ref(false);

const updateMobile = () => {
    isMobile.value = width.value < 1024;
    if (width.value >= 1024) {
        isSidebarOpen.value = true;
    } else {
        isSidebarOpen.value = false;
    }
};

onMounted(() => {
    updateMobile();
    window.addEventListener("resize", updateMobile);

    // Provide toast instance globally
    if (toastRef.value) {
        provide("toast", toastRef.value);
    }
});

onUnmounted(() => {
    window.removeEventListener("resize", updateMobile);
});
</script>
