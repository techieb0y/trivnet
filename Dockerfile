FROM centos:7
COPY rpmbuild/noarch/trivnet*.rpm /tmp
RUN yum -y localinstall /tmp/trivnet*.rpm