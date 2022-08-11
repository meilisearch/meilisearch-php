#!/bin/sh

# current_tag=$(echo $GITHUB_REF | cut -d '/' -f 3 | tr -d ' ',v)
meilisearch_php_version=$(grep 'public const VERSION =' ./src/MeiliSearch.php | cut -d ' ' -f 9 | tr -d "'" | tr -d ";")
composer_version=$(grep '"version":' composer.json | cut -d ':' -f 2- | tr -d ' ' | tr -d '"' | tr -d ',')

# Works with the format vX.X.X-xxx-beta.X
# none or multiple -xxx are valid
#
# Examples of correct format:
# v0.1.0-beta.0
# v0.1.0-xxx-beta.0
# v0.1.0-xxx-xxx-beta.0
echo "$meilisearch_php_version" | grep -E "[0-9]*\.[0-9]*\.[0-9]*-([a-z]*-)*beta\.[0-9]*$"
if [ $? != 0 ]; then
    echo "Error: Your beta tag in src/Meilisearch.php: $meilisearch_php_version is wrongly formatted."
    echo 'Please refer to the contributing guide for help.'
    exit 1
fi

echo "$composer_version" | grep -E "[0-9]*\.[0-9]*\.[0-9]*-([a-z]*-)*beta\.[0-9]*$"
if [ $? != 0 ]; then
    echo "Error: Your beta tag in composer.json: $composer_version is wrongly formatted or is missing."
    echo 'Please refer to the contributing guide for help.'
    exit 1
fi
exit 0
