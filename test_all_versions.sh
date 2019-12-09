#!/bin/bash

# docker run --rm -v $(pwd):/app -w /app composer install

function displayTitle {
    echo -e "\e[1m*** Testing with \e[92m$1\e[39m\e[0m ***"
}
function version_gt() {
    test "$(printf '%s\n' "$@" | sort -V | head -n 1)" != "$1";
}
echo Checking Docker is running...
docker version
docker_status=$?
if test $docker_status -gt 0
then
    echo "Error: Docker is not running correctly ???"
    exit 1
fi
# i=0

# while [ $? == 0 ]
# do
#    i=$((i+1))
#    curl https://registry.hub.docker.com/v2/repositories/library/php/tags/?page=$i 2>/dev/null|jq '."results"[]["name"]'

# done
# exit
COMPOSER_CURRENT=$(composer show 'phpunit/phpunit' | grep -Eow "[0-9\.]+" -m 1 )
# if version_gt $COMPOSER_CURRENT "6.5"; then
#     composer remove phpunit/phpunit theseer/phpdox
#     composer require phpunit/phpunit:^6.5
# fi
declare -A versions
# 7.key.value
# versions[0]=33
versions[1]=33
versions[2]=25
versions[3]=12
versions[4]=0

onlyMinor=1

command="vendor/bin/phpunit \
                            --configuration ./configuration/phpunit.xml \
                            --testsuite All --no-coverage --columns 160 --disallow-test-output"
                            # --testsuite units --no-coverage --columns 160 --disallow-test-output --no-logging"

for minor in "${!versions[@]}"
do
    echo "********************* testing PHP 7.$minor ***************************"
    tag="cli-alpine"
    if test $minor -eq 0
    then
        tag="cli"
    fi
    if [ $onlyMinor != 1 ]
    then
        for patch in `seq 0 ${versions[$minor]}`
        do
            displayTitle "7.$minor.$patch"
            docker run --rm -v $(pwd):/app -w /app php:7.$minor.$patch-$tag $command
        done
    else
        patch=${versions[$minor]}
        displayTitle "7.$minor.$patch"
        docker run --rm -v $(pwd):/app -w /app php:7.$minor.$patch-$tag $command
    fi
done

# php composer.phar self-update 1.7.0
# for v in "${versions[@]}"
# do
#     echo -e "\e[1m*** Testing with \e[92m$v\e[39m\e[0m ***"
#     docker run --rm -v $(pwd):/app -w /app php:$v vendor/bin/phpunit --configuration ./configuration/phpunit.xml --testsuite All --no-coverage --columns 160 --disallow-test-output --do-not-cache-result --no-logging
# done
# php composer.phar self-update 1.9.0
#
# if version_gt $COMPOSER_CURRENT "6.5"; then
#     composer self-update
#     composer require --dev theseer/phpdox
#     composer update phpunit/phpunit
# fi