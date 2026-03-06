<script setup>
import { ref } from "vue"
import { Link, usePage } from "@inertiajs/vue3"
import ApplicationLogo from "@/Components/ApplicationLogo.vue"

const collapsed = ref(false)
const mobileOpen = ref(false)

const page = usePage()

const menu = [
    { name: "Dashboard", route: "/dashboard", icon: "🏠" },
    { name: "Categorias", route: "/categories", icon: "📂" },
    { name: "Formas de Pagamento", route: "/payments", icon: "💳" },
    { name: "Transações", route: "/transactions", icon: "💰" },
]

const isActive = (route) => page.url.startsWith(route)
</script>

<template>
<div class="flex min-h-screen bg-gray-950 text-gray-200">

    <!-- OVERLAY MOBILE -->
    <div
        v-if="mobileOpen"
        @click="mobileOpen = false"
        class="fixed inset-0 bg-black/50 z-40 lg:hidden"
    ></div>

    <!-- SIDEBAR -->
    <aside
        :class="[
            'fixed lg:static z-50 h-screen flex flex-col justify-between bg-gray-900 border-r border-gray-800 transition-all duration-300',
            collapsed ? 'w-20' : 'w-80',
            mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
    >

        <div>

            <!-- HEADER -->
            <div class="flex items-center justify-between px-4 py-4 border-b border-gray-800">

                <span v-if="!collapsed" class="font-bold text-lg">
                    <Link :href="route('dashboard')">
                        <ApplicationLogo
                            class="block h-12 w-auto fill-current text-gray-800"
                        />
                    </Link>
                </span>
                <span v-if="!collapsed" class="font-bold text-lg">
                    Controle de Gastos
                </span>

                <button
                    @click="collapsed = !collapsed"
                    class="hidden lg:block text-gray-400 hover:text-white"
                >
                    ☰
                </button>

                <button
                    @click="mobileOpen = false"
                    class="lg:hidden text-gray-400 hover:text-white"
                >
                    ✕
                </button>

            </div>

            <!-- MENU -->
            <nav class="mt-4 space-y-1">

                <Link
                    v-for="item in menu"
                    :key="item.route"
                    :href="item.route"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg mx-2 transition"
                    :class="isActive(item.route)
                        ? 'bg-gray-800 text-white'
                        : 'text-gray-400 hover:bg-gray-800 hover:text-white'"
                    @click="mobileOpen = false"
                >

                    <span class="text-lg">
                        {{ item.icon }}
                    </span>

                    <span v-if="!collapsed">
                        {{ item.name }}
                    </span>

                </Link>

            </nav>

        </div>

        <!-- USER MENU (DESKTOP) -->
        <div class="border-t border-gray-800 p-4 hidden lg:block">

            <div v-if="!collapsed" class="mb-2 text-sm">
                {{ $page.props.auth.user.name }}
            </div>

            <Link
                :href="route('profile.edit')"
                class="block text-sm text-gray-400 hover:text-white mb-2"
            >
                Perfil
            </Link>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="text-sm text-red-400 hover:text-red-300"
            >
                Sair
            </Link>

        </div>

    </aside>

    <!-- CONTEÚDO -->
    <div class="flex-1 flex flex-col">

        <!-- TOPBAR -->
        <header class="flex items-center justify-between px-6 py-4 border-b border-gray-800 bg-gray-900">

            <!-- BOTÃO MOBILE -->
            <button
                @click="mobileOpen = true"
                class="lg:hidden text-gray-400 hover:text-white"
            >
                ☰
            </button>

            <h1 class="font-semibold">
                Dashboard
            </h1>

            <!-- USER MOBILE -->
            <div class="flex items-center gap-4 lg:hidden">

                <Link
                    :href="route('profile.edit')"
                    class="text-gray-400 hover:text-white text-sm"
                >
                    Perfil
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-red-400 hover:text-red-300 text-sm"
                >
                    Sair
                </Link>

            </div>

        </header>

        <!-- PAGE CONTENT -->
        <main class="flex-1">
            <slot />
        </main>

    </div>

</div>
</template>
