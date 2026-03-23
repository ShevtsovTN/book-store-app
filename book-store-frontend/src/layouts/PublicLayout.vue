<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'

const auth = useAuthStore()
const cart = useCartStore()
</script>

<template>
  <div class="public-layout">
    <header class="header">
      <nav class="nav">
        <RouterLink :to="{ name: 'home' }" class="nav__brand">BookStore</RouterLink>

        <div class="nav__links">
          <RouterLink :to="{ name: 'catalog' }">Catalog</RouterLink>
          <RouterLink :to="{ name: 'search' }">Search</RouterLink>
        </div>

        <div class="nav__actions">
          <template v-if="auth.isAuthenticated">
            <RouterLink :to="{ name: 'cart' }" class="nav__cart">
              Cart
              <span v-if="cart.itemsCount > 0" class="nav__badge">{{ cart.itemsCount }}</span>
            </RouterLink>
            <RouterLink :to="{ name: 'reading-list' }">My Books</RouterLink>
          </template>
          <template v-else>
            <RouterLink :to="{ name: 'login' }">Login</RouterLink>
            <RouterLink :to="{ name: 'register' }">Register</RouterLink>
          </template>
        </div>
      </nav>
    </header>

    <main class="main">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.public-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.header {
  position: sticky;
  top: 0;
  z-index: 100;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
}

.nav {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
  height: 64px;
  display: flex;
  align-items: center;
  gap: 2rem;
}

.nav__brand {
  font-size: 1.25rem;
  font-weight: 700;
  text-decoration: none;
  color: inherit;
}

.nav__links,
.nav__actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.nav__links {
  flex: 1;
}

.nav__cart {
  position: relative;
}

.nav__badge {
  position: absolute;
  top: -8px;
  right: -10px;
  min-width: 18px;
  height: 18px;
  padding: 0 4px;
  border-radius: 9px;
  background: #ef4444;
  color: #fff;
  font-size: 0.7rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.main {
  flex: 1;
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
  padding: 2rem 1rem;
}
</style>
