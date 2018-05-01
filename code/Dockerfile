FROM ubuntu:16.04
MAINTAINER Simon Lindsay <simon@iseek.biz> @singularo

LABEL RUN docker run --name \${NAME} \${IMAGE}

ENV DEBIAN_FRONTEND noninteractive

RUN echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu xenial main" > /etc/apt/sources.list.d/php.list \
&& apt-key adv --keyserver keyserver.ubuntu.com --recv E5267A6C

RUN apt-get update \
&& apt-get -y install php7.2 php7.2-common php7.2-curl \
&& apt-get -y autoremove && apt-get -y autoclean && apt-get clean && rm -rf /var/lib/apt/lists /tmp/* /var/tmp/*

COPY . /root

ENTRYPOINT [ "/root/sms2_send.php" ]
CMD [ ]
