#!/usr/bin/env bash

set -e

vendor/bin/phpstan analyse
vendor/bin/phpunit --testsuite unit -v
vendor/bin/behat --suite acceptance -vvv --tags '~@ignore' --snippets-for="Test\Acceptance\FeatureContext"
