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
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
