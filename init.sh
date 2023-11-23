#!/bin/bash

function check_dependencies ()
{
    if [ ! "$(docker ps -q -f name=logger)" ]; then
        echo "#############################################################################"
        echo "###                                                                       ###"
        echo "### WARNING: Logger is not running, please setup.                         ###"
        echo "### https://stash.mgcorp.co/projects/pbngbe/repos/logger/browse/README.md ###"
        echo "###                                                                       ###"
        echo "#############################################################################"
        exit 0
    fi
}

function setup_dot_env ()
{
    if [ ! -f lumen/.env ]; then
        if [[ -f lumen/.env.example ]]; then
            cp lumen/.env.example lumen/.env
        else
            echo "#########################################################################################"
            echo "###                                                                                   ###"
            echo "### WARNING: .env.example file does not exist and it's required for the proper setup! ###"
            echo "###                                                                                   ###"
            echo "#########################################################################################"
            exit 0
        fi
    fi
}

function docker_pull_and_up ()
{
    docker-compose pull
    docker-compose up -d
}

function composer_install ()
{
    if [ ! -d "vendor" ]; then
        docker run --rm -v /${PWD}:/app neatous/composer-prestissimo install --ignore-platform-reqs --prefer-dist --no-suggest
    fi
}

function run ()
{
    check_dependencies
    setup_dot_env
    docker_pull_and_up
    composer_install
}

run
