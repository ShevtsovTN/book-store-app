<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { booksApi } from '@/api/books'
import { HttpError } from '@/api/client'
import type { Book, BookStatus, AccessType } from '@/types'

// ── State ────────────────────────────────────────────────────
const books = ref<Book[]>([])
const isLoading = ref(false)
const searchQuery = ref('')
const statusFilter = ref<BookStatus | 'all'>('all')

// ── Book Modal ───────────────────────────────────────────────
const showModal = ref(false)
const editingId = ref<number | null>(null)
const isSaving = ref(false)
const fieldErrors = ref<Record<string, string[]>>({})

const form = ref({
  title: '',
  description: '',
  isbn: '',
  language: 'es',
  publisher: '',
  published_year: new Date().getFullYear() as number | null,
  access_type: 'free' as AccessType,
  price: 0,
  currency: 'EUR' as 'USD' | 'EUR',
  status: 'draft' as BookStatus,
})

// ── Computed ─────────────────────────────────────────────────
const filtered = computed(() => {
  let list = books.value
  if (statusFilter.value !== 'all') {
    list = list.filter((b) => b.status === statusFilter.value)
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(
      (b) =>
        b.title.toLowerCase().includes(q) ||
        (b.isbn ?? '').includes(q) ||
        (b.publisher ?? '').toLowerCase().includes(q),
    )
  }
  return list
})

const subtitle = computed(() => `Catálogo completo · ${books.value.length} libros`)

// ── API Calls ─────────────────────────────────────────────────
async function load(): Promise<void> {
  isLoading.value = true
  try {
    const res = await booksApi.index({ per_page: 100 })
    books.value = res.data
  } finally {
    isLoading.value = false
  }
}

onMounted(load)

// ── Modal helpers ─────────────────────────────────────────────
function openCreate(): void {
  editingId.value = null
  fieldErrors.value = {}
  form.value = {
    title: '',
    description: '',
    isbn: '',
    language: 'es',
    publisher: '',
    published_year: new Date().getFullYear(),
    access_type: 'free',
    price: 0,
    currency: 'EUR',
    status: 'draft',
  }
  showModal.value = true
}

function openEdit(book: Book): void {
  editingId.value = book.id
  fieldErrors.value = {}
  form.value = {
    title: book.title,
    description: book.description ?? '',
    isbn: book.isbn ?? '',
    language: book.language,
    publisher: book.publisher ?? '',
    published_year: book.published_year,
    access_type: book.access_type,
    price: book.price.amount,
    currency: book.price.currency as 'USD' | 'EUR',
    status: book.status,
  }
  showModal.value = true
}

function closeModal(): void {
  showModal.value = false
}

async function handleSave(): Promise<void> {
  fieldErrors.value = {}
  isSaving.value = true
  const payload = {
    title: form.value.title,
    description: form.value.description || null,
    isbn: form.value.isbn || null,
    language: form.value.language,
    publisher: form.value.publisher || null,
    published_year: form.value.published_year,
    access_type: form.value.access_type,
    price: form.value.price,
    currency: form.value.currency,
  }
  try {
    if (editingId.value) {
      await booksApi.update(editingId.value, payload)
    } else {
      await booksApi.create(payload)
    }
    closeModal()
    await load()
    showToast(editingId.value ? 'Libro actualizado' : 'Libro creado')
  } catch (e) {
    if (e instanceof HttpError && e.body.errors) {
      fieldErrors.value = e.body.errors
    }
  } finally {
    isSaving.value = false
  }
}

async function toggleStatus(book: Book): Promise<void> {
  const newStatus: BookStatus = book.status === 'published' ? 'draft' : 'published'
  await booksApi.update(book.id, {
    title: book.title,
    language: book.language,
    access_type: book.access_type,
    price: book.price.amount,
    currency: book.price.currency as 'USD' | 'EUR',
    // status: newStatus,
  })
  await load()
  showToast(`"${book.title}" ${newStatus === 'published' ? 'publicado' : 'guardado como borrador'}`)
}

async function handleDelete(book: Book): Promise<void> {
  if (!confirm(`¿Eliminar "${book.title}"?`)) return
  await booksApi.destroy(book.id)
  await load()
  showToast(`"${book.title}" eliminado`)
}

// ── Toast ─────────────────────────────────────────────────────
const toastMsg = ref('')
const toastVisible = ref(false)
let toastTimer: ReturnType<typeof setTimeout>

function showToast(msg: string): void {
  toastMsg.value = msg
  toastVisible.value = true
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => (toastVisible.value = false), 3000)
}

// ── Helpers ───────────────────────────────────────────────────
const ACCESS_LABELS: Record<AccessType, string> = {
  free: 'Gratis',
  purchase: 'Individual',
  subscription: 'Suscripción',
}
const ACCESS_CLS: Record<AccessType, string> = {
  free: 'badge-green',
  purchase: 'badge-purple',
  subscription: 'badge-blue',
}
const BOOK_COLORS = [
  'linear-gradient(160deg,#8B1A1A,#4A0E0E)',
  'linear-gradient(160deg,#1a4a8a,#0d2d5e)',
  'linear-gradient(160deg,#c06010,#8a3a00)',
  'linear-gradient(160deg,#1a6a3a,#0d3d22)',
  'linear-gradient(160deg,#4a1a8a,#2d0d5e)',
] as const
function coverColor(id: number): string {
  return BOOK_COLORS[id % BOOK_COLORS.length] ?? BOOK_COLORS[0]
}
</script>

<template>
  <div class="books-page">
    <!-- Page header -->
    <div class="page-header">
      <div>
        <h1 class="page-header__title">Gestión de Libros</h1>
        <p class="page-header__sub">{{ subtitle }}</p>
      </div>
      <div class="header-actions">
        <button class="btn-secondary" @click="load">
          <svg
            width="14"
            height="14"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <polyline points="1 4 1 10 7 10" />
            <path d="M3.51 15a9 9 0 1 0 .49-3.26" />
          </svg>
          Actualizar
        </button>
        <button class="btn-primary" @click="openCreate">
          <svg
            width="14"
            height="14"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
          >
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
          </svg>
          Nuevo libro
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-wrap">
      <!-- Filters -->
      <div class="table-filters">
        <div class="filter-tabs">
          <button
            v-for="s in ['all', 'published', 'draft', 'archived']"
            :key="s"
            class="filter-tab"
            :class="{ active: statusFilter === s }"
            @click="statusFilter = s as BookStatus | 'all'"
          >
            {{
              s === 'all'
                ? 'Todos'
                : s === 'published'
                  ? 'Publicados'
                  : s === 'draft'
                    ? 'Borradores'
                    : 'Archivados'
            }}
          </button>
        </div>

        <div class="filter-search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
          </svg>
          <input v-model="searchQuery" type="text" placeholder="Buscar título, ISBN..." />
        </div>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="table-loading">
        <span>Cargando...</span>
      </div>

      <!-- Table body -->
      <table v-else>
        <thead>
          <tr>
            <th>Libro</th>
            <th>ISBN</th>
            <th>Acceso</th>
            <th>Precio</th>
            <th>Estado</th>
            <th>Páginas</th>
            <th style="width: 120px">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="book in filtered" :key="book.id">
            <td>
              <div class="book-row-cell">
                <div class="book-cover" :style="{ background: coverColor(book.id) }">📖</div>
                <div class="book-info">
                  <span class="book-info__title">{{ book.title }}</span>
                  <span class="book-info__meta"
                    >{{ book.publisher ?? '—' }} · {{ book.language.toUpperCase() }}</span
                  >
                </div>
              </div>
            </td>
            <td class="td-mono">{{ book.isbn ?? '—' }}</td>
            <td>
              <span class="badge" :class="ACCESS_CLS[book.access_type]">
                {{ ACCESS_LABELS[book.access_type] }}
              </span>
            </td>
            <td>
              <strong class="price">{{ book.is_free ? '—' : book.price.formatted }}</strong>
            </td>
            <td>
              <span
                class="badge"
                :class="
                  book.status === 'published'
                    ? 'badge-green'
                    : book.status === 'draft'
                      ? 'badge-amber'
                      : 'badge-red'
                "
              >
                {{
                  book.status === 'published'
                    ? 'Publicado'
                    : book.status === 'draft'
                      ? 'Borrador'
                      : 'Archivado'
                }}
              </span>
            </td>
            <td class="td-muted">{{ book.pages_count }}</td>
            <td>
              <div class="row-actions">
                <button class="btn-icon" title="Editar" @click="openEdit(book)">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                  </svg>
                </button>
                <button
                  class="btn-icon"
                  :title="book.status === 'published' ? 'Despublicar' : 'Publicar'"
                  @click="toggleStatus(book)"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                  </svg>
                </button>
                <button
                  class="btn-icon"
                  style="color: var(--red)"
                  title="Eliminar"
                  @click="handleDelete(book)"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6" />
                    <path
                      d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"
                    />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!filtered.length">
            <td colspan="7" class="table-empty">No se encontraron libros</td>
          </tr>
        </tbody>
      </table>

      <div class="table-footer">
        <span class="table-footer__info">{{ filtered.length }} de {{ books.length }} libros</span>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal">
          <div class="modal__header">
            <h2 class="modal__title">{{ editingId ? 'Editar libro' : 'Nuevo libro' }}</h2>
            <button class="modal__close" @click="closeModal">×</button>
          </div>

          <div class="modal__body">
            <div class="form-grid-2">
              <!-- Title -->
              <div class="form-field form-full">
                <label class="form-label">Título <span class="req">*</span></label>
                <input
                  v-model="form.title"
                  type="text"
                  class="form-input"
                  :class="{ error: fieldErrors.title }"
                  placeholder="Título del libro"
                />
                <span v-if="fieldErrors.title" class="field-error">{{ fieldErrors.title[0] }}</span>
              </div>

              <!-- Language / Year -->
              <div class="form-field">
                <label class="form-label">Idioma <span class="req">*</span></label>
                <select v-model="form.language" class="form-select">
                  <option value="es">Español</option>
                  <option value="en">English</option>
                  <option value="fr">Français</option>
                  <option value="de">Deutsch</option>
                  <option value="ru">Русский</option>
                </select>
              </div>

              <div class="form-field">
                <label class="form-label">Año publicación</label>
                <input
                  v-model.number="form.published_year"
                  type="number"
                  class="form-input"
                  :class="{ error: fieldErrors.published_year }"
                  placeholder="2024"
                  min="1000"
                  max="2100"
                />
              </div>

              <!-- ISBN / Publisher -->
              <div class="form-field">
                <label class="form-label">ISBN</label>
                <input
                  v-model="form.isbn"
                  type="text"
                  class="form-input"
                  placeholder="978-X-XXX-XXXXX-X"
                />
              </div>

              <div class="form-field">
                <label class="form-label">Editorial</label>
                <input
                  v-model="form.publisher"
                  type="text"
                  class="form-input"
                  placeholder="Editorial..."
                />
              </div>

              <!-- Access type / Price / Currency -->
              <div class="form-field">
                <label class="form-label">Tipo de acceso <span class="req">*</span></label>
                <select v-model="form.access_type" class="form-select">
                  <option value="free">Gratuito</option>
                  <option value="purchase">Venta individual</option>
                  <option value="subscription">Suscripción</option>
                </select>
              </div>

              <div class="form-field">
                <label class="form-label">Precio (centavos)</label>
                <input
                  v-model.number="form.price"
                  type="number"
                  class="form-input"
                  :class="{ error: fieldErrors.price }"
                  placeholder="1990 = €19.90"
                  min="0"
                />
                <span v-if="fieldErrors.price" class="field-error">{{ fieldErrors.price[0] }}</span>
              </div>

              <div class="form-field">
                <label class="form-label">Divisa</label>
                <select v-model="form.currency" class="form-select">
                  <option value="EUR">EUR</option>
                  <option value="USD">USD</option>
                </select>
              </div>

              <div class="form-field">
                <label class="form-label">Estado</label>
                <select v-model="form.status" class="form-select">
                  <option value="draft">Borrador</option>
                  <option value="published">Publicado</option>
                  <option value="archived">Archivado</option>
                </select>
              </div>

              <!-- Description -->
              <div class="form-field form-full">
                <label class="form-label">Descripción</label>
                <textarea
                  v-model="form.description"
                  class="form-input form-textarea"
                  rows="4"
                  placeholder="Sinopsis del libro..."
                />
              </div>
            </div>
          </div>

          <div class="modal__footer">
            <button class="btn-secondary" @click="closeModal">Cancelar</button>
            <button class="btn-primary" :disabled="isSaving" @click="handleSave">
              <svg
                v-if="!isSaving"
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
              >
                <polyline points="20 6 9 17 4 12" />
              </svg>
              <span>{{ isSaving ? 'Guardando…' : 'Guardar libro' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Toast -->
    <Teleport to="body">
      <div class="toast" :class="{ show: toastVisible }">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="20 6 9 17 4 12" />
        </svg>
        <span>{{ toastMsg }}</span>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
@import '@/assets/admin.css';

.books-page {
  animation: fadeUp 0.35s ease both;
}
@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.header-actions {
  display: flex;
  gap: 10px;
}

/* Book row */
.book-row-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}
.book-cover {
  width: 28px;
  height: 38px;
  border-radius: 2px 4px 4px 2px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  flex-shrink: 0;
  box-shadow: -2px 2px 6px rgba(0, 0, 0, 0.3);
}
.book-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}
.book-info__title {
  font-size: 0.86rem;
  font-weight: 500;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 240px;
}
.book-info__meta {
  font-size: 0.74rem;
  color: var(--text-muted);
}

.price {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 0.9rem;
  font-weight: 700;
}

.row-actions {
  display: flex;
  gap: 6px;
}

.table-loading {
  padding: 32px;
  text-align: center;
  font-size: 0.84rem;
  color: var(--text-muted);
}
.table-empty {
  text-align: center;
  padding: 32px;
  color: var(--text-muted);
  font-size: 0.84rem;
}

/* Form modal */
.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0 16px;
}
.form-full {
  grid-column: 1 / -1;
}
.form-textarea {
  resize: vertical;
  line-height: 1.6;
}
.req {
  color: var(--red);
}
.field-error {
  font-size: 0.75rem;
  color: var(--red);
}
.form-input.error {
  border-color: var(--red);
}
</style>
