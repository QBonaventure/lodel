#!/bin/sh

USER_ID=${LOCAL_USER_ID:-9001}

echo "Starting with UID : $USER_ID"
#adduser -D -s /bin/false -u $USER_ID user

ssh -N -i /ssh/id_rsa -F /ssh/config -o UserKnownHostsFile=/ssh/known_hosts -L 172.31.0.30:3306:localhost:3306 calenda-devel
