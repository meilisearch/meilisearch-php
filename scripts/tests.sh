#!/bin/sh

echo 'Setting FDs limit to 10000'
ulimit -Sn 10000

echo "Launching tests..."
vendor/bin/phpunit --color tests/ "$@"

exit $?
