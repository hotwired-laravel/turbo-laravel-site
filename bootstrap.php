<?php

use TightenCo\Jigsaw\Jigsaw;

/** @var \Illuminate\Container\Container $container */
/** @var \TightenCo\Jigsaw\Events\EventBus $events */

/*
 * You can run custom code at different stages of the build process by
 * listening to the 'beforeBuild', 'afterCollections', and 'afterBuild' events.
 *
 * For example:
 *
 * $events->beforeBuild(function (Jigsaw $jigsaw) {
 *     // Your code here
 * });
 */
$highlighter = new \Tempest\Highlight\Highlighter;

// Wraps fenced code blocks in the docs "code card" chrome (language label + copy button).
$container['markdownParser']->parser = new \App\DocsMarkdownParser;

$container['markdownParser']->code_block_content_func = function ($code, $language) use ($highlighter) {
    return strtr($highlighter->parse(
        content: strtr($code, [
            "<{{'?php'}}" => '<?php',
            "{{'@'}}" => '@',
            '@{{' => '{{',
            '@{!!' => '{!!',
        ]),
        language: $language,
    ), [
        '<?php' => "<{{'?php'}}",
        '@' => "{{'@'}}",
        '{{' => '@{{',
        '{!!' => '@{!!',
    ]);
};
