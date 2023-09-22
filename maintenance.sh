#!/bin/bash

EXECUTABLE=(./sail artisan)

usage() {
    echo "maintenance on|off"
    printf '\ton\t- enables maintenance mode\n'
    printf '\toff\t- disables maintenance mode\n'
}

if [[ $# == 0 ]]; then
    usage
    exit
fi

case "$1" in
    "on")
        "${EXECUTABLE[@]}" down
        ;;

    "off")
        if [[ -n "$2" ]]; then
            "${EXECUTABLE[@]}" up --secret="$2"
        else
            "${EXECUTABLE[@]}" up
        fi
        ;;

    *)
        usage
        ;;
esac

