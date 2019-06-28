FROM nginx:1.13.9

COPY .ol/openshift/web/nginx.conf /etc/nginx/conf.d/default.conf

RUN sed -i "s/APP_HOSTNAME/${APP_HOSTNAME:-app}/g" /etc/nginx/conf.d/default.conf

WORKDIR /var/www/html/

COPY web/ /var/www/html/web/