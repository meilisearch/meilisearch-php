#!/bin/sh

echo "Launching linting..."
vendor/bin/php-cs-fixer fix -v --config=.php-cs-fixer.dist.php --using-cache=no --dry-run --allow-risky=yes

