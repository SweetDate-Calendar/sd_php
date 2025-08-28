<?php

$finder = PhpCsFixer\Finder::create()
  ->in(__DIR__ . '/src')
  ->in(__DIR__ . '/tests')
  ->name('*.php')
  ->ignoreVCSIgnored(true);

return (new PhpCsFixer\Config())
  ->setRiskyAllowed(false)
  ->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'single_quote' => true,
    'no_unused_imports' => true,
    'no_trailing_whitespace' => true,
    'blank_line_before_statement' => ['statements' => ['return']],
    'ordered_imports' => true,
    'phpdoc_trim' => true,
  ])
  ->setFinder($finder);
