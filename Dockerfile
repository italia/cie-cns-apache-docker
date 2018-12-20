FROM ubuntu:18.04

LABEL maintainer="Antonio Musarra <antonio.musarra@gmail.com>"
LABEL name="httpd-cns-dontesta-it"
LABEL description="Apache HTTP 2.4 per SmartCard TS-CNS (Tessera Sanitaria - Carta Nazionale Servizi)"
LABEL version="1.2.1"
LABEL it.dontesta.vendor="Antonio Musarra's Blog"
LABEL it.dontesta.url="https://www.dontesta.it"
LABEL it.dontesta.github="https://github.com/amusarra/apache-httpd-ts-cns-docker"
LABEL it.dontesta.twitter="antonio_musarra"
LABEL it.dontesta.is-beta="true"

# Apache ENVs
ENV APACHE_SERVER_NAME cns.dontesta.it
ENV APACHE_SERVER_ADMIN cns@dontesta.it
ENV APACHE_SSL_CERTS cns-dontesta-it_crt.pem
ENV APACHE_SSL_PRIVATE cns-dontesta-it_key.pem
ENV APACHE_SSL_PORT 10443
ENV APACHE_LOG_LEVEL info
ENV APACHE_SSL_LOG_LEVEL info
ENV APPLICATION_URL https://${APACHE_SERVER_NAME}:${APACHE_SSL_PORT}

# Env for deb conf
ENV DEBIAN_FRONTEND noninteractive

# Env for Trusted CA certificate
ENV GOV_TRUST_CERTS_DOWNLOAD_SCRIPT_URL https://raw.githubusercontent.com/amusarra/apache-httpd-ts-cns-docker/master/scripts/parse-gov-certs.py
ENV GOV_TRUST_CERTS_SERVICE_TYPE_IDENTIFIER http://uri.etsi.org/TrstSvc/Svctype/IdV
ENV GOV_TRUST_CERTS_OUTPUT_PATH /tmp/gov/trust/certs

# Install services, packages and do cleanup
RUN apt update \
    && apt install -y apache2 \
    && apt install -y php libapache2-mod-php \
    && apt install -y curl \
    && apt install -y python \
    && apt install -y cron \
    && rm -rf /var/lib/apt/lists/*

# Download Trusted CA certificate and copy to ssl system path
RUN rm -rf ${GOV_TRUST_CERTS_OUTPUT_PATH} \
    && curl ${GOV_TRUST_CERTS_DOWNLOAD_SCRIPT_URL} \
    | python /dev/stdin --output-folder ${GOV_TRUST_CERTS_OUTPUT_PATH} \
    --service-type-identifier ${GOV_TRUST_CERTS_SERVICE_TYPE_IDENTIFIER} \
    && cp ${GOV_TRUST_CERTS_OUTPUT_PATH}/*.pem /etc/ssl/certs/

# Copy Apache configuration file
COPY configs/httpd/000-default.conf /etc/apache2/sites-available/
COPY configs/httpd/default-ssl.conf /etc/apache2/sites-available/
COPY configs/httpd/ssl-params.conf /etc/apache2/conf-available/
COPY configs/httpd/dir.conf /etc/apache2/mods-enabled/
COPY configs/httpd/ports.conf /etc/apache2/

# Copy Server (pub and key) cns.dontesta.it
COPY configs/certs/*_crt.pem /etc/ssl/certs/
COPY configs/certs/*_key.pem /etc/ssl/private/

# Copy php samples script
COPY configs/test/info.php /var/www/html/info.php
COPY configs/test/index.php /var/www/html/index.php
COPY configs/test/certificate_policy_check.php /var/www/html/certificate_policy_check.php

# Copy auto-update-gov-certificates scripts and entrypoint
COPY scripts/auto-update-gov-certificates /auto-update-gov-certificates
COPY scripts/entrypoint /entrypoint

# Set execute flag for entrypoint and crontab entry
# Add Cron entry auto-update-gov-certificates
# Create Project ENV for crontab
RUN chmod +x /entrypoint \
    && chmod +x /auto-update-gov-certificates \
    && echo "0 0 6 1/1 * ? * root . /project_env.sh; /auto-update-gov-certificates >> /var/log/cron.log 2>&1" > /etc/cron.d/auto-update-gov-certificates \
    && printenv | sed 's/^\(.*\)$/export \1/g' | grep -E "APACHE_|APPLICATION_URL|GOV_" > /project_env.sh \
    && chmod +x /project_env.sh

# Configure and enabled Apache features
RUN a2enmod ssl \
    && a2enmod headers \
    && a2ensite default-ssl \
    && a2enconf ssl-params \
    && c_rehash /etc/ssl/certs/

# Expose Apache
EXPOSE ${APACHE_SSL_PORT}

# Define entry for setup contrab
ENTRYPOINT ["/entrypoint"]

# Launch Apache
CMD ["/usr/sbin/apache2ctl", "-DFOREGROUND"]