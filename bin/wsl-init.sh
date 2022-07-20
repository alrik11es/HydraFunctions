#!/usr/bin/env bash

export DEBIAN_FRONTEND=noninteractive

echo "What is your WSL user name? [web]"
read WSL_USER_NAME
WSL_USER_NAME=${WSL_USER_NAME:-web}

echo "What is your WSL user group? (Same as username if you're unsure) [web]"
read WSL_USER_GROUP
WSL_USER_GROUP=${WSL_USER_GROUP:-web}

sudo useradd -m -d /home/$WSL_USER_NAME $WSL_USER_NAME -s /bin/bash

# Update Package List
apt-get update

# Update System Packages
apt-get upgrade -y

# Force Locale
echo "LC_ALL=en_US.UTF-8" >> /etc/default/locale
locale-gen en_US.UTF-8

apt-get install -y software-properties-common curl

# Install Some PPAs
apt-add-repository ppa:ondrej/php -y
apt-add-repository ppa:chris-lea/redis-server -y
# NodeJS
#curl -sL https://deb.nodesource.com/setup_14.x | sudo -E bash -

## Update Package Lists
apt-get update

# Install Some Basic Packages
apt-get install -y build-essential dos2unix gcc git git-lfs libmcrypt4 libpcre3-dev libpng-dev unzip make \
python3-pip re2c supervisor unattended-upgrades whois vim libnotify-bin pv mcrypt bash-completion zsh imagemagick

## Set My Timezone
#ln -sf /usr/share/zoneinfo/UTC /etc/localtime

# Install Generic PHP packages
apt-get install -y --allow-change-held-packages \
php-imagick php-memcached php-redis php-dev php-swoole

# PHP 8.0
apt-get install -y --allow-change-held-packages \
php8.0 php8.0-bcmath php8.0-bz2 php8.0-cgi php8.0-cli php8.0-common php8.0-curl php8.0-dba php8.0-dev \
php8.0-enchant php8.0-fpm php8.0-gd php8.0-gmp php8.0-imap php8.0-interbase php8.0-intl php8.0-ldap \
php8.0-mbstring php8.0-mysql php8.0-odbc php8.0-opcache php8.0-pgsql php8.0-phpdbg php8.0-pspell php8.0-readline \
php8.0-snmp php8.0-soap php8.0-sqlite3 php8.0-sybase php8.0-tidy php8.0-xml php8.0-xsl php8.0-zip

# Fixed php fpm bind listening socket - no such file issue.
mkdir -p /run/php
touch /run/php/php8.0-fpm.sock

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chown -R $WSL_USER_NAME:$WSL_USER_NAME /home/$WSL_USER_NAME/.config

# Set Some PHP CLI Settings
sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php/8.0/cli/php.ini
sed -i "s/display_errors = .*/display_errors = On/" /etc/php/8.0/cli/php.ini
sed -i "s/memory_limit = .*/memory_limit = 512M/" /etc/php/8.0/cli/php.ini
sed -i "s/;date.timezone.*/date.timezone = UTC/" /etc/php/8.0/cli/php.ini

# Install Nginx
apt-get install -y --allow-downgrades --allow-remove-essential --allow-change-held-packages nginx

#rm /etc/nginx/sites-enabled/default
#rm /etc/nginx/sites-available/default

# Create a configuration file for Nginx overrides.
mkdir -p /home/$WSL_USER_NAME/.config/nginx
chown -R $WSL_USER_NAME:$WSL_USER_GROUP /home/$WSL_USER_NAME
touch /home/$WSL_USER_NAME/.config/nginx/nginx.conf
ln -sf /home/$WSL_USER_NAME/.config/nginx/nginx.conf /etc/nginx/conf.d/nginx.conf

# Setup Some PHP-FPM Options
echo "opcache.revalidate_freq = 0" >> /etc/php/8.0/mods-available/opcache.ini

sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php/8.0/fpm/php.ini
sed -i "s/display_errors = .*/display_errors = On/" /etc/php/8.0/fpm/php.ini
sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" /etc/php/8.0/fpm/php.ini
sed -i "s/memory_limit = .*/memory_limit = 512M/" /etc/php/8.0/fpm/php.ini
sed -i "s/upload_max_filesize = .*/upload_max_filesize = 100M/" /etc/php/8.0/fpm/php.ini
sed -i "s/post_max_size = .*/post_max_size = 100M/" /etc/php/8.0/fpm/php.ini
sed -i "s/;date.timezone.*/date.timezone = UTC/" /etc/php/8.0/fpm/php.ini

printf "[openssl]\n" | tee -a /etc/php/8.0/fpm/php.ini
printf "openssl.cainfo = /etc/ssl/certs/ca-certificates.crt\n" | tee -a /etc/php/8.0/fpm/php.ini

printf "[curl]\n" | tee -a /etc/php/8.0/fpm/php.ini
printf "curl.cainfo = /etc/ssl/certs/ca-certificates.crt\n" | tee -a /etc/php/8.0/fpm/php.ini

# Set The Nginx & PHP-FPM User
sed -i "s/user www-data;/user $WSL_USER_NAME;/" /etc/nginx/nginx.conf
sed -i "s/# server_names_hash_bucket_size.*/server_names_hash_bucket_size 64;/" /etc/nginx/nginx.conf

sed -i "s/user = www-data/user = $WSL_USER_NAME/" /etc/php/8.0/fpm/pool.d/www.conf
sed -i "s/group = www-data/group = $WSL_USER_NAME/" /etc/php/8.0/fpm/pool.d/www.conf

sed -i "s/listen\.owner.*/listen.owner = $WSL_USER_NAME/" /etc/php/8.0/fpm/pool.d/www.conf
sed -i "s/listen\.group.*/listen.group = $WSL_USER_NAME/" /etc/php/8.0/fpm/pool.d/www.conf
sed -i "s/;listen\.mode.*/listen.mode = 0666/" /etc/php/8.0/fpm/pool.d/www.conf

service nginx restart
service php8.0-fpm restart

# Add $WSL_USER_NAME User To WWW-Data
usermod -a -G www-data $WSL_USER_NAME
id $WSL_USER_NAME
groups $WSL_USER_GROUP

## Install Node
#apt-get install -y nodejs
#/usr/bin/npm install -g npm
##/usr/bin/npm install -g gulp-cli
##/usr/bin/npm install -g bower
#/usr/bin/npm install -g yarn
##/usr/bin/npm install -g grunt-cli