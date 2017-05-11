# vim: set tabstop=8 softtabstop=8 noexpandtab:

cs:
	# Running PHP-CS-Fixer on AmazonPay/
	@php vendor/bin/php-cs-fixer fix AmazonPay/ --rules=@Symfony

	# Running PHP-CS-Fixer on Psr/
	@php vendor/bin/php-cs-fixer fix Psr/ --rules=@Symfony

	# Running PHP-CS-Fixer on tst/
	@php vendor/bin/php-cs-fixer fix tst/ --rules=@Symfony

	# Running PHP-CS-Fixer on create-amazon-pay-phar.php
	@php vendor/bin/php-cs-fixer fix create-amazon-pay-phar.php/ --rules=@Symfony
