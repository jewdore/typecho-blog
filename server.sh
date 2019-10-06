#!/usr/bin/env bash
[[ $1 == true ]]  && export XDEBUG_CONFIG="idekey=PHPSTORM"
env | grep idekey
php -S 0.0.0.0:1002 -t .