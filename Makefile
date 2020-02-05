.PHONY: sniff fix fix_n_sniff phpstan

# Sniff the source files.
sniff:
	vendor/bin/phpcs --colors -p --standard=PSR2 -s src

# Fix the source and test files.
fix:
	vendor/bin/phpcbf --colors -p --standard=PSR2 -s src tests

# Fix and then sniff the source files.
fix_n_sniff: cs_fix cs_sniff

# Runs phpstan on the source files.
phpstan:
	phpstan analyze src --level=7
