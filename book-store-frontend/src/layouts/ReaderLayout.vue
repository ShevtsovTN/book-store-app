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

<style scoped></style>
