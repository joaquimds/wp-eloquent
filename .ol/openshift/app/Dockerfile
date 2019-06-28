# TODO: use a standard outlandish image, I have no idea what this is
FROM outlandish/wordpress:latest

COPY . /var/www/html

WORKDIR /var/www/html

COPY .ol/openshift/app/startup.sh /startup.sh

CMD /startup.sh
