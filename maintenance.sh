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
    "on"|"off")

        STATE="up"

        if [[ "$1" == "on" ]]; then
            STATE="down"
        fi

        "${EXECUTABLE[@]}" "$STATE"
        ;;
    *)
        usage
        ;;
esac

