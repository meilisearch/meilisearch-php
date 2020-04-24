#!/bin/sh

echo 'Setting FDs limit to 1000'
ulimit -Sn 1000

echo "Launching tests..."
vendor/bin/phpunit --color tests/
