#!/bin/bash

CURRENT_VERSION=$(cat ./version)

MINOR="${CURRENT_VERSION##*.}"

NEW_MINOR=$(("$MINOR" + 1))

echo "$NEW_MINOR"

build() {
    echo "Building version 1.0.$1"
    docker build -t sinf-website:1.0."$1" .
}

tag() {
    echo "Tagging version 1.0.$1"
    docker tag sinf-website:1.0."$1" registry.sinf.pt/website-sinf-2023/sinf-website:1.0."$1"
}

push() {
    echo "Pushing version 1.0.$1"
    docker push registry.sinf.pt/website-sinf-2023/sinf-website:1.0."$1"
}

update_version() {
    echo "1.0.$1" > ./version
}

build "$NEW_MINOR"
tag "$NEW_MINOR"
push "$NEW_MINOR"
update_version "$NEW_MINOR"
