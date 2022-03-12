FROM centos:7
RUN yum -y localinstall rpmbuild/noarch/trivnet*.rpm