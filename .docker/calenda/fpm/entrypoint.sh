#!/bin/sh

USER_ID=${LOCAL_USER_ID:-9001}


cp /code/lodelconfig-default.php /code/lodelconfig.php
sed -i "s|exit()|// exit()|g" /code/lodelconfig.php
sed -i "s/^\$cfg\['database'\] = '';$/\$cfg['database'] = '$MYSQL_DATABASE';/g" /code/lodelconfig.php
sed -i "s/^\$cfg\['dbusername'\] = '';$/\$cfg['dbusername'] = '$MYSQL_USER';/g" /code/lodelconfig.php
sed -i "s/^\$cfg\['dbpasswd.*$/\$cfg['dbpasswd'] = '$MYSQL_PASSWORD';/g" /code/lodelconfig.php
sed -i "s/^\$cfg\['dbhost.*$/\$cfg['dbhost'] = '172.31.0.30';/g" /code/lodelconfig.php
sed -i "s/^\$cfg\['debugMode.*$/\$cfg['debugMode'] = $DEBUG_MODE;/g" /code/lodelconfig.php

sed -i "s/;security.limit_extensions = .php .php3 .php4 .php5/security.limit_extensions = .php .html/" /usr/local/etc/php-fpm.d/www.conf

sed -i '$ a\php_value[include_path]  = /code/lodel/scripts' test /usr/local/etc/php-fpm.d/www.conf

chmod 664 /code/lodelconfig.php
chown $USER_ID:$USER_ID /code/lodelconfig.php

composer update -d /code

keyfile=$(sed -n 's/\$cfg\['\''install_key'\''] = '\''\(.*\)'\'';/\1/p' /code/lodelconfig.php)
echo $keyfile
touch /code/$keyfile
chmod 664 /code/$keyfile
chown $USER_ID:$USER_ID /code/$keyfile



exec php-fpm
