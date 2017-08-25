#!/bin/sh

USER_ID=${LOCAL_USER_ID:-9001}

echo "Starting with UID : $USER_ID"
adduser -D -s /bin/false -u $USER_ID user


exec php-fpm
