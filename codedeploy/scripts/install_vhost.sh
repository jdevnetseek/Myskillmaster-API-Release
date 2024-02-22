#!/bin/bash

FILENAME=api.conf
SERVER_NAME=api.staging.myskillmaster.com
SERVER_LOGS=apistaging

if [[ "$DEPLOYMENT_GROUP_NAME" == *-dev-asg ]]
then
    # this only executes when only in development asg
    SERVER_NAME=api.myskillmaster.appetiserdev.tech
    SERVER_LOGS=apidevelopment
elif [[ "$DEPLOYMENT_GROUP_NAME" == *-prod-asg ]]
then
    # this only executes when only in production asg
    SERVER_NAME=api.myskillmaster.com
    SERVER_LOGS=apiproduction
fi


cp /var/www/api/codedeploy/nginx/vhost.conf /etc/nginx/sites-available/$FILENAME

ln -sf /etc/nginx/sites-available/$FILENAME /etc/nginx/sites-enabled/$FILENAME

sed -i -e "s/{{SERVER_NAME}}/$SERVER_NAME/g" /etc/nginx/sites-available/$FILENAME

sed -i -e "s/{{SERVER_LOGS}}/$SERVER_LOGS/g" /etc/nginx/sites-available/$FILENAME

mkdir -p /var/log/nginx/${SERVER_LOGS}/
