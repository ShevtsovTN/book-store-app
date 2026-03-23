<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useBooksStore } from '@/stores/books'
import BookCard from '@/components/book/BookCard.vue'
import AppSpinner from '@/components/ui/AppSpinner.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import type { BookStatus, AccessType } from '@/types'

const books = useBooksStore()

const status = ref<BookStatus | ''>('')
const accessType = ref<AccessType | ''>('')
const page = ref(1)

function load(): void {
  books.fetchBooks({
    status: status.value || undefined,
    access_type: accessType.value || undefined,
    page: page.value,
    per_page: 20,
  })
}

watch([status, accessType], () => {
  page.value = 1
  load()
})

watch(page, load)
onMounted(load)
</script>

<template>
  <div>
    <div class="catalog-filters">
      <select v-model="status">
        <option value="">All statuses</option>
        <option value="published">Published</option>
        <option value="draft">Draft</option>
        <option value="archived">Archived</option>
      </select>

      <select v-model="accessType">
        <option value="">All access types</option>
        <option value="free">Free</option>
        <option value="purchase">Purchase</option>
        <option value="subscription">Subscription</option>
      </select>
    </div>

    <AppSpinner v-if="books.isLoading" />

    <template v-else>
      <div class="book-grid">
        <BookCard v-for="book in books.books" :key="book.id" :book="book" />
      </div>

      <AppPagination
        v-if="books.meta"
        :current="books.meta.current_page"
        :total="books.meta.total_pages"
        @change="page = $event"
      />
    </template>
  </div>
</template>

<style scoped>
.catalog-filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.catalog-filters select {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: #fff;
}

.book-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}
</style>
