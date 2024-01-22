<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.DIRECTORY_SEPARATOR.'src')
    ->in(__DIR__.DIRECTORY_SEPARATOR.'tests')
    ->append(['.php_cs.dist']);

$rules = [
    '@Symfony' => true,
    'declare_strict_types' => true,
    'void_return' => true,
    'native_function_invocation' => ['include' => ['@compiler_optimized'], 'scope' => 'namespaced'],
    'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
];

$config = new PhpCsFixer\Config();

return $config
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setFinder($finder);
