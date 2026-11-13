<?php

namespace App;

use TightenCo\Jigsaw\Parsers\JigsawMarkdownParser;

/**
 * Wraps every fenced code block in the docs "code card" chrome:
 * a language label and a copy-to-clipboard button.
 */
class DocsMarkdownParser extends JigsawMarkdownParser
{
    protected function _doFencedCodeBlocks_callback($matches)
    {
        $language = ltrim($matches[2], '.');
        $attrs = $matches[3];
        $codeblock = rtrim($matches[4], "\n");

        $codeblock = $this->code_block_content_func
            ? call_user_func($this->code_block_content_func, $codeblock, $language)
            : htmlspecialchars($codeblock, ENT_NOQUOTES);

        $codeblock = preg_replace_callback('/^\n+/', [$this, '_doFencedCodeBlocks_newlines'], $codeblock);

        $classes = $language !== '' ? [$this->code_class_prefix.$language] : [];
        $attr = $this->doExtraAttributes('code', $attrs, null, $classes);

        $label = $language !== ''
            ? '<span class="code-card-lang">'.htmlspecialchars($language).'</span>'
            : '';

        $html = <<<HTML
<div class="code-card group" data-controller="clipboard">
<div class="code-card-tools">{$label}<button type="button" class="code-card-copy" data-action="clipboard#copy" aria-label="Copy code">
<svg class="group-data-copied:hidden" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="8.5" y="8.5" width="11" height="11" rx="2"></rect><path d="M15.5 8.5V6.5a2 2 0 0 0-2-2h-7a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h2"></path></svg>
<svg class="hidden group-data-copied:block" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg>
</button></div>
<pre><code{$attr}>{$codeblock}</code></pre>
</div>
HTML;

        return "\n\n".$this->hashBlock($html)."\n\n";
    }
}
