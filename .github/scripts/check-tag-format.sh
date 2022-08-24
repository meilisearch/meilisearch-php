#!/bin/sh

meilisearch_php_version=$(grep 'public const VERSION =' src/MeiliSearch.php | cut -d ' ' -f 9 | tr -d "'" | tr -d ";")
is_bump_beta=$1

if [ $is_bump_beta = true ]; then
    meilisearch_php_version="0.1.0-bump-meilisearch-v0.28.0-beta.0"
    # Works with the format X.X.X-bump-meilisearch-vX.X.X-beta.X
    #
    # Examples of correct format:
    # 0.1.0-bump-meilisearch-v0.28.0-beta.0
    echo "$meilisearch_php_version" | grep -E "[0-9]*\.[0-9]*\.[0-9]*-bump-meilisearch-v[0-9]*\.[0-9]*\.[0-9]*-beta\.[0-9]*$"
    if [ $? != 0 ]; then
        echo "Error: Your pre-release beta tag in src/Meilisearch.php: $meilisearch_php_version is wrongly formatted."
        echo 'Please refer to the contributing guide for help.'
        exit 1
    fi

elif [ $is_bump_beta = false ]; then
    # Works with the format X.X.X-xxx-beta.X
    # none or multiple -xxx are valid
    #
    # Examples of correct format:
    # 0.1.0-beta.0
    # 0.1.0-xxx-beta.0
    # 0.1.0-xxx-xxx-beta.0
    echo "$meilisearch_php_version" | grep -E "[0-9]*\.[0-9]*\.[0-9]*-([a-z]*-)*beta\.[0-9]*$"
    if [ $? != 0 ]; then
        echo "Error: Your beta tag in src/Meilisearch.php: $meilisearch_php_version is wrongly formatted."
        echo 'Please refer to the contributing guide for help.'
        exit 1
    fi
fi

exit 0
