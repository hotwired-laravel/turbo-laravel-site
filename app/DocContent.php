<?php

namespace App;

use Illuminate\Support\Str;

/**
 * Splits a rendered docs/bootcamp page into the pieces the layout needs:
 * the title (the page's <h1>), the body, and an "On this page" outline.
 */
class DocContent
{
    /**
     * @param  array<int, array{id: string, text: string}>  $toc
     */
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $number,
        public readonly string $body,
        public readonly array $toc,
    ) {}

    public static function make(string $html): self
    {
        $title = null;
        $number = null;

        if (preg_match('#<h1[^>]*>(.*?)</h1>#s', $html, $match)) {
            $title = trim($match[1]);
            $number = static::chapterNumber($html);
            $html = str_replace($match[0], '', $html);
        }

        $toc = [];
        $used = [];

        $html = preg_replace_callback('#<h([23])([^>]*)>(.*?)</h\1>#s', function ($match) use (&$toc, &$used) {
            [, $level, $attrs, $inner] = $match;
            $text = html_entity_decode(trim(strip_tags($inner)), ENT_QUOTES | ENT_HTML5);

            if (preg_match('/\bid="([^"]+)"/', $attrs, $id)) {
                $id = $id[1];
            } else {
                $id = $base = Str::slug($text) ?: 'section';

                for ($i = 2; isset($used[$id]); $i++) {
                    $id = "{$base}-{$i}";
                }

                $attrs .= " id=\"{$id}\"";
            }

            $used[$id] = true;

            if ($level === '2') {
                $toc[] = ['id' => $id, 'text' => $text];
            }

            return "<h{$level}{$attrs}>{$inner}</h{$level}>";
        }, $html);

        return new self($title, $number, $html, $toc);
    }

    /**
     * Bootcamp chapters are titled like "# *04.* Editing Chirps".
     */
    public static function chapterNumber(string $html): ?string
    {
        return preg_match('#<h1[^>]*>\s*<em>\s*([\d.]+)\s*</em>#', $html, $match) ? $match[1] : null;
    }

    public function plainTitle(): string
    {
        return trim(html_entity_decode(strip_tags(preg_replace('#<em>\s*[\d.]+\s*</em>#', '', $this->title ?? '')), ENT_QUOTES | ENT_HTML5));
    }
}
