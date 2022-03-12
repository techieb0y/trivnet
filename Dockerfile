FROM centos:7
COPY trivnet*.rpm /tmp
RUN yum -y localinstall /tmp/trivnet*.rpm