<?php

declare(strict_types=1);

if (!function_exists('array_is_list')) {
    /**
     * @param array<mixed, mixed> $array
     *
     * @see https://github.com/symfony/polyfill/blob/1.x/src/Php81/Php81.php
     */
    function array_is_list(array $array): bool
    {
        if ([] === $array || $array === array_values($array)) {
            return true;
        }

        $nextKey = -1;

        foreach ($array as $k => $v) {
            if ($k !== ++$nextKey) {
                return false;
            }
        }

        return true;
    }
}
