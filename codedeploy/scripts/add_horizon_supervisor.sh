#!/bin/bash

FILENAME=horizon.conf

cp /var/www/api/codedeploy/supervisor/$FILENAME /etc/supervisor/conf.d/$FILENAME

supervisorctl reread
supervisorctl update
supervisorctl start horizon