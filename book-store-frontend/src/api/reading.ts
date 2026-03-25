import { http, buildQuery } from './client'
import type {
  ReadingEntry,
  ReadingProgress,
  ReadingHistory,
  BookPage,
  PaginatedResponse,
  ReadingStatus,
  SaveProgressPayload,
  StartSessionPayload,
  EndSessionPayload,
} from '@/types'

export const readingApi = {
  // Reading List
  list: (params: { status?: ReadingStatus; per_page?: number; page?: number } = {}) =>
    http.get<PaginatedResponse<ReadingEntry>>(
      `/reading-list${buildQuery(params as Record<string, unknown>)}`,
    ),

  addToList: (bookId: number) => http.post<ReadingEntry>('/reading-list', { book_id: bookId }),

  startReading: (bookId: number, totalPages: number) =>
    http.patch<ReadingEntry>(`/reading-list/${bookId}/start`, { total_pages: totalPages }),

  updateProgress: (bookId: number, currentPage: number) =>
    http.patch<ReadingEntry>(`/reading-list/${bookId}/progress`, { current_page: currentPage }),

  removeFromList: (bookId: number) => http.delete<void>(`/reading-list/${bookId}`),

  // Progress
  getProgress: (bookId: number) => http.get<ReadingProgress>(`/books/${bookId}/progress`),

  saveProgress: (bookId: number, payload: SaveProgressPayload) =>
    http.post<{ completion_percentage: string; is_finished: string }>(
      `/books/${bookId}/progress`,
      payload,
    ),

  // Pages
  getPage: (bookId: number, pageId: number) =>
    http.get<BookPage>(`/books/${bookId}/pages/${pageId}`),

  // Sessions
  startSession: (bookId: number, payload: StartSessionPayload = {}) =>
    http.post<string>(`/books/${bookId}/sessions`, payload),

  endSession: (bookId: number, sessionId: number, payload: EndSessionPayload) =>
    http.patch<{ pages_read: string; duration_seconds: string }>(
      `/books/${bookId}/sessions/${sessionId}`,
      payload,
    ),

  // History
  history: () => http.get<ReadingHistory>('/reading/history'),
}
