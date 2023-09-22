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
        if [[ -n "$2" ]]; then
            "${EXECUTABLE[@]}" down --secret="$2"
        else
            "${EXECUTABLE[@]}" down
        fi
        ;;

    "off")
        "${EXECUTABLE[@]}" up
        ;;

    *)
        usage
        ;;
esac

