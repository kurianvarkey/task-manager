#!/bin/sh

php artisan test --profile --coverage --min=80 --coverage-html=tests/report/coverage
#php artisan test --testsuite=Feature --stop-on-failure