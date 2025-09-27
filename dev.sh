#!/usr/bin/env bash

# Peras: I have reworked this script to ensure future developers have a full, self-contained development environment by running it.
# The includes:
# - Checking for the existance of a '.env' file to hold environment variables needed for the first app bootstrap (building the images).
# - 'sail up' command to start the Laravel Sail environment.
# - Running the available migrations. The DB holds a special table to keep track of what has been migrated so they don't always run.
# - Seeding the application with dummy data
# - 'sail npm install' to install Node.js dependencies on the running container. Since there is a volume attached pointing to the CWD, this will also create the 'node_modules' folder in you computer.

if [[ ! -f .env ]]; then
    echo "Missing .env file needed to run app. This file is already gitignored so you can create it without it being versioned."
    exit 1
fi

./sail up -d laravel.test # Peras: this already spins up the DB service so no need to have a separate command for that

# Peras: 'fresh' does a clean, full migration of the DB. For now I will just keep the 'up' variant and leave the 'fresh' variant commented out
./migrate.sh up
# ./migrate.sh fresh

# Peras: insert dummy data into the database
./seed.sh

./sail artisan queue:work &
worker=$!

# ensure we have Vite available
# Peras: I chose to do a blank dependency installation here to account for missing or broken dependencies
rm -rf node_modules
./sail npm install

./sail npx puppeteer browsers install chrome

./sail npm run dev &
vite=$!

trap 'kill "$worker"; kill "$vite"; ./sail down' SIGINT
while kill -s 0 "$worker" || kill -s 0 "$vite"; do
    wait "$worker"
    wait "$vite"
done &>/dev/null