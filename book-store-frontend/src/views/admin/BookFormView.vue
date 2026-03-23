<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { booksApi } from '@/api/books'
import { HttpError } from '@/api/client'
import AppFormField from '@/components/ui/AppFormField.vue'
import AppSpinner from '@/components/ui/AppSpinner.vue'
import type { AccessType, BookStatus } from '@/types'
import { useToastStore } from '@/stores/toast'

const toast = useToastStore()

const props = defineProps<{ id?: number }>()

const router = useRouter()
const isEdit = computed(() => props.id !== undefined)

const title = ref('')
const description = ref('')
const isbn = ref('')
const language = ref('en')
const publisher = ref('')
const publishedYear = ref<number | null>(null)
const accessType = ref<AccessType>('free')
const price = ref(0)
const currency = ref<'USD' | 'EUR'>('EUR')
const status = ref<BookStatus>('draft')

const fieldErrors = ref<Record<string, string[]>>({})
const isLoading = ref(false)
const isFetching = ref(false)

onMounted(async () => {
  if (!isEdit.value) return
  isFetching.value = true
  try {
    const book = await booksApi.show(props.id!)
    title.value = book.title
    description.value = book.description ?? ''
    isbn.value = book.isbn ?? ''
    language.value = book.language
    publisher.value = book.publisher ?? ''
    publishedYear.value = book.published_year
    accessType.value = book.access_type
    price.value = book.price.amount
    currency.value = book.price.currency as 'USD' | 'EUR'
    status.value = book.status
  } finally {
    isFetching.value = false
  }
})

async function handleSubmit(): Promise<void> {
  fieldErrors.value = {}
  isLoading.value = true

  const payload = {
    title: title.value,
    description: description.value || null,
    isbn: isbn.value || null,
    language: language.value,
    publisher: publisher.value || null,
    published_year: publishedYear.value,
    access_type: accessType.value,
    price: price.value,
    currency: currency.value,
  }

  try {
    if (isEdit.value) {
      await booksApi.update(props.id!, payload)
      toast.success('Success', 'Book updated successfully')
    } else {
      await booksApi.create(payload)
      toast.success('Success', 'Book created successfully')
    }

    await router.push({ name: 'admin-books' })
  } catch (e) {
    let message = 'Something went wrong. Please try again.'

    if (e instanceof HttpError) {
      if (e.body?.message) {
        message = e.body.message
      }

      if (e.body?.errors) {
        fieldErrors.value = e.body.errors
      }
    }

    toast.error('Error', message)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="book-form-page">
    <h1 class="page-title">{{ isEdit ? 'Edit Book' : 'New Book' }}</h1>

    <AppSpinner v-if="isFetching" />

    <form v-else class="book-form" @submit.prevent="handleSubmit">
      <div class="form-grid">
        <AppFormField label="Title *" :errors="fieldErrors.title">
          <input v-model="title" type="text" required />
        </AppFormField>

        <AppFormField label="Language *" :errors="fieldErrors.language">
          <input v-model="language" type="text" maxlength="2" placeholder="en" required />
        </AppFormField>

        <AppFormField label="ISBN" :errors="fieldErrors.isbn">
          <input v-model="isbn" type="text" />
        </AppFormField>

        <AppFormField label="Publisher" :errors="fieldErrors.publisher">
          <input v-model="publisher" type="text" />
        </AppFormField>

        <AppFormField label="Published Year" :errors="fieldErrors.published_year">
          <input v-model.number="publishedYear" type="number" min="1000" max="2100" />
        </AppFormField>

        <AppFormField label="Access Type *" :errors="fieldErrors.access_type">
          <select v-model="accessType">
            <option value="free">Free</option>
            <option value="purchase">Purchase</option>
            <option value="subscription">Subscription</option>
          </select>
        </AppFormField>

        <AppFormField label="Price (cents) *" :errors="fieldErrors.price">
          <input v-model.number="price" type="number" min="0" required />
        </AppFormField>

        <AppFormField label="Currency *" :errors="fieldErrors.currency">
          <select v-model="currency">
            <option value="EUR">EUR</option>
            <option value="USD">USD</option>
          </select>
        </AppFormField>
      </div>

      <AppFormField label="Description" :errors="fieldErrors.description">
        <textarea v-model="description" rows="5" />
      </AppFormField>

      <div class="form-actions">
        <button type="button" class="btn btn--secondary" @click="router.back()">Cancel</button>
        <button type="submit" class="btn btn--primary" :disabled="isLoading">
          {{ isLoading ? 'Saving…' : isEdit ? 'Update Book' : 'Create Book' }}
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.book-form {
  background: #fff;
  border-radius: 12px;
  padding: 2rem;
  max-width: 860px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0 1.5rem;
}

textarea {
  width: 100%;
  padding: 0.6rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.95rem;
  resize: vertical;
  font-family: inherit;
}

.form-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

.btn {
  padding: 0.6rem 1.25rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
}

.btn--primary {
  background: #4f46e5;
  color: #fff;
}

.btn--secondary {
  background: #f3f4f6;
  color: #374151;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
