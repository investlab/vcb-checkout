FROM yiisoftware/yii2-php:7.3-apache
#RUN sed -i -e 's|/app/web|/app/vcb|g' /etc/apache2/sites-available/000-default.conf
ENV TZ="Asia/Ho_Chi_Minh"
COPY ./docker/000-default.conf /etc/apache2/sites-available/
RUN a2enmod ssl
COPY ./docker/apm/apm-agent-php_1.8.4_all.deb /app/vcb/docker/apm/
RUN dpkg -i /app/vcb/docker/apm/apm-agent-php_1.8.4_all.deb
COPY ./docker/apm/apm-config-custom.ini /usr/local/etc/php/conf.d/elastic_apm.ini