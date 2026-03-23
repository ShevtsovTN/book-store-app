<script setup lang="ts">
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { computed } from 'vue'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

async function handleLogout(): Promise<void> {
  await auth.adminLogout()
  await router.push({ name: 'admin-login' })
}
const isActive = (name: string) => computed(() => route.name === name)
</script>

<template>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <span class="admin-sidebar__brand">Admin Panel</span>

      <nav class="admin-sidebar__nav">
        <RouterLink
          :to="{ name: 'admin-dashboard' }"
          :class="{ 'router-link-active': isActive('admin-dashboard').value }"
          >Dashboard</RouterLink
        >
        <RouterLink
          :to="{ name: 'admin-books' }"
          :class="{ 'router-link-active': isActive('admin-books').value }"
          >Books</RouterLink
        >
      </nav>

      <div class="admin-sidebar__footer">
        <span>{{ auth.user?.name }}</span>
        <button @click="handleLogout">Logout</button>
      </div>
    </aside>

    <main class="admin-layout__main">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.admin-layout {
  min-height: 100vh;
  display: flex;
}

.admin-sidebar {
  width: 220px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  padding: 1.5rem 1rem;
  background: #111827;
  color: #d1d5db;
  gap: 1.5rem;
}

.admin-sidebar__brand {
  font-size: 1rem;
  font-weight: 700;
  color: #fff;
}

.admin-sidebar__nav {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.admin-sidebar__nav a {
  display: block;
  padding: 0.6rem 0.75rem;
  border-radius: 8px;
  color: #9ca3af;
  text-decoration: none;
}

.admin-sidebar__nav a:hover,
.admin-sidebar__nav a.router-link-active {
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
}

.admin-sidebar__footer {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
}

.admin-sidebar__footer button {
  background: none;
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: inherit;
  border-radius: 6px;
  padding: 0.25rem 0.5rem;
  cursor: pointer;
}

.admin-layout__main {
  flex: 1;
  padding: 2rem;
  background: #f9fafb;
}
</style>
