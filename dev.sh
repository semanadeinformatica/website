#!/bin/bash

./sail up -d laravel.test
./sail artisan queue:work
./sail npm run dev
