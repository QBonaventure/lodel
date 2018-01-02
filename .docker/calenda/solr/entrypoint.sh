#!/bin/sh

ssh -N -i /ssh/id_rsa -F /ssh/config -o UserKnownHostsFile=/ssh/known_hosts -L 172.31.0.40:8983:localhost:8983 calenda-devel
