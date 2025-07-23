
dep:
	apt install nginx -y
#	apt install php8.1-fpm -y
#	apt install php8.1-sqlite3 -y
	apt install php-fpm -y
	apt install php-sqlite3 -y

status-nginx:
	systemctl status nginx

status-php:
	systemctl status php8.1-fpm
#	systemctl status php8.3-fpm

restart-nginx:
	systemctl restart nginx

adduser:
	gpasswd -a www-data `whoami`

addconf:
	cp expweb.conf /etc/nginx/sites-available/
	ln -sf /etc/nginx/sites-available/expweb.conf /etc/nginx/sites-enabled/
	systemctl restart nginx

delconf:
	unlink /etc/nginx/sites-enabled/expweb.conf
	rm -f /etc/nginx/sites-available/expweb.conf

# To prevent 401 error:
# chmod 755 /home/rob, /home/rob/src, /home/rob/src/expweb
#
# To prevent error in writing sqlite3 db file:
# chmod 757 /home/rob/src/expweb
# to make expweb dir writable for all users.
#
# -rwxrwxrwx left to right rwx (file owner, group owner, all users)
# So 757 means writable for file owner and all users
