<script setup lang="ts">
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const todayDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
  })
})

const stats = [
  {
    label: 'Today Revenue',
    value: '$1,240',
    delta: '+12% vs yesterday',
    up: true,
    color: 'var(--accent)',
    icon: '💰',
  },
  {
    label: 'New Orders',
    value: '34',
    delta: '+8 vs yesterday',
    up: true,
    color: 'var(--green)',
    icon: '📦',
  },
  {
    label: 'Active Readers',
    value: '892',
    delta: '+47 this week',
    up: true,
    color: 'var(--blue)',
    icon: '📖',
  },
  {
    label: 'Active Subscriptions',
    value: '112',
    delta: '+9 this month',
    up: true,
    color: 'var(--blue)',
    icon: '📖',
  },
]
</script>

<template>
  <div class="page active">
    <div class="page-header">
      <div>
        <h1 class="page-header__title">Dashboard</h1>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div
        v-for="(stat, i) in stats"
        :key="i"
        class="stat-card"
        :style="{ '--stat-color': stat.color }"
      >
        <div class="stat-card__label">{{ stat.label }}</div>
        <div class="stat-card__value">{{ stat.value }}</div>

        <div class="stat-card__delta" :class="stat.up ? 'up' : 'down'">
          {{ stat.up ? '▲' : '▼' }} {{ stat.delta }}
        </div>

        <div class="stat-card__icon">{{ stat.icon }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.activity-item .dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-top: 4px;
  flex-shrink: 0;
}
</style>
