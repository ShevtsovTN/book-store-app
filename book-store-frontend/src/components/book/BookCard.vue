<script setup lang="ts">
import { RouterLink } from 'vue-router'
import type { Book } from '@/types'

defineProps<{ book: Book }>()
</script>

<template>
  <RouterLink class="book-card" :to="{ name: 'book-detail', params: { id: book.id } }">
    <div class="book-card__cover">
      <img v-if="book.cover_url" :src="book.cover_url" :alt="book.title" />
      <div v-else class="book-card__cover-placeholder" />
    </div>

    <div class="book-card__body">
      <h3 class="book-card__title">{{ book.title }}</h3>

      <div class="book-card__meta">
        <span v-if="book.is_free" class="tag tag--green">Free</span>
        <span v-else class="book-card__price">{{ book.price.formatted }}</span>
        <span class="tag tag--gray">{{ book.language.toUpperCase() }}</span>
      </div>
    </div>
  </RouterLink>
</template>

<style scoped>
.book-card {
  display: flex;
  flex-direction: column;
  text-decoration: none;
  color: inherit;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
  transition:
    box-shadow 0.15s,
    transform 0.15s;
}

.book-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.book-card__cover {
  aspect-ratio: 2/3;
  overflow: hidden;
  background: #f3f4f6;
}

.book-card__cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.book-card__cover-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
}

.book-card__body {
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.book-card__title {
  font-size: 0.9rem;
  font-weight: 600;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.book-card__meta {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.book-card__price {
  font-size: 0.85rem;
  font-weight: 600;
  color: #4f46e5;
}

.tag {
  font-size: 0.7rem;
  padding: 0.15rem 0.45rem;
  border-radius: 9999px;
  font-weight: 500;
}

.tag--green {
  background: #d1fae5;
  color: #065f46;
}

.tag--gray {
  background: #f3f4f6;
  color: #6b7280;
}
</style>
