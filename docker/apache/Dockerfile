FROM ubuntu:24.04

RUN apt-get update && apt-get install -y apache2 libapache2-mod-php \
    && a2enmod rewrite \
    && service apache2 restart

COPY apache.conf /etc/apache2/sites-available/000-default.conf

CMD ["apachectl", "-D", "FOREGROUND"]

#RUN apt-get update && apt-get install -y apache2

#COPY tinder.navi.jpn.com.conf /etc/apache2/sites-enabled/
## COPY default-ssl.conf /etc/apache2/sites-enabled/
COPY dev.tinder.navi.jpn.com /etc/letsencrypt/live/tinder.navi.jpn.com/
COPY dev.tinder.navi.jpn.com-le-ssl.conf /etc/apache2/sites-available/
