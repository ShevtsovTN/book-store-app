<script setup lang="ts">
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'
import { onMounted } from 'vue'

const auth = useAuthStore()
const notifications = useNotificationsStore()
const router = useRouter()

onMounted(() => notifications.fetchUnreadCount())

async function handleLogout(): Promise<void> {
  await auth.logout()
  await router.push({ name: 'login' })
}
</script>

<template>
  <div class="reader-layout">
    <aside class="sidebar">
      <RouterLink :to="{ name: 'home' }" class="sidebar__brand">BookStore</RouterLink>

      <nav class="sidebar__nav">
        <RouterLink :to="{ name: 'reading-list' }">Reading List</RouterLink>
        <RouterLink :to="{ name: 'reading-history' }">History</RouterLink>
        <RouterLink :to="{ name: 'cart' }">Cart</RouterLink>
        <RouterLink :to="{ name: 'notifications' }">
          Notifications
          <span v-if="notifications.hasUnread" class="sidebar__badge">
            {{ notifications.unreadCount }}
          </span>
        </RouterLink>
      </nav>

      <div class="sidebar__footer">
        <span class="sidebar__user">{{ auth.user?.name }}</span>
        <button class="sidebar__logout" @click="handleLogout">Logout</button>
      </div>
    </aside>

    <main class="reader-layout__main">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.reader-layout {
  min-height: 100vh;
  display: flex;
}

.sidebar {
  width: 240px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  padding: 1.5rem 1rem;
  background: #1e1b4b;
  color: #e0e7ff;
  gap: 1.5rem;
}

.sidebar__brand {
  font-size: 1.2rem;
  font-weight: 700;
  color: #fff;
  text-decoration: none;
}

.sidebar__nav {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.sidebar__nav a {
  position: relative;
  display: flex;
  align-items: center;
  padding: 0.6rem 0.75rem;
  border-radius: 8px;
  color: #c7d2fe;
  text-decoration: none;
  transition: background 0.15s;
}

.sidebar__nav a:hover,
.sidebar__nav a.router-link-active {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.sidebar__badge {
  margin-left: auto;
  background: #ef4444;
  color: #fff;
  font-size: 0.7rem;
  min-width: 18px;
  height: 18px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
}

.sidebar__footer {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.sidebar__user {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.sidebar__logout {
  background: none;
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: inherit;
  border-radius: 6px;
  padding: 0.25rem 0.5rem;
  cursor: pointer;
  font-size: 0.8rem;
}

.reader-layout__main {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
}
</style>
