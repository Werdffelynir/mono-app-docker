#!/bin/bash

if [ -f .env ]
then
  export $(cat .env | sed 's/#.*//g' | xargs)
fi

if [ -z $APP_URL ]
then
  export APP_URL=localhost
fi

if [ -z $APP_PORT ]
then
  export APP_PORT=3001
fi

echo 'Local Development Server Start'

cd ./public && php -S $APP_URL:$APP_PORT
