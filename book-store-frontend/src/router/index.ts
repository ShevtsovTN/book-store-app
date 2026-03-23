import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

declare module 'vue-router' {
  interface RouteMeta {
    requiresAuth?: boolean
    requiresAdmin?: boolean
    requiresGuest?: boolean
  }
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    // ── Public ──────────────────────────────────────────────
    {
      path: '/',
      component: () => import('@/layouts/PublicLayout.vue'),
      children: [
        {
          path: '',
          name: 'home',
          component: () => import('@/views/HomeView.vue'),
        },
        {
          path: 'catalog',
          name: 'catalog',
          component: () => import('@/views/CatalogView.vue'),
        },
        {
          path: 'books/:id',
          name: 'book-detail',
          component: () => import('@/views/BookDetailView.vue'),
          props: (route) => ({ id: Number(route.params.id) }),
        },
        {
          path: 'search',
          name: 'search',
          component: () => import('@/views/SearchView.vue'),
        },
        {
          path: '/demo/toasts',
          name: 'toast-demo',
          component: () => import('@/views/ToastDemoView.vue'),
        },
      ],
    },

    // ── Guest-only ───────────────────────────────────────────
    {
      path: '/auth',
      component: () => import('@/layouts/AuthLayout.vue'),
      meta: { requiresGuest: true },
      children: [
        {
          path: 'login',
          name: 'login',
          component: () => import('@/views/auth/LoginView.vue'),
        },
        {
          path: 'register',
          name: 'register',
          component: () => import('@/views/auth/RegisterView.vue'),
        },
        {
          path: '/admin/login',
          name: 'admin-login',
          component: () => import('@/views/auth/AdminLoginView.vue'),
          meta: { requiresGuest: true },
        },
      ],
    },

    // ── Reader (authenticated) ───────────────────────────────
    {
      path: '/reader',
      component: () => import('@/layouts/ReaderLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: 'reading-list',
          name: 'reading-list',
          component: () => import('@/views/reader/ReadingListView.vue'),
        },
        //     {
        //       path: 'books/:bookId/read/:pageId',
        //       name: 'read-page',
        //       component: () => import('@/views/reader/ReadPageView.vue'),
        //       props: (route) => ({
        //         bookId: Number(route.params.bookId),
        //         pageId: Number(route.params.pageId),
        //       }),
        //     },
        //     {
        //       path: 'history',
        //       name: 'reading-history',
        //       component: () => import('@/views/reader/ReadingHistoryView.vue'),
        //     },
        //     {
        //       path: 'notifications',
        //       name: 'notifications',
        //       component: () => import('@/views/reader/NotificationsView.vue'),
        //     },
        {
          path: 'cart',
          name: 'cart',
          component: () => import('@/views/reader/CartView.vue'),
        },
      ],
    },

    // ── Admin ────────────────────────────────────────────────
    {
      path: '/admin',
      component: () => import('@/layouts/AdminLayout.vue'),
      meta: { requiresAdmin: true },
      children: [
        {
          path: '',
          name: 'admin-dashboard',
          component: () => import('@/views/admin/DashboardView.vue'),
        },
        {
          path: 'books',
          name: 'admin-books',
          component: () => import('@/views/admin/BooksView.vue'),
        },
        // {
        //   path: 'books/create',
        //   name: 'admin-book-create',
        //   component: () => import('@/views/admin/BookFormView.vue'),
        // },
        // {
        //   path: 'books/:id/edit',
        //   name: 'admin-book-edit',
        //   component: () => import('@/views/admin/BookFormView.vue'),
        //   props: (route) => ({ id: Number(route.params.id) }),
        // },
      ],
    },

    {
      path: '/admin/login',
      name: 'admin-login',
      component: () => import('@/views/auth/AdminLoginView.vue'),
      meta: { requiresGuest: true },
    },

    // // ── Fallback ─────────────────────────────────────────────
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/views/NotFoundView.vue'),
    },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.requiresAdmin && (!auth.isAuthenticated || !auth.isAdmin)) {
    return { name: 'admin-login' }
  }

  if (to.meta.requiresGuest && auth.isAuthenticated) {
    return auth.isAdmin ? { name: 'admin-dashboard' } : { name: 'home' }
  }
})

export default router
