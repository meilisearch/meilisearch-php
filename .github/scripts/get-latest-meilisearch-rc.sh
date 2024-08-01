#!/bin/bash

# This script is used in the SDK CIs to fetch the latest Meilisearch RC name (ex: v0.16.0rc2).
# The Meilisearch RC is needed when maintainers are developing features during the pre-release week because the final Meilisearch release is not out yet.

temp_file='temp_file' # temp_file needed because `grep` would start before the download is over
curl -s 'https://api.github.com/repos/meilisearch/meilisearch/releases' > "$temp_file"
latest_rc_release=$(cat "$temp_file" \
    | grep -E 'tag_name' | grep 'rc' | head -1 \
    | tr -d ',"' | cut -d ':' -f2 | tr -d ' ')
rm -rf "$temp_file"
echo "$latest_rc_release"
