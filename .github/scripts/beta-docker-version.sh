#!/bin/sh

meilisearch_php_version=$(grep 'public const VERSION =' ./src/MeiliSearch.php | cut -d ' ' -f 9 | tr -d "'" | tr -d ";")
beta_feature=$(echo $meilisearch_php_version | sed -r 's/[0-9]+.[0-9]+.[0-9]+-meilisearch-//')
beta_feature=$(echo $beta_feature | sed -r 's/-beta\.[0-9]*$//')

docker_image=$(curl https://hub.docker.com/v2/repositories/getmeili/meilisearch/tags | jq | grep "$beta_feature" | head -1)
docker_image=$(echo $docker_image | grep '"name":' | cut -d ':' -f 2- | tr -d ' ' | tr -d '"' | tr -d ',')
echo $docker_image
