<script setup lang="ts">
import { onMounted } from 'vue'
import { useBooksStore } from '@/stores/books'
import BookCard from '@/components/book/BookCard.vue'
import AppSpinner from '@/components/ui/AppSpinner.vue'

const books = useBooksStore()
onMounted(() => books.fetchPopular('week'))
</script>

<template>
  <div>
    <h1 class="page-title">Popular This Week</h1>

    <AppSpinner v-if="books.isLoading" />

    <p v-else-if="books.error" class="error">{{ books.error }}</p>

    <div v-else class="book-grid">
      <BookCard v-for="book in books.books" :key="book.id" :book="book" />
    </div>
  </div>
</template>

<style scoped>
.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.book-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1.5rem;
}

.error {
  color: #ef4444;
}
</style>
