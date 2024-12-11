<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude([
        'media',
        'node_modules',
        'coverage',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS2.0' => true,
    ])
    ->setFinder($finder);
