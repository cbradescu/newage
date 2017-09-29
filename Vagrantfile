# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure("2") do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
  config.vm.box = "puppetlabs/ubuntu-14.04-64-puppet"
  config.vm.box_version = "1.0.3"

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  config.vm.box_check_update = true

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  #config.vm.network "forwarded_port", guest: 80, host: 8000

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network :private_network, ip: "192.168.44.101"
  config.vm.hostname = "newage.local"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on
  # your network.
  # config.vm.network "public_network"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
   config.vm.synced_folder "./", "/vagrant",
	:nfs => true,
	:mount_options => ['nolock,vers=3,udp,noatime'],
	:linux__nfs_options => ['rw','no_subtree_check','all_squash','async']
  config.nfs.map_uid = Process.uid
  config.nfs.map_gid = Process.gid
 
  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  config.vm.provider "virtualbox" do |v|
  # Display the VirtualBox GUI when booting the machine
  #  v.gui = true
	v.name="NewAge-CRM"
  #
  #   # Customize the amount of memory on the VM:
	v.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/vagrant-root", "1"] # Allows symlinks in windows
	v.customize ["modifyvm", :id, "--memory", 2048]
	v.customize ["modifyvm", :id, "--cpus", 2]
  end
  #
  # View the documentation for the provider you are using for more
  # information on available options.

  # Define a Vagrant Push strategy for pushing to Atlas. Other push strategies
  # such as FTP and Heroku are also available. See the documentation at
  # https://docs.vagrantup.com/v2/push/atlas.html for more information.
  # config.push.define "atlas" do |push|
  #   push.app = "YOUR_ATLAS_USERNAME/YOUR_APPLICATION_NAME"
  # end

	
  # Enable provisioning with a shell script. Additional provisioners such as
  # Puppet, Chef, Ansible, Salt, and Docker are also available. Please see the
  # documentation for more information about their specific syntax and use.
  config.vm.provision "shell", inline: <<-SHELL

	echo "*****************************************************"
	echo "************* Provision process started *************"
	echo "*****************************************************"

	# --------------------- Provision configuration ---------------------

	echo -e "\n******** Provision configuration ********\n"

	# --- Database settings ---

	DB_USER="catalin"
	DB_PASSWORD="123456"
	DB_NAME="newage_crm"

	# --- Oro application settings ---

	APP_HOST="localhost"
	APP_USER="admin"
	APP_PASSWORD="123456"
	APP_LOAD_DEMO_DATA="y"    # y | n

	# --- Git settings ---

	# If you don't want to download source files from scratch (for example if you in development of your
	# Oro-based app, with your own existing sources), just comment GIT_ variables below (by # sign)
#	GIT_REPO="https://github.com/cbradescu/newage.git"

	# If you don't want to checkout a particular tag or branch (just stay at the HEAD of master
	# branch), comment GIT_TAG variable below
#	GIT_TAG="2.0"

	# --------------------- LEMP installation & configuration ---------------------

	echo -e "\n******** Step 1 of 2. LEMP installation & configuration ********\n"

  	# --- Preconfigure mysql root password ---

  	echo -e "\n*** Preconfigure mysql root password ***\n"
  	echo "mysql-server mysql-server/root_password password $DB_PASSWORD" | debconf-set-selections
  	echo "mysql-server mysql-server/root_password_again password $DB_PASSWORD" | debconf-set-selections

  	# --- Main installations ---

	# --- Add new GPG Key ---
	curl --remote-name --location https://apt.puppetlabs.com/DEB-GPG-KEY-puppet
	gpg --keyid-format 0xLONG --with-fingerprint ./DEB-GPG-KEY-puppet
	apt-key add DEB-GPG-KEY-puppet
	
	sudo apt-get install -y software-properties-common
	sudo add-apt-repository ppa:ondrej/php
	
  	echo -e "\n*** Apt-get update ***\n"
	
  	apt-get update
 	echo -e "\n*** Install git, nginx, php, mysql-client, mysql-server, nodejs ***\n"
 	apt-get install --assume-yes git nginx mysql-client mysql-server nodejs php php7.1-xml php7.1-intl php7.1-mysql php-mbstring php7.1-curl php7.1-gd php7.1-mcrypt php7.1-soap php7.1-tidy php7.1-zip php-ldap
	apt-get remove --assume-yes apache2 apache2-bin apache2-data
	
  	# --- DB installation tuning ---

  	echo -e "\n*** DB installation tuning ***\n"
  	mysql -uroot -p$DB_PASSWORD -e "DROP DATABASE IF EXISTS $DB_NAME"
  	mysql -uroot -p$DB_PASSWORD -e "CREATE DATABASE $DB_NAME"
  	mysql -uroot -p$DB_PASSWORD -e "grant all privileges on $DB_NAME.* to '$DB_USER'@'%' identified by '$DB_PASSWORD'"

  	# --- Nginx site config ---

  	echo -e "\n*** Create nginx site config ***\n"
  	cat > /etc/nginx/sites-available/$APP_HOST <<____NGINXCONFIGTEMPLATE
        server {
            server_name $APP_HOST www.$APP_HOST;
            root  /vagrant/web;

            location / {
                # try to serve file directly, fallback to app.php
                try_files \\$uri /app_dev.php\\$is_args\\$args;
            }

            location ~ ^/(app|app_dev|config|install)\.php(/|$) {
                fastcgi_pass unix:/run/php/php7.1-fpm.sock;
                fastcgi_split_path_info ^(.+\.php)(/.*)$;
                include fastcgi_params;
                fastcgi_buffers 128 4096k;
                fastcgi_buffer_size 4096k;
                fastcgi_param SCRIPT_FILENAME \\$document_root\\$fastcgi_script_name;
                fastcgi_read_timeout 300;
                fastcgi_param HTTPS off;
            }

            error_log /vagrant/app/logs/${APP_HOST}_error.log;
            access_log /vagrant/app/logs/${APP_HOST}_access.log;
        }
____NGINXCONFIGTEMPLATE
  	ln -s /etc/nginx/sites-available/$APP_HOST /etc/nginx/sites-enabled/$APP_HOST
    rm -rf /etc/nginx/sites-enabled/default
 	mkdir /home/vagrant/logs/nginx
  	service nginx restart

  	# --- Add vagrant user to www-data group (for composer) ---

   	echo -e "\n*** Add 'vagrant' user to 'www-data' group and vice versa ***\n"
 	usermod -a -G www-data vagrant
 	usermod -a -G vagrant www-data

 	# ---  Configure php-fpm ---

   	echo -e "\n*** Configure php-fpm ***\n"
 	sed -i 's/;listen.allowed_clients/listen.allowed_clients/g' /etc/php/7.1/fpm/pool.d/www.conf
 	sed -i 's/;pm.max_requests/pm.max_requests/g' /etc/php/7.1/fpm/pool.d/www.conf

 	# --- Set recommended PHP.INI settings ---

   	echo -e "\n*** Set php.ini settings ***\n"
 	# FPM php.ini
 	sed -i 's/;date.timezone =/date.timezone = Europe\\/Bucharest/g' /etc/php/7.1/fpm/php.ini
 	sed -i 's/memory_limit = [0-9MG]*/memory_limit = 1G/g' /etc/php/7.1/fpm/php.ini
 	sed -i 's/max_execution_time = [0-9]*/max_execution_time = 600/g' /etc/php/7.1/fpm/php.ini
 	sed -i 's/;opcache.enable=0/opcache.enable=1/g' /etc/php/7.1/fpm/php.ini
 	sed -i 's/;opcache.memory_consumption=[0-9]*/opcache.memory_consumption=256/g' /etc/php/7.1/fpm/php.ini
 	sed -i 's/;opcache.interned_strings_buffer=[0-9]*/opcache.interned_strings_buffer=8/g' /etc/php/7.1/fpm/php.ini
 	sed -i 's/;opcache.max_accelerated_files=[0-9]*/opcache.max_accelerated_files=11000/g' /etc/php/7.1/fpm/php.ini
 	sed -i 's/;opcache.fast_shutdown=[01]*/opcache.fast_shutdown=1/g' /etc/php/7.1/fpm/php.ini
 	sed -i 's/;realpath_cache_size = 16k/realpath_cache_size = 4096k/g' /etc/php/7.1/fpm/php.ini
 	sed -i 's/;realpath_cache_ttl = 120/realpath_cache_ttl = 7200/g' /etc/php/7.1/fpm/php.ini
 	# CLI php.ini
 	sed -i 's/;date.timezone =/date.timezone = Europe\\/Bucharest/g' /etc/php/7.1/cli/php.ini
 	sed -i 's/max_execution_time = [0-9]*/max_execution_time = 600/g' /etc/php/7.1/cli/php.ini
 	sed -i 's/;opcache.enable=0/opcache.enable=1/g' /etc/php/7.1/cli/php.ini
 	sed -i 's/;opcache.memory_consumption=[0-9]*/opcache.memory_consumption=256/g' /etc/php/7.1/cli/php.ini
 	sed -i 's/;opcache.interned_strings_buffer=[0-9]*/opcache.interned_strings_buffer=8/g' /etc/php/7.1/cli/php.ini
 	sed -i 's/;opcache.max_accelerated_files=[0-9]*/opcache.max_accelerated_files=11000/g' /etc/php/7.1/cli/php.ini
 	sed -i 's/;opcache.fast_shutdown=[01]*/opcache.fast_shutdown=1/g' /etc/php/7.1/cli/php.ini
 	sed -i 's/;realpath_cache_size = 16k/realpath_cache_size = 4096k/g' /etc/php/7.1/cli/php.ini
 	sed -i 's/;realpath_cache_ttl = 120/realpath_cache_ttl = 7200/g' /etc/php/7.1/cli/php.ini
	# "save comments", "load comments" for lexical parser for doctrine?

	service php7.1-fpm restart

 	# --- Set recommended MySQL settings ---

   	echo -e "\n*** Configure MySQL ***\n"
	echo -e "\n[mysqld]" >> /etc/mysql/my.cnf
	echo "innodb_file_per_table = 0" >> /etc/mysql/my.cnf
	echo "wait_timeout = 28800" >> /etc/mysql/my.cnf

	service mysql restart

 	# --- Configure app/config/parameters.yml ---

 	# (to prevent composer interactive dialog)
 	echo -e "\n*** Configure app/config/parameters.yml ***\n"
 	cp /vagrant/app/config/parameters.yml.dist /vagrant/app/config/parameters.yml
 	sed -i "s/database_user:[ ]*root/database_user: $DB_USER/g" /vagrant/app/config/parameters.yml
 	sed -i "s/database_password:[ ]*~/database_password: $DB_PASSWORD/g" /vagrant/app/config/parameters.yml
 	sed -i "s/database_name:[ ]*[a-zA-Z0-9_]*/database_name: $DB_NAME/g" /vagrant/app/config/parameters.yml
	
 	# --- Composer Install ---

   	echo -e "\n*** Download and install composer ***\n"
 	wget -cq https://getcomposer.org/installer
 	php ./installer --install-dir=/usr/bin --filename=composer
 	rm ./installer
	
 	# --- Perform final cleaning ---
   	echo -e "\n*** Perform final cleaning ***\n"
	mkdir /home/vagrant/cache
	mkdir /home/vagrant/logs
   	chown -R www-data:www-data /home/vagrant/cache /home/vagrant/logs
   	find /home/vagrant/cache -type d -exec chmod 0775 {} \\;
   	find /home/vagrant/logs -type d -exec chmod 0775 {} \\;
	ln -s /home/vagrant/cache /vagrant/app/cache
	ln -s /home/vagrant/logs /vagrant/app/logs

   	echo -e "\n*** Run 'composer install' command ***\n"
	cd /vagrant
#	composer global require fxp/composer-asset-plugin:1.2.2
	composer global require fxp/composer-asset-plugin:~1.2
 	composer install --prefer-dist --no-dev

 	# --- Install Oro applicatioin ---
	
	# --- Clear cache ---
	echo -e "\n*** Cleaning cache to read parameters.yml\n"
	rm -rf /vagrant/app/cache/*

   	echo -e "\n*** Run 'oro:install' command ***\n"
   	php app/console oro:install --env=prod --application-url="http://$APP_HOST/" --organization-name="New Age Advertising" --user-name="$APP_USER" --user-email="cbradescu@yahoo.com" --user-firstname="Catalin" --user-lastname="B." --user-password="$APP_PASSWORD" --sample-data=$APP_LOAD_DEMO_DATA --timeout=0 --no-debug

   	echo -e "\n*** Run 'oro:api:doc:cache:clear' command ***\n"
   	php app/console oro:api:doc:cache:clear --env=prod

	# --- Clear cache ---
	echo -e "\n*** Cleaning cache\n"
	rm -rf /vagrant/app/cache/*
	
	echo -e "\n*** Warmp-up cache for dev mode cache\n"
	php app/console cache:warmup --env=dev	

	echo -e "\n*** Run 'doctrine:schema:update --force --dump-sql'\n"
	php app/console doctrine:schema:update --force --dump-sql
	
	echo -e "\n*** Run 'oro:entity-config:update --force'\n"
	php app/console oro:entity-config:update --force
		
	echo -e "\n*** Run 'oro:entity-extend:update-config' \n"
	php app/console oro:entity-extend:update-config

   	echo -e "\n*** Oro application installation finished ***\n"

	# --------------------- Final words ---------------------

   	echo -e "\n************* Congratulations! *************\n"

   	echo -e "\nInstallation finished successfully!\n"

  SHELL
end

# Useful Oro commands:
# ---------------------
# php app/console doctrine:schema:update --force --dump-sql
# php app/console oro:entity-config:update --force
# php app/console oro:entity-extend:update-config
# php app/console cache:clear --env=dev
# php app/console cache:warmup --env=dev

# Set git line endings for commited files:
# ----------------------------------------
# git config core.eol lf
# git config core.autocrlf input

# !!! Activare fiser swap pentru erori de memorie precum:
# -------------------------------------------------------
#  [ErrorException]
#  proc_open(): fork failed - Cannot allocate memory
#
# 1. We will create a 1 GiB file (/mnt/1GiB.swap) to use as swap:
#   sudo fallocate -l 1g /mnt/1GiB.swap
# 1.1 If fallocate fails or it not available, you can use dd:
#   sudo dd if=/dev/zero of=/mnt/1GiB.swap bs=1024 count=1048576# sudo chmod 600 /mnt/1GiB.swap
# 2. We need to set the swap file permissions to 600 to prevent other users from being able to read potentially sensitive information from the swap file.
#   sudo chmod 600 /mnt/1GiB.swap
# 3. Format the file as swap:
#   sudo mkswap /mnt/1GiB.swap
# 4. Enable use of Swap File
#   sudo swapon /mnt/1GiB.swap
# 5. Confirm that the swap partition exists:
#   cat /proc/swaps


# To skip composer require-dev:
#   composer required <xxx-package> --update-no-dev



# sudo apt-get install redis-server
# composer require snc/redis-bundle 1.1.x-dev --update-no-dev
# composer require predis/predis ^1.0 --update-no-dev