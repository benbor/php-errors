#!/usr/bin/env bash

BASEDIR=$(dirname "$0")

function stopOnFail() {
  if [ $1 -ne 0 ];
  then
      exit  1;
#    echo Failed. Skipped
  fi
}

function runFromBrew() {
  php_brew_version=$1
  php_unit=$2;

  php_test_version=$(echo $php_brew_version | sed 's/\.//g' | sed 's/\@//g')

  command="PHPERRORS_PHP_TEST_VERSION=$php_test_version $(brew --prefix $php_brew_version)/bin/php $BASEDIR/$php_unit.phar --configuration=../testsPhp/phpunit.xml"
  echo "Execute:"
  echo "$command"
  eval "$command"
  stopOnFail $?
}

runFromBrew 'PHP@7.0' 'phpunit-5.5'
runFromBrew 'PHP@7.1' 'phpunit-5.5'
runFromBrew 'PHP@7.2' 'phpunit-7.5'
runFromBrew 'PHP@7.3' 'phpunit-7.5'
runFromBrew 'PHP@7.4' 'phpunit-7.5'
runFromBrew 'PHP@8.0' 'phpunit-9.5'
runFromBrew 'PHP@8.1' 'phpunit-9.5'