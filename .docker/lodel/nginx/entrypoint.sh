#!/bin/sh

USER_ID=${LOCAL_USER_ID:-9001}

echo "Starting with UID : $USER_ID"


openssl genrsa -des3 -passout pass:password -out /etc/nginx/ssl/ssl.key 2048 -noout
openssl rsa -in /etc/nginx/ssl/ssl.key -passin pass:password -out /etc/nginx/ssl/ssl/ssl.key
openssl req -new -key /etc/nginx/ssl/ssl.key -out /etc/nginx/ssl/ssl.csr -passin pass:password \
    -subj "/C=country/ST=state/L=locality/O=organization/OU=organizationalunit/CN=commonname/emailAddress=quentin.bonaventure@openedition.org"

exec "$@"
