#!/bin/bash

EXECUTABLE=(./sail artisan)

usage() {
    echo "migrate up|down|fresh"
    echo -e '\tup\t- runs the provided migrations'
    echo -e '\tdown\t- rolls back the changes made by the provided migrations'
    echo -e '\tfresh\t- drops all tables and runs all migrations'
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
    "fresh")
        "${EXECUTABLE[@]}" migrate:fresh
        ;;
    *)
        usage
        ;;
esac

