<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Application\Catalog\Interfaces\BookCoverStorageInterface;
use App\Application\Catalog\Interfaces\BookFileStorageInterface;
use App\Domain\Catalog\Enums\AccessTypeEnum;
use App\Domain\Catalog\Enums\BookStatusEnum;
use App\Infrastructure\Persistence\Models\BookModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Fakes\FakeBookCoverStorage;
use Tests\Fakes\FakeBookFileStorage;
use Tests\TestCase;

final class BookCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coverStorage = new FakeBookCoverStorage();
        $this->fileStorage  = new FakeBookFileStorage();

        $this->instance(BookCoverStorageInterface::class, $this->coverStorage);
        $this->instance(BookFileStorageInterface::class, $this->fileStorage);
    }

    public function test_create_book_returns_201(): void
    {
        $response = $this->postJson(route('books.store'), $this->validPayload([
            'description' => 'A classic novel about love and romance.',
            'language'    => 'en'
        ]));

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'title', 'slug', 'status', 'file_links', 'cover_url',
                'access_type', 'price' => ['amount', 'currency', 'formatted'],
            ]);
    }

    public function test_create_book_persists_to_database(): void
    {
        $this->postJson(route('books.store'), $this->validPayload([
            'title' => 'Wars and Peace',
            'description' => 'A classic novel about love and romance.',
            'language'    => 'en'
        ]));

        $this->assertDatabaseHas('books', ['title' => 'Wars and Peace']);
    }

    public function test_create_book_requires_title(): void
    {
        $response = $this->postJson(route('books.store'), $this->validPayload([
            'title' => null,
            'description' => 'A classic novel about love and romance.',
            'language'    => 'en'
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_create_book_requires_valid_access_type(): void
    {
        $response = $this->postJson(route('books.store'), $this->validPayload([
            'access_type' => 'invalid',
            'description' => 'A classic novel about love and romance.',
            'language'    => 'en'
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['access_type']);
    }

    public function test_create_book_requires_valid_currency(): void
    {
        $response = $this->postJson(route('books.store'), $this->validPayload([
            'currency' => 'XXX',
            'description' => 'A classic novel about love and romance.',
            'language'    => 'en'
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['currency']);
    }

    public function test_get_book_returns_200(): void
    {
        /** @var BookModel $model */
        $model = BookModel::factory()->create();

        $response = $this->getJson(
            route('books.show', [
                'book' => $model->id,
            ])
        );

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $model->id]);
    }

    public function test_get_book_has_empty_file_links_when_no_file(): void
    {
        /** @var BookModel $model */
        $model = BookModel::factory()->create(['file_path' => null]);

        $this->getJson(route('books.show', ['book' => $model->id]))
            ->assertStatus(200)
            ->assertJsonFragment(['file_links' => []]);
    }

    public function test_get_book_has_file_link_when_file_exists(): void
    {
        /** @var BookModel $model */
        $model = BookModel::factory()->create();
        $model->update(['file_path' => "books/{$model->id}/test-book.pdf"]);

        $this->fileStorage->registerExisting("books/{$model->id}/test-book.pdf");

        $response = $this->getJson(route('books.show', ['book' => $model->id]));

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('file_links'));
        $this->assertEquals('application/pdf', $response->json('file_links.0.mime_type'));
    }

    public function test_get_book_returns_multiple_file_links(): void
    {
        /** @var BookModel $model */
        $model = BookModel::factory()->create();
        $model->update(['file_path' => "books/{$model->id}/test-book.pdf"]);

        $this->fileStorage->registerExisting("books/{$model->id}/test-book.pdf");
        $this->fileStorage->registerExisting("books/{$model->id}/test-book.epub");

        $response = $this->getJson(route('books.show', ['book' => $model->id]));

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('file_links'));
    }

    public function test_get_book_cover_url_is_null_when_no_cover(): void
    {
        /** @var BookModel $model */
        $model = BookModel::factory()->create(['cover_path' => null]);

        $this->getJson(route('books.show', ['book' => $model->id]))
            ->assertStatus(200)
            ->assertJsonFragment(['cover_url' => null]);
    }

    public function test_get_book_cover_url_when_cover_exists(): void
    {
        /** @var BookModel $model */
        $model = BookModel::factory()->create();
        $model->update(['cover_path' => "covers/{$model->id}/cover.jpg"]);

        $response = $this->getJson(route('books.show', ['book' => $model->id]));

        $this->assertStringContainsString("covers/{$model->id}/cover.jpg", $response->json('cover_url'));
    }

    public function test_get_nonexistent_book_returns_404(): void
    {
        $response = $this->getJson(
            route('books.show', [
                'book' => 99999,
            ])
        );

        $response->assertStatus(404);
    }

    public function test_list_books_returns_paginated_response(): void
    {
        BookModel::factory()
            ->count(5)
            ->create();

        $response = $this->getJson(route('books.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'per_page', 'current_page', 'total_pages'],
            ]);
    }

    public function test_list_books_filters_by_status(): void
    {
        BookModel::factory()->create(['status' => BookStatusEnum::PUBLISHED]);
        BookModel::factory()->create(['status' => BookStatusEnum::DRAFT]);

        $response = $this->getJson(
            route('books.index',
                [
                    'status' => BookStatusEnum::PUBLISHED->value,
                ]
            ));

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    public function test_list_books_filters_by_access_type(): void
    {
        BookModel::factory()->create(['access_type' => AccessTypeEnum::FREE]);
        BookModel::factory()->create(['access_type' => AccessTypeEnum::SUBSCRIPTION]);

        $response = $this->getJson(
            route('books.index',
                [
                    'access_type' => AccessTypeEnum::FREE->value,
                ]
            ));

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    public function test_update_book_returns_200(): void
    {
        /** @var BookModel $model */
        $model = BookModel::factory()->create();

        $response = $this->putJson(
            route('books.update',
                [
                    'book' => $model->id,
                ]
            ),
            $this->validPayload([
                'title' => 'Updated Title',
                'language' => 'en',
            ])
        );

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Title']);
    }

    public function test_update_book_persists_changes(): void
    {
        /** @var BookModel $model */
        $model = BookModel::factory()->create();

        $this->putJson(
            route('books.update',
                [
                    'book' => $model->id,
                ]
            ),
            $this->validPayload([
                'title' => 'New Title',
                'language' => 'en',
            ])
        );

        $this->assertDatabaseHas('books', [
            'id'    => $model->id,
            'title' => 'New Title',
        ]);
    }

    public function test_update_nonexistent_book_returns_404(): void
    {
        $response = $this->putJson(
            route('books.update',
                [
                    'book' => 999999,
                ]
            ),
            $this->validPayload([
                'title' => 'New Title',
                'language' => 'en',
            ]));

        $response->assertStatus(404);
    }

    public function test_delete_book_returns_204(): void
    {
        /** @var BookModel $model */
        $model = BookModel::factory()->create();

        $response = $this->deleteJson(route('books.destroy', ['book' => $model->id]));

        $response->assertStatus(204);
    }

    public function test_delete_book_soft_deletes_from_database(): void
    {
        /** @var BookModel $model */
        $model = BookModel::factory()->create();

        $this->deleteJson(route('books.destroy', ['book' => $model->id]));

        $this->assertSoftDeleted('books', ['id' => $model->id]);
    }

    public function test_delete_nonexistent_book_returns_404(): void
    {
        $response = $this->deleteJson(route('books.destroy', ['book' => 9999999]));

        $response->assertStatus(404);
    }

    public function test_delete_book_removes_all_files_from_storage(): void
    {
        /** @var BookModel $model */
        $model = BookModel::factory()->create();
        $model->update(['file_path' => "books/{$model->id}/test-book.pdf"]);

        $this->fileStorage->registerExisting("books/{$model->id}/test-book.pdf");
        $this->fileStorage->registerExisting("books/{$model->id}/test-book.epub");

        $this->deleteJson(route('books.destroy', ['book' => $model->id]));

        $this->assertTrue($this->fileStorage->wasDeleted("books/{$model->id}"));
        $this->assertEmpty($this->fileStorage->listFiles("books/{$model->id}"));
    }

    public function test_delete_book_removes_cover_from_storage(): void
    {
        /** @var BookModel $model */
        $model = BookModel::factory()->create([
            'cover_path' => 'covers/1/cover.jpg',
        ]);

        $this->deleteJson(route('books.destroy', ['book' => $model->id]));

        $this->assertTrue($this->coverStorage->wasDeleted('covers/1/cover.jpg'));
    }

    public function test_delete_book_removes_file_from_storage(): void
    {
        /** @var BookModel $model */
        $model = BookModel::factory()->create();
        $model->update(['file_path' => "books/{$model->id}/test-book.pdf"]);

        $this->fileStorage->registerExisting("books/{$model->id}/test-book.pdf");

        $this->deleteJson(route('books.destroy', ['book' => $model->id]));

        $this->assertTrue($this->fileStorage->wasDeleted("books/{$model->id}")); // ← динамический id
        $this->assertEmpty($this->fileStorage->listFiles("books/{$model->id}"));
    }

    public function test_delete_book_with_no_files_does_not_fail(): void
    {
        /** @var BookModel $model */
        $model = BookModel::factory()->create([
            'cover_path' => null,
            'file_path'  => null,
        ]);

        $this->deleteJson(route('books.destroy', ['book' => $model->id]))
            ->assertStatus(204);

        $this->assertEmpty($this->coverStorage->getDeleted());
        $this->assertEmpty($this->fileStorage->getDeleted());
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title'       => 'Master and Margarita',
            'access_type' => AccessTypeEnum::FREE->value,
            'price'       => 0,
            'currency'    => 'EUR',
        ], $overrides);
    }
}
