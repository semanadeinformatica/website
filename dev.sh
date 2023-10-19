#!/bin/bash

./sail up -d laravel.test

./sail artisan queue:work &
worker=$!

./sail npm run dev &
vite=$!

trap 'kill "$worker"; kill "$vite"; ./sail down' SIGINT
while kill -s 0 "$worker" || kill -s 0 "$vite"; do
    wait "$worker"
    wait "$vite"
done &>/dev/null
