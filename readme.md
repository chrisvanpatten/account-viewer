# Account Viewer

A small PHP app to view financial accounts and balances. Works with data from [mintapi](https://github.com/mrooney/mintapi).

## Setup

1. Set up [mintapi](https://github.com/mrooney/mintapi) on your server
2. Rename "config.example.json" to "config.json" and update your account credentials
3. Set up the refresh-mint.py script with a cron job (optional)
4. Point your webroot to public/

## Usage

### Viewer

The viewer can be accessed at the URL you set up this script.

### Alexa

The `/api/alexa` path allows you to configure a private Alexa skill so you can ask for bank balances via speech.

### API

The `/api/accounts` path 

## License
