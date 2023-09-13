#!/bin/bash

EXECUTABLE=(./sail artisan)

usage() {
    echo "migrate up|down"
    printf '\tup\t- runs the provided migrations\n'
    printf '\tdown\t- rolls back the changes made by the provided migrations\n'
}

if [[ $# == 0 ]]; then
    usage
    exit
fi

case "$1" in
    "up")
        "${EXECUTABLE[@]}" migrate
        ;;
    "down")
        "${EXECUTABLE[@]}" migrate:rollback
        ;;
    *)
        usage
        ;;
esac

