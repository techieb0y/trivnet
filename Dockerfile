FROM centos:7
COPY rpmbuild/RPMS/noarch/trivnet-*.rpm /tmp/
RUN yum -y localinstall /tmp/trivnet*.rpm