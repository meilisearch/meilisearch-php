#!/bin/sh

echo "Launching linting in fix mode..."
vendor/bin/php-cs-fixer fix -v --config=.php-cs-fixer.dist.php --using-cache=no --allow-risky=yes

