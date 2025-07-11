# Setup guide

## Running the project in development mode

- Clone this repository by running `git clone https://github.com/NIAEFEUP/enei-website.git`.
- Navigate to the newly created folder and copy the `env.example` file, renaming it `.env`.
- Assuming you've already installed PHP, Composer and Node.js, simply run `./dev.sh` and access `localhost:8000`: the website should be available.

## Troubleshooting

### General tips

In case you come up with some unforeseen errors that you simply cannot find out how to solve, clearing the config cache and removing old volumes with `php artisan cache:clear` and `./sail down --rmi -v` respectively should help: the error might come from old cache or volumes.

### "*Your requirements could not be resolved to an installable set of packages.*"

This error might appear after `dev.sh` runs `composer update` when your device is missing a crucial PHP package. Look for a line like this:

`- league/mime-type-detection[1.4.0, ..., 1.15.0] require ext-fileinfo * -> it is missing from your system. Install or enable PHP's fileinfo extension.`

The extension can be different, but in this case, you should install the `php8-fileinfo` package or enable it in your `php.ini` file (located at `/etc/php/<version>/cli/` or `/etc/php<version>/cli/`) by removing the semicolon in `;extension=fileinfo`, in case it is already installed.