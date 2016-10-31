import mintapi
import json

with open('config.json') as config:
    config = json.load(config)

for account in config['accounts']:
	mint = mintapi.Mint(account['email'], account['password'], account['session'])
	mint.initiate_account_refresh()
