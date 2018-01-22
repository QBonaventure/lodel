#!/bin/sh

ssh -N -i /ssh/id_rsa -F /ssh/config -o UserKnownHostsFile=/ssh/known_hosts -L 172.30.0.30:3306:localhost:3306 calenda-devel
