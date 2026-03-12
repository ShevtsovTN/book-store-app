<?php

namespace App\Domain\Reading\ValueObjects;

final readonly class ReadingSettings
{
    public function __construct(
        public int    $fontSize,        // px: 12–24
        public string $fontFamily,      // Georgia|Arial|Roboto
        public float  $lineHeight,      // 1.2–2.0
        public string $theme,           // light|dark|sepia
        public int    $pageWidth,       // % контейнера: 50–100
        public string $paginationMode,  // page|scroll
        public int    $wordsPerPage,    // 100–500
    ) {
        $this->validate();
    }

    public static function default(): self
    {
        return new self(
            fontSize:       16,
            fontFamily:     'Georgia',
            lineHeight:     1.6,
            theme:          'light',
            pageWidth:      70,
            paginationMode: 'page',
            wordsPerPage:   300,
        );
    }

    public function withTheme(string $theme): self
    {
        return new self(
            fontSize:       $this->fontSize,
            fontFamily:     $this->fontFamily,
            lineHeight:     $this->lineHeight,
            theme:          $theme,
            pageWidth:      $this->pageWidth,
            paginationMode: $this->paginationMode,
            wordsPerPage:   $this->wordsPerPage,
        );
    }

    public function withFontSize(int $fontSize): self
    {
        return new self(
            fontSize:       $fontSize,
            fontFamily:     $this->fontFamily,
            lineHeight:     $this->lineHeight,
            theme:          $this->theme,
            pageWidth:      $this->pageWidth,
            paginationMode: $this->paginationMode,
            wordsPerPage:   $this->wordsPerPage,
        );
    }

    public function toArray(): array
    {
        return [
            'font_size'       => $this->fontSize,
            'font_family'     => $this->fontFamily,
            'line_height'     => $this->lineHeight,
            'theme'           => $this->theme,
            'page_width'      => $this->pageWidth,
            'pagination_mode' => $this->paginationMode,
            'words_per_page'  => $this->wordsPerPage,
        ];
    }

    private function validate(): void
    {
        if ($this->fontSize < 12 || $this->fontSize > 24) {
            throw new \InvalidArgumentException('fontSize must be from 12 to 24');
        }

        if (!in_array($this->theme, ['light', 'dark', 'sepia'])) {
            throw new \InvalidArgumentException('Invalid theme: ' . $this->theme);
        }

        if (!in_array($this->paginationMode, ['page', 'scroll'])) {
            throw new \InvalidArgumentException('Invalid pagination mode: ' . $this->paginationMode);
        }

        if ($this->wordsPerPage < 100 || $this->wordsPerPage > 500) {
            throw new \InvalidArgumentException('wordsPerPage must be from 100 to 500');
        }
    }
}
