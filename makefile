export COMPOSER_ALLOW_SUPERUSER=1

default: # Commande make par défaut
	@make -s help

help: # Aide : Liste les commandes possibles
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

update-binaries: ## recupère les derniers binaires
	rm -fr bin
	wget -c -P bin https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-linux-x64
	chmod +x bin/tailwindcss-linux-x64