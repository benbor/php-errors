#!/usr/bin/env bash

BASEDIR=$(dirname "$0")

function stopOnFail() {
  if [ $1 -ne 0 ];
  then
    #  exit  1;
    echo Failed. Skipped
  fi
}

$(brew --prefix PHP@7.0)/bin/php $BASEDIR/phpunit-5.5.phar --configuration=../testsPhp70/phpunit.xml
stopOnFail $?
$(brew --prefix PHP@7.1)/bin/php $BASEDIR/phpunit-5.5.phar --configuration=../testsPhp71/phpunit.xml
stopOnFail $?
$(brew --prefix PHP@7.2)/bin/php $BASEDIR/phpunit-7.5.phar --configuration=../testsPhp72/phpunit.xml
stopOnFail $?
$(brew --prefix PHP@7.3)/bin/php $BASEDIR/phpunit-7.5.phar --configuration=../testsPhp73/phpunit.xml
stopOnFail $?
$(brew --prefix PHP@7.4)/bin/php $BASEDIR/phpunit-7.5.phar --configuration=../testsPhp74/phpunit.xml
stopOnFail $?
$(brew --prefix PHP@8.0)/bin/php $BASEDIR/phpunit-9.5.phar --configuration=../testsPhp80/phpunit.xml
stopOnFail $?
$(brew --prefix PHP@8.1)/bin/php $BASEDIR/phpunit-9.5.phar --configuration=../testsPhp81/phpunit.xml
stopOnFail $?