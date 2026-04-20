<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useBooksStore } from '@/stores/books'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import AppSpinner from '@/components/ui/AppSpinner.vue'
import { useReadingStore } from '@/stores'

const props = defineProps<{ id: number }>()

const booksStore = useBooksStore()
const cartStore = useCartStore()
const readingStore = useReadingStore()
const auth = useAuthStore()
const toast = useToastStore()
const router = useRouter()

const activeTab = ref<'desc' | 'details'>('desc')

const book = computed(() => booksStore.currentBook)

onMounted(() => booksStore.fetchBook(props.id))

async function handleAddToCart(): Promise<void> {
  if (!auth.isAuthenticated) {
    await router.push({ name: 'login' })
    return
  }
  await cartStore.addItem('book', props.id)
  if (!cartStore.error) {
    toast.success('Add to cart', book.value?.title)
  } else {
    toast.error('Fail', cartStore.error)
  }
}

async function handleAddToReadingList(): Promise<void> {
  if (!auth.isAuthenticated) {
    await router.push({ name: 'login' })
    return
  }
  await readingStore.addToList(props.id)
  if (!readingStore.error) {
    toast.success('Add to reading list', book.value?.title)
  } else {
    toast.error('Fail', readingStore.error)
  }
}
</script>

<template>
  <div class="book-detail">
    <AppSpinner v-if="booksStore.isLoading" />

    <div v-else-if="book" class="book-detail__wrapper">
      <!-- HERO SECTION -->
      <section class="book-hero">
        <div class="container book-hero__inner">
          <!-- 3D Cover -->
          <div class="book-cover-col">
            <div class="book-cover-3d">
              <div class="book-spine">
                <span>{{ book.title }}</span>
              </div>
              <div class="book-front">
                <div class="book-front__genre">
                  {{ book.is_free ? 'FREE' : 'CATALOG' }}
                </div>
                <div class="book-front__title">{{ book.title }}</div>
                <div v-if="book.publisher" class="book-front__author">
                  {{ book.publisher }}
                </div>

                <div class="book-front__art">
                  <img
                    v-if="book.cover_url"
                    :src="book.cover_url"
                    :alt="book.title"
                    class="book-front__art-img"
                  />
                  <div v-else class="generic-book-art">
                    <div class="generic-book-art__page" />
                    <div class="generic-book-art__glow">📖</div>
                  </div>
                </div>

                <div class="book-front__award">
                  {{ book.pages_count }} pages • {{ book.language.toUpperCase() }}
                </div>
              </div>
            </div>

            <button
              v-if="!book.is_free"
              class="cover-btn cover-btn--primary"
              :disabled="cartStore.isLoading"
              @click="handleAddToCart"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1" />
                <circle cx="20" cy="21" r="1" />
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
              </svg>
              Add to Cart
            </button>
            <button
              v-if="!book.is_free"
              class="cover-btn cover-btn--primary"
              :disabled="cartStore.isLoading"
              @click="handleAddToReadingList"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1" />
                <circle cx="20" cy="21" r="1" />
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
              </svg>
              Add Reading List
            </button>
          </div>

          <!-- Info -->
          <div class="book-info-col">
            <div class="book-tags">
              <span class="tag tag--blue">{{ book.language.toUpperCase() }}</span>
              <span v-if="book.is_free" class="tag tag--green">Free</span>
              <span v-else class="tag tag--amber">Paid</span>
              <span v-if="book.publisher" class="tag tag--amber">{{ book.publisher }}</span>
            </div>

            <h1 class="book-title">{{ book.title }}</h1>

            <p v-if="book.description" class="book-author">
              {{ book.description.slice(0, 160) }}{{ book.description.length > 160 ? '…' : '' }}
            </p>

            <div class="book-meta">
              <div class="meta-item">
                <span class="meta-item__label">Price</span>
                <span v-if="book.is_free" class="meta-item__val">Free</span>
                <span v-else class="meta-item__val">{{ book.price.formatted }}</span>
              </div>
              <div class="meta-item">
                <span class="meta-item__label">Language</span>
                <span class="meta-item__val">{{ book.language.toUpperCase() }}</span>
              </div>
              <div v-if="book.publisher" class="meta-item">
                <span class="meta-item__label">Publisher</span>
                <span class="meta-item__val">{{ book.publisher }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- BODY -->
      <div class="book-body">
        <div class="container book-body__grid">
          <div>
            <div class="ctabs">
              <button
                class="ctab"
                :class="{ active: activeTab === 'desc' }"
                @click="activeTab = 'desc'"
              >
                Description
              </button>
              <button
                class="ctab"
                :class="{ active: activeTab === 'details' }"
                @click="activeTab = 'details'"
              >
                Details
              </button>
            </div>

            <div v-if="activeTab === 'desc'" class="csection active">
              <h2 class="csection__title">Synopsis</h2>
              <div class="synopsis">
                <p>{{ book.description ?? 'No description available.' }}</p>
              </div>
            </div>

            <div v-if="activeTab === 'details'" class="csection active">
              <h2 class="csection__title">Book Details</h2>
              <div class="detail-row">
                <span class="chapter-num">Language</span>
                <span class="chapter-title">{{ book.language.toUpperCase() }}</span>
              </div>
              <div class="detail-row">
                <span class="chapter-num">Pages</span>
                <span class="chapter-title">{{ book.pages_count }}</span>
              </div>
              <div v-if="book.publisher" class="detail-row">
                <span class="chapter-num">Publisher</span>
                <span class="chapter-title">{{ book.publisher }}</span>
              </div>
              <div class="detail-row">
                <span class="chapter-num">Price</span>
                <span class="chapter-title">{{
                  book.is_free ? 'Free' : book.price.formatted
                }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.book-detail__wrapper {
  opacity: 1;
  transition: opacity 0.4s ease;
}

.chapter-num {
  font-weight: 700;
  color: #e8a020;
  min-width: 110px;
}
.chapter-title {
  flex: 1;
  color: #222;
}
.chapter-pages {
  font-size: 0.8rem;
  color: #777;
  font-family: 'Barlow Condensed', sans-serif;
}

.generic-book-art {
  width: 100px;
  height: 72px;
  background: linear-gradient(160deg, #3a3a5e, #1e1e3a);
  border-radius: 4px;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.15);
}
.generic-book-art__page {
  position: absolute;
  top: 8px;
  left: 12px;
  right: 12px;
  bottom: 8px;
  background: #f8f4eb;
  border-radius: 2px;
  transform: rotate(8deg);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}
.generic-book-art__glow {
  font-size: 2.4rem;
  filter: drop-shadow(0 0 12px #e8a020);
  z-index: 2;
}
</style>
