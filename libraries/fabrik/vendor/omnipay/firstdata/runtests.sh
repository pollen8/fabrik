#!/bin/sh

#
# Command line runner for unit tests for composer projects
# (c) Del 2015 http://www.babel.com.au/
# No Rights Reserved
#

#
# Ensure that dependencies are installed (including codeception and phpunit)
#
if [ -f composer.lock ]; then
    /usr/local/bin/composer install
else
    /usr/local/bin/composer update
fi

#
# Clean up after any previous test runs
#
mkdir -p documents
rm -rf documents/coverage-html-new
rm -f documents/coverage.xml

#
# Run phpunit
#
vendor/bin/phpunit --coverage-html documents/coverage-html-new --coverage-clover documents/coverage.xml --log-junit documents/phpunit.xml

if [ -d documents/coverage-html-new ]; then
  rm -rf documents/coverage-html
  mv documents/coverage-html-new documents/coverage-html
fi

