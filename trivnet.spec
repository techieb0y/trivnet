Name:           trivnet
Version:        1.0
Release:        %{now}
Summary:        A web-based database for tracking marathon runners and other uses.

Group:          ham
License:        Unspecified
Source:        trivnet.tar.gz

BuildRoot:      %(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

Requires:       postgresql13-server postgresql13-contrib rh-php73-php-pgsql rh-php73-php-gd rh-php73-php-cli rh-php73-php-fpm rh-php73-php-opcache trivnet-static
BuildRequires:  php-cli curl unzip

%define debug_package %{nil}

%description
A rewrite of TrivnetDB (original by Dennis, KB8ZQZ), building on the original ARESDATA.

%package static
Summary:	Static content used by trivnet

%description static
Static content used by trivnet

%prep
rm -vf /tmp/trivnet/*.dat
rm -vf /tmp/trivnet/counts

%build
# # Create a loadable copy of the current FCC database
# This has been moved to the GitLab CI pipeline, so repeated builds can cache the file
# echo "Fetching FCC database..."
# [ -d /tmp/trivnet/ ] || mkdir /tmp/trivnet/
# [ -f /tmp/trivnet/l_amat.zip ] || curl -o /tmp/trivnet/l_amat.zip http://wireless.fcc.gov/uls/data/complete/l_amat.zip
# unzip -d /tmp/trivnet/ /tmp/trivnet/l_amat.zip
# echo "Parsing FCC database files..."
# php %{_builddir}/util/fcc-util.php /tmp/trivnet/


%install
mkdir -p %{buildroot}/tmp/
mkdir -p %{buildroot}/var/www/trivnet/
mv %{_sourcedir}/trivnet-fcc.out %{buildroot}/tmp/
rsync -arv %{_builddir}/trivnet/ %{buildroot}/var/www/trivnet/
# mkdir -p %{buildroot}/etc/cron.d/ && echo "* * * * * trivnet /opt/rh/rh-php73/root/usr/bin/php /var/www/trivnet/async.php --runonce" > %{buildroot}/etc/cron.d/trivnet

# Set up apache
mkdir -p %{buildroot}/etc/httpd/conf.d/ && mv %{_sourcedir}/httpd.conf %{buildroot}/etc/httpd/conf.d/trivnet.conf
mkdir -p %{buildroot}/etc/systemd/system/ && mv %{_sourcedir}/trivnet-async.service %{buildroot}/etc/systemd/system/trivnet-async.service

%clean
rm -f /tmp/trivnet-fcc.out

%post
echo "Running with \$1 of: $1"
if [ $1 -eq 1 ]; then
	PASSWORD=`head -c12 /dev/urandom | base64`
	PREFIX="/var/lib/pgsql/13/data"

	adduser -r trivnet -M -d /var/www/trivnet/
	chown -R trivnet /var/www/trivnet/

	echo "Checking PostgreSQL setup"
	chsh -s /bin/bash postgres
	/usr/pgsql-13/bin/postgresql13-setup initdb

	echo "Insert ACL into pg_hba.conf"
	cat << EOF > /tmp/$$.awk
/^host( )+all( )+all( )+127/    { print "host   trivnet         trivnet         127.0.0.1/32            md5" }
/.*/            { print \$0 }
EOF

	mv ${PREFIX}/pg_hba.conf ${PREFIX}/pg_hba.orig
	awk -f /tmp/$$.awk ${PREFIX}/pg_hba.orig > ${PREFIX}/pg_hba.conf && rm -f ${PREFIX}/pg_hba.orig
	
	echo "Starting PostgreSQL"
	systemctl enable postgresql-13
	systemctl start postgresql-13

	echo "Create 'trivnet' database objects"
	su -c "createuser -l -S -R -D trivnet" postgres
	su -c "createdb -O trivnet trivnet" postgres
	su -c "psql -c \"alter user trivnet with password '${PASSWORD}'\" trivnet" postgres

	echo "Load the schema"
	su -c "psql trivnet < /var/www/trivnet/setup.sql" trivnet
	su -c "psql trivnet < /var/www/trivnet/mtcm.sql" trivnet
	su -c "psql trivnet < /var/www/trivnet/upsert.sql" trivnet

	echo "Load the tablefunc stuff from contrib"
	su -c "psql -c \"CREATE EXTENSION tablefunc;\" trivnet" postgres

	echo "Load the FCC database data"
	cat << EOF > /tmp/load.sql
set client_encoding to latin1;
delete from part97;
copy "part97" from '/tmp/trivnet-fcc.out';
EOF
	su -c "psql trivnet < /tmp/load.sql" postgres && rm -f /tmp/load.sql && rm -f /tmp/trivnet-fcc.out

	echo "Put the generated password into the config file"
	cat << EOF > /tmp/$$.awk
/^\\\$DB_PASS/    { print "\$DB_PASS = \"${PASSWORD}\";"; next }
/.*/            { print \$0 }
EOF

	mv /var/www/trivnet/include/config.inc /var/www/trivnet/include/config.tmpl
	awk -f /tmp/$$.awk /var/www/trivnet/include/config.tmpl > /var/www/trivnet/include/config.inc
	rm -f /var/www/trivnet/include/config.tmpl

	echo "Making data directories"
	mkdir /var/www/trivnet/jobs/
	mkdir /var/www/trivnet/csvdata/

	echo "Setting permissions"
	chown trivnet:apache /var/www/trivnet/jobs/
	chmod 774 /var/www/trivnet/jobs/

	echo "Starting Apache"
	systemctl enable httpd
	systemctl start httpd 
	systemctl daemon-reload
	systemctl enable trivnet-async
	systemctl start trivnet-async
        rm -f /tmp/$$.awk
else
  echo "Not doing DB setup as this is an upgrde"
fi

%post static
	echo "Linking jQuery"
	[ -h /var/www/trivnet/js/jquery.js ] && rm -f /var/www/trivnet/js/jquery.js
	ln -s /var/www/trivnet/js/jquery-1.10.2.min.js /var/www/trivnet/js/jquery.js

%preun static
	[ -h /var/www/trivnet/js/jquery.js ] && rm -f /var/www/trivnet/js/jquery.js

%files static
/var/www/trivnet/css
/var/www/trivnet/common
/var/www/trivnet/images
/var/www/trivnet/js
/var/www/trivnet/help.html

%files
/var/www/trivnet/.htaccess
/var/www/trivnet/*.php
/var/www/trivnet/*.sql
/var/www/trivnet/agents
/var/www/trivnet/include
/tmp/trivnet-fcc.out
#/etc/cron.d/trivnet
/etc/httpd/conf.d/trivnet.conf
/etc/systemd/system/trivnet-async.service

%changelog
* Sun Jul 31 2021 kd8gbl - 1.3
- Update PostgreSQL version references to bump from 9.6 to 13

* Sun Jul 26 2020 kd8gbl - 1.2
- Switch to a SystemD unit file to start the async engine versus a cron job

* Mon Jul 13 2020 kd8gbl - 1.1
- Updates for compatibility with PHP7 via FPM

* Sat Sep 01 2018 kd8gbl - 1.0b2
- Splitting static content into sub-package for CDN

* Sat Mar 26 2016 kd8gbl - 1.0b1
- Original packaging for RHEL7
