<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useReadingStore } from '@/stores/reading'
import AppSpinner from '@/components/ui/AppSpinner.vue'
import type { ReadingStatus } from '@/types'

const reading = useReadingStore()
const status = ref<ReadingStatus | ''>('')

function load(): void {
  reading.fetchList({ status: status.value || undefined })
}

watch(status, load)
onMounted(load)

async function remove(bookId: number): Promise<void> {
  await reading.removeFromList(bookId)
}
</script>

<template>
  <div>
    <div class="list-header">
      <h1>Reading List</h1>

      <select v-model="status">
        <option value="">All</option>
        <option value="want_to_read">Want to Read</option>
        <option value="reading">Reading</option>
        <option value="finished">Finished</option>
        <option value="dropped">Dropped</option>
      </select>
    </div>

    <AppSpinner v-if="reading.isLoading" />

    <p v-else-if="!reading.list.length" class="empty">Your reading list is empty.</p>

    <ul v-else class="reading-list">
      <li v-for="entry in reading.list" :key="entry.book_id" class="reading-item">
        <div class="reading-item__info">
          <RouterLink :to="{ name: 'book-detail', params: { id: entry.book_id } }">
            Book #{{ entry.book_id }}
          </RouterLink>
          <span class="badge">{{ entry.status }}</span>
        </div>

        <div class="reading-item__progress">
          <div class="progress-bar">
            <div
              class="progress-bar__fill"
              :style="{ width: `${entry.progress_percentage ?? 0}%` }"
            />
          </div>
          <span class="progress-text"
            >{{ entry.current_page }} / {{ entry.total_pages ?? '?' }} pages</span
          >
        </div>

        <div class="reading-item__actions">
          <RouterLink
            v-if="entry.status === 'reading'"
            :to="{ name: 'read-page', params: { bookId: entry.book_id, pageId: 1 } }"
            class="btn btn--sm"
          >
            Continue
          </RouterLink>
          <button class="btn btn--sm btn--danger" @click="remove(entry.book_id)">Remove</button>
        </div>
      </li>
    </ul>
  </div>
</template>

<style scoped>
.list-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.list-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
}

.list-header select {
  padding: 0.4rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
}

.reading-list {
  list-style: none;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.reading-item {
  display: grid;
  grid-template-columns: 1fr auto auto;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
}

.reading-item__info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.reading-item__progress {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  min-width: 160px;
}

.progress-bar {
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar__fill {
  height: 100%;
  background: #4f46e5;
  transition: width 0.3s;
}

.progress-text {
  font-size: 0.75rem;
  color: #6b7280;
}

.reading-item__actions {
  display: flex;
  gap: 0.5rem;
}

.badge {
  font-size: 0.75rem;
  padding: 0.2rem 0.5rem;
  background: #e0e7ff;
  color: #3730a3;
  border-radius: 9999px;
}

.btn {
  padding: 0.35rem 0.75rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 500;
  text-decoration: none;
}

.btn--sm {
  background: #4f46e5;
  color: #fff;
}

.btn--danger {
  background: #fee2e2;
  color: #b91c1c;
}

.empty {
  color: #6b7280;
}
</style>
