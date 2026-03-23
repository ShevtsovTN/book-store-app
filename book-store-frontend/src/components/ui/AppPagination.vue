<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  current: number
  total: number
}>()

const emit = defineEmits<{
  change: [page: number]
}>()

const pages = computed<(number | '…')[]>(() => {
  const { current, total } = props
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)

  const result: (number | '…')[] = [1]

  if (current > 3) result.push('…')

  const start = Math.max(2, current - 1)
  const end = Math.min(total - 1, current + 1)

  for (let i = start; i <= end; i++) result.push(i)

  if (current < total - 2) result.push('…')
  result.push(total)

  return result
})
</script>

<template>
  <nav class="pagination" aria-label="Pagination">
    <button class="pagination__btn" :disabled="current === 1" @click="emit('change', current - 1)">
      ←
    </button>

    <template v-for="page in pages" :key="page">
      <span v-if="page === '…'" class="pagination__ellipsis">…</span>
      <button
        v-else
        class="pagination__btn"
        :class="{ 'pagination__btn--active': page === current }"
        @click="emit('change', page)"
      >
        {{ page }}
      </button>
    </template>

    <button
      class="pagination__btn"
      :disabled="current === total"
      @click="emit('change', current + 1)"
    >
      →
    </button>
  </nav>
</template>

<style scoped>
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  padding: 1rem 0;
}

.pagination__btn {
  min-width: 36px;
  height: 36px;
  padding: 0 0.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #fff;
  cursor: pointer;
  font-size: 0.875rem;
  transition:
    background 0.15s,
    border-color 0.15s;
}

.pagination__btn:hover:not(:disabled) {
  border-color: #4f46e5;
  color: #4f46e5;
}

.pagination__btn--active {
  background: #4f46e5;
  border-color: #4f46e5;
  color: #fff;
}

.pagination__btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.pagination__ellipsis {
  padding: 0 0.25rem;
  color: #9ca3af;
}
</style>
