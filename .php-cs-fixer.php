<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['tools', 'vendor'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        '@PHP8x4Migration' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder)
;