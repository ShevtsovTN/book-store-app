<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useBreadcrumb } from '@/composables/useBreadcrumb.ts'

const items = useBreadcrumb()
</script>

<template>
  <div v-if="items.length" class="breadcrumb">
    <div class="breadcrumb__inner">
      <template v-for="(item, index) in items" :key="index">
        <RouterLink v-if="item.to" :to="item.to">{{ item.text }}</RouterLink>
        <span v-else>{{ item.text }}</span>

        <span v-if="index < items.length - 1" class="breadcrumb__separator" aria-hidden="true">
          ›
        </span>
      </template>
    </div>
  </div>
</template>

<style></style>
