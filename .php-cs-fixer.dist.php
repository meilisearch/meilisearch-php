<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->append([__FILE__]);

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
        'void_return' => true,
        'native_function_invocation' => ['include' => ['@compiler_optimized'], 'scope' => 'namespaced'],
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'php_unit_strict' => true,
        // @todo: when we'll support only PHP 8.0 and upper, we can enable `parameters` for `trailing_comma_in_multiline` rule
        'trailing_comma_in_multiline' => ['after_heredoc' => true, 'elements' => ['array_destructuring', 'arrays', 'match'/* , 'parameters' */]],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
