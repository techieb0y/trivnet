Name:           trivnet
Version:        1.0
Release:        b1
Summary:        A web-based database for tracking marathon runners and other uses.

Group:          ham
License:        Unspecified
Source:        trivnet.tar.gz

BuildRoot:      %(mktemp -ud %{_tmppath}/%{name}-%{version}-%{release}-XXXXXX)

Requires:       php postgresql-server postgresql-contrib php-pgsql php-gd python-carbon graphite-web mod_wsgi
BuildRequires:  php-cli curl unzip

%define debug_package %{nil}

%description
A rewrite of TrivnetDB (original by Dennis, KB8ZQZ), building on the original ARESDATA.

%prep
rm -vf /tmp/trivnet/*.dat
rm -vf /tmp/trivnet/counts

%build
# Create a loadable copy of the current FCC database
echo "Fetching FCC database..."
[ -d /tmp/trivnet/ ] || mkdir /tmp/trivnet/
[ -f /tmp/trivnet/l_amat.zip ] || curl -o /tmp/trivnet/l_amat.zip http://wireless.fcc.gov/uls/data/complete/l_amat.zip
unzip -d /tmp/trivnet/ /tmp/trivnet/l_amat.zip
echo "Parsing FCC database files..."
php %{_builddir}/util/fcc-util.php /tmp/trivnet/


%install
mkdir -p %{buildroot}/tmp/
mkdir -p %{buildroot}/var/www/trivnet/
mv /tmp/trivnet-fcc.out %{buildroot}/tmp/
rsync -arv %{_builddir}/trivnet/ %{buildroot}/var/www/trivnet/
mkdir -p %{buildroot}/etc/cron.d/ && echo "* * * * * trivnet php /var/www/trivnet/async.php --runonce" > %{buildroot}/etc/cron.d/trivnet

# Set up apache
mkdir -p %{buildroot}/etc/httpd/conf.d/ && mv %{_sourcedir}/httpd.conf %{buildroot}/etc/httpd/conf.d/trivnet.conf

%clean

%post
set -x
PASSWORD=`head -c12 /dev/urandom | sha1sum | base64 | cut -c 1-16`

adduser -r trivnet -M -d /var/www/trivnet/
chown -R trivnet /var/www/trivnet/

systemctl enable postgresql
systemctl start postgresql

# Create 'trivnet' database objects
su -c "createuser -l -S -R -D trivnet" postgres
su -c "createdb -O trivnet trivnet" postgres
su -c "psql -c \"alter user trivnet with password '${PASSWORD}'\" trivnet" postgres

# Load the schema
su -c "psql trivnet < /var/www/trivnet/setup.sql" trivnet
su -c "psql trivnet < /var/www/trivnet/mtcm.sql" trivnet

# Load the tablefunc stuff from contrib
su -c "psql -c \"CREATE EXTENSION tablefunc;\" trivnet" postgres

# Load the FCC database data
cat << EOF > /tmp/load.sql
set client_encoding to latin1;
copy "part97" from '/tmp/trivnet-fcc.out';
EOF
su -c "psql trivnet < /tmp/load.sql" postgres && rm -f /tmp/load.sql && rm -f /tmp/trivnet-fcc.out

# Put the generated password into the config file
cat << EOF > /tmp/$$.awk
/^\\\$DB_PASS/    { print "\$DB_PASS = \"${PASSWORD}\";"; next }
/.*/            { print \$0 }
EOF

mv /var/www/trivnet/include/config.inc /var/www/trivnet/include/config.tmpl
awk -f /tmp/$$.awk /var/www/trivnet/include/config.tmpl > /var/www/trivnet/include/config.inc
rm -f /var/www/trivnet/include/config.tmpl

# Insert ACL into pg_hba.conf
cat << EOF > /tmp/$$.awk
/^host( )+all( )+all( )+127/    { print "host   trivnet         trivnet         127.0.0.1/32            md5" }
/.*/            { print \$0 }
EOF

mv /var/lib/pgsql/data/pg_hba.conf /var/lib/pgsql/data/pg_hba.orig
awk -f /tmp/$$.awk /var/lib/pgsql/data/pg_hba.orig > /var/lib/pgsql/data/pg_hba.conf
rm -f /var/lib/pgsql/data/pg_hba.orig

mkdir /var/www/trivnet/jobs/
chown trivnet:apache /var/www/trivnet/jobs/
chmod 774 /var/www/trivnet/jobs/

systemctl enable httpd
systemctl start httpd

%files
/var/www/trivnet/
/tmp/trivnet-fcc.out
/etc/cron.d/trivnet
/etc/httpd/conf.d/trivnet.conf

%changelog
* Sat Mar 26 2016 kd8gbl - 1.0b1
- Original packaging for RHEL7
