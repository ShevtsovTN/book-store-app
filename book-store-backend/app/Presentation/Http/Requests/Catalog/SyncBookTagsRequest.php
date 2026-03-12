<?php

namespace App\Presentation\Http\Requests\Catalog;

use App\Domain\Catalog\Enums\AccessTypeEnum;
use App\Domain\Shared\ValueObjects\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SyncBookTagsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tag_ids'   => ['present', 'array'],
            'tag_ids.*' => ['required', 'integer', 'min:1'],
        ];
    }
}
