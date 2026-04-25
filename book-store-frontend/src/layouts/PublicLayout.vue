<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import AppBreadcrumbs from '@/components/ui/AppBreadcrumbs.vue'
import { onMounted } from 'vue'

const auth = useAuthStore()
const cart = useCartStore()

onMounted(() => cart.fetchCart())
</script>

<template>
  <header class="header">
    <div class="header__inner container">
      <RouterLink :to="{ name: 'home' }" class="logo">
        <span class="logo__book">Book</span>
        <span class="logo__shop">store</span>
      </RouterLink>
      <nav class="nav">
        <div class="nav__links">
          <RouterLink
            :to="{ name: 'catalog' }"
            class="nav__link"
            active-class="nav__link--active"
            exact-active-class="active"
          >
            Catalog
          </RouterLink>
          <RouterLink
            :to="{ name: 'search' }"
            class="nav__link"
            active-class="nav__link--active"
            exact-active-class="active"
          >
            Search
          </RouterLink>
        </div>
      </nav>
      <div v-if="auth.isAuthenticated" class="header__actions">
        <RouterLink :to="{ name: 'reading-list' }" class="header__wishlist">My Books</RouterLink>
        <RouterLink :to="{ name: 'cart' }" class="cart-btn">
          <div class="cart-btn__icon">
            <svg
              width="22"
              height="22"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
            >
              <circle cx="9" cy="21" r="1"></circle>
              <circle cx="20" cy="21" r="1"></circle>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <span v-if="cart.itemsCount > 0" class="cart-btn__count">{{ cart.itemsCount }}</span>
          </div>
          <div class="cart-btn__info">
            <span class="cart-btn__label">Cart</span>
            <span v-if="cart.itemsCount > 0" class="cart-btn__total"
              >Total: {{ cart.total?.formatted }}</span
            >
          </div>
        </RouterLink>
      </div>
      <div v-else class="header__actions">
        <RouterLink :to="{ name: 'login' }">Login</RouterLink>
        <RouterLink :to="{ name: 'register' }">Register</RouterLink>
      </div>
    </div>
  </header>

  <AppBreadcrumbs></AppBreadcrumbs>

  <main class="main container">
    <RouterView />
  </main>

  <footer class="footer">
    <div class="footer__inner container">
      <div class="footer__col">
        <div class="logo footer__logo">
          <span class="logo__book">Book</span><span class="logo__shop">store</span>
        </div>
        <p class="footer__desc">
          Your trusted bookstore since 2010. Thousands of titles, fair prices, and fast shipping
          across Spain.
        </p>
      </div>

      <div class="footer__col">
        <h4 class="footer__heading">Categories</h4>
        <ul class="footer__links">
          <li>
            <RouterLink :to="{ name: 'catalog' }">Fiction</RouterLink>
          </li>
          <li>
            <RouterLink :to="{ name: 'catalog' }">Science Fiction</RouterLink>
          </li>
          <li>
            <RouterLink :to="{ name: 'catalog' }">Children</RouterLink>
          </li>
          <li>
            <RouterLink :to="{ name: 'catalog' }">Classics</RouterLink>
          </li>
        </ul>
      </div>

      <div class="footer__col">
        <h4 class="footer__heading">Information</h4>
        <ul class="footer__links">
          <li><a href="#">About us</a></li>
          <li><a href="#">Returns</a></li>
          <li><a href="#">Privacy</a></li>
        </ul>
      </div>

      <div class="footer__col">
        <h4 class="footer__heading">Contact</h4>
        <ul class="footer__contact">
          <li>📍 Calle Libros 12, Madrid</li>
          <li>📞 900 000 000</li>
          <li>✉️ hello@bookshop.es</li>
        </ul>
      </div>
    </div>

    <div class="footer__bottom">
      <p>© 2025 BookShop. All rights reserved.</p>
    </div>
  </footer>
</template>

<style>
@import '@/assets/styles.css';
</style>
