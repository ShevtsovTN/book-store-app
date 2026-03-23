<script setup lang="ts">
import { useToastStore } from '@/stores/toast'
import ToastContainer from '@/components/ui/ToastContainer.vue'

const toast = useToastStore()
</script>

<template>
  <div class="demo">
    <h1>Toast notifications</h1>

    <section class="demo__grid">
      <button
        class="btn btn--success"
        @click="toast.success('Книга добавлена', 'Добавлена в список «Хочу прочитать»')"
      >
        success
      </button>
      <button
        class="btn btn--error"
        @click="toast.error('Ошибка оплаты', 'Проверьте данные карты и попробуйте снова')"
      >
        error
      </button>
      <button
        class="btn btn--warning"
        @click="toast.warning('Сессия истекает', 'Вы будете отключены через 5 минут')"
      >
        warning
      </button>
      <button
        class="btn btn--info"
        @click="toast.info('Новая глава доступна', 'Продолжайте читать «Мастер и Маргарита»')"
      >
        info
      </button>
      <button class="btn btn--neutral" @click="toast.success('Без сообщения')">без message</button>
      <button
        class="btn btn--neutral"
        @click="toast.info('Sticky уведомление', 'Не закроется само', 0)"
      >
        sticky (duration 0)
      </button>
    </section>
  </div>

  <ToastContainer />
</template>

<style scoped>
.demo {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2rem;
  background: #f5f0e8;
  font-family: 'Georgia', serif;
}

h1 {
  font-size: 1.5rem;
  font-weight: 400;
  color: #2c2416;
  letter-spacing: 0.03em;
}

.demo__grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  justify-content: center;
  max-width: 480px;
}

.btn {
  padding: 0.6rem 1.4rem;
  border: 1px solid currentColor;
  border-radius: 2px;
  background: transparent;
  font-family: 'Georgia', serif;
  font-size: 0.875rem;
  letter-spacing: 0.04em;
  cursor: pointer;
  transition:
    background 0.15s ease,
    color 0.15s ease;
}

.btn--success {
  color: #4a7c59;
}
.btn--success:hover {
  background: #4a7c59;
  color: #fff;
}

.btn--error {
  color: #b84c4c;
}
.btn--error:hover {
  background: #b84c4c;
  color: #fff;
}

.btn--warning {
  color: #b8843a;
}
.btn--warning:hover {
  background: #b8843a;
  color: #fff;
}

.btn--info {
  color: #4a6b8a;
}
.btn--info:hover {
  background: #4a6b8a;
  color: #fff;
}

.btn--neutral {
  color: #6b5f4e;
}
.btn--neutral:hover {
  background: #6b5f4e;
  color: #fff;
}
</style>
