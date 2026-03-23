<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useBooksStore } from '@/stores/books'
import { booksApi } from '@/api/books'
import AppSpinner from '@/components/ui/AppSpinner.vue'

const books = useBooksStore()

onMounted(() => books.fetchBooks({ per_page: 50 }))

async function handleDelete(id: number): Promise<void> {
  if (!confirm('Delete this book?')) return
  await booksApi.destroy(id)
  await books.fetchBooks({ per_page: 50 })
}
async function handlePublish(id: number): Promise<void> {
  if (!confirm('Publish this book?')) return
  await booksApi.publish(id)
  await books.fetchBooks({ per_page: 50 })
}
</script>

<template>
  <div>
    <div class="page-header">
      <h1>Books</h1>
      <RouterLink :to="{ name: 'admin-book-create' }" class="btn btn--primary">
        + New Book
      </RouterLink>
    </div>

    <AppSpinner v-if="books.isLoading" />

    <table v-else class="data-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Status</th>
          <th>Access</th>
          <th>Price</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="book in books.books" :key="book.id">
          <td>{{ book.id }}</td>
          <td>{{ book.title }}</td>
          <td>
            <span class="badge">{{ book.status }}</span>
          </td>
          <td>{{ book.access_type }}</td>
          <td>{{ book.price.formatted }}</td>
          <td class="actions">
            <RouterLink
              :to="{ name: 'admin-book-edit', params: { id: book.id } }"
              class="btn btn-edit"
              >Edit</RouterLink
            >
            <button class="btn btn-publish" @click="handlePublish(book.id)">Publish</button>
            <button class="btn-delete" @click="handleDelete(book.id)">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.page-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  background: #fff;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.data-table th,
.data-table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #f3f4f6;
  font-size: 0.9rem;
}

.data-table th {
  background: #f9fafb;
  font-weight: 600;
  color: #374151;
}

.actions {
  display: flex;
  gap: 0.75rem;
}

.btn {
  padding: 0.4rem 0.9rem;
  border-radius: 6px;
  border: none;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  text-decoration: none;
}

.btn--primary {
  background: #4f46e5;
  color: #fff;
}

.btn-publish {
  background: #4f46e5;
  color: #fff;
}

.btn-publish:hover {
  background: #4338ca;
}

/* ---- Edit ---- */
.btn-edit {
  background: #6366f1;
  color: #fff;
}

.btn-edit:hover {
  background: #4f46e5;
}

.btn-delete {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 0.85rem;
  padding: 0;
}

.badge {
  font-size: 0.75rem;
  padding: 0.2rem 0.5rem;
  background: #f3f4f6;
  border-radius: 9999px;
}
</style>
