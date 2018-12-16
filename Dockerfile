FROM ubuntu:18.04
LABEL maintainer="Antonio Musarra <antonio.musarra@gmail.com>"

# Apache ENVs
ENV APACHE_SERVER_NAME cns.dontesta.it
ENV APACHE_SERVER_ADMIN cns@dontesta.it
ENV APACHE_SSL_CERTS cns-dontesta-it_crt.pem
ENV APACHE_SSL_PRIVATE cns-dontesta-it_key.pem
ENV APACHE_SSL_PORT 10443
ENV APPLICATION_URL https://${APACHE_SERVER_NAME}:${APACHE_SSL_PORT}

# Env for deb conf
ENV DEBIAN_FRONTEND noninteractive

# Env for Trusted CA certificate
ENV GOV_TRUST_CERTS_DOWNLOAD_SCRIPT_URL https://gist.githubusercontent.com/costan1974/1476912d51094e0a32f64d5d0c1e4007/raw/654a382253ef542558a7b2470048e855518f2d64/parse-gov-certs.py
ENV GOV_TRUST_CERTS_OUTPUT_PATH /tmp/gov/trust/certs

# Install services, packages and do cleanup
RUN apt update \
    && apt install -y apache2 \
    && apt install -y php libapache2-mod-php \
    && apt install -y curl \
    && apt install -y python \
    && rm -rf /var/lib/apt/lists/*

# Download Trusted CA certificate
RUN curl ${GOV_TRUST_CERTS_DOWNLOAD_SCRIPT_URL} \
    | python /dev/stdin --output-folder ${GOV_TRUST_CERTS_OUTPUT_PATH}

# Copy Apache configuration file
COPY configs/httpd/000-default.conf /etc/apache2/sites-available/
COPY configs/httpd/default-ssl.conf /etc/apache2/sites-available/
COPY configs/httpd/ssl-params.conf /etc/apache2/conf-available/
COPY configs/httpd/dir.conf /etc/apache2/mods-enabled/
COPY configs/httpd/ports.conf /etc/apache2/

# Copy CNS certs
COPY configs/certs/cns/*.pem /etc/ssl/certs/

# Copy Server (pub and key) cns.dontesta.it
COPY configs/certs/*_crt.pem /etc/ssl/certs/
COPY configs/certs/*_key.pem /etc/ssl/private/

# Copy phpinfo test script
COPY configs/test/info.php /var/www/html/info.php
COPY configs/test/index.php /var/www/html/index.php

RUN a2enmod ssl \
    && a2enmod headers \
    && a2ensite default-ssl \
    && a2enconf ssl-params \
    && c_rehash /etc/ssl/certs/

# Expose Apache
EXPOSE ${APACHE_SSL_PORT}
 
# Launch Apache
CMD ["/usr/sbin/apache2ctl", "-DFOREGROUND"]