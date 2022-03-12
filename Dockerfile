FROM centos:7
COPY rpmbuild/RPMS/noarch/trivnet-*.rpm /tmp/
RUN yum install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-7-`uname -m`/pgdg-redhat-repo-latest.noarch.rpm
RUN yum -y localinstall /tmp/trivnet*.rpm