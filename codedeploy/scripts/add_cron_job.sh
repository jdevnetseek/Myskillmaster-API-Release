#!/bin/bash
echo "* * * * *		www-data 	cd /var/www/api && php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/app-cron