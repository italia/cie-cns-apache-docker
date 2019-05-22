FROM ubuntu:18.04

# Metadata params
ARG BUILD_DATE
ARG VCS_REF
ARG VCS_URL
ARG VERSION

LABEL maintainer="Antonio Musarra <antonio.musarra@gmail.com>" \
    org.label-schema.name="cie-cns-apache-docker" \
    org.label-schema.description="Apache HTTP 2.4 per SmartCard TS-CNS (Tessera Sanitaria - Carta Nazionale Servizi) e CIE (Carta Identità Elettronica)" \
    org.label-schema.version=${VERSION} \
    org.label-schema.build-date=${BUILD_DATE} \
    org.label-schema.vendor="Antonio Musarra's Blog" \
    org.label-schema.url="https://www.dontesta.it" \
    org.label-schema.vcs-url=${VCS_URL} \
    org.label-schema.vcs-ref=${VCS_REF} \
    org.label-schema.schema-version="1.0"

# Apache ENVs
ENV APACHE_SERVER_NAME cns.dontesta.it
ENV APACHE_SERVER_ADMIN cns@dontesta.it
ENV APACHE_SSL_CERTS cns-dontesta-it_crt.pem
ENV APACHE_SSL_PRIVATE cns-dontesta-it_key.pem
ENV APACHE_SSL_PORT 10443
ENV APACHE_LOG_LEVEL info
ENV APACHE_SSL_LOG_LEVEL info
ENV APACHE_SSL_VERIFY_CLIENT optional
ENV APPLICATION_URL https://${APACHE_SERVER_NAME}:${APACHE_SSL_PORT}
ENV CLIENT_VERIFY_LANDING_PAGE /error.php

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
COPY configs/httpd/default-ssl.conf /etc/apache2/sites-available/
COPY configs/httpd/ssl-params.conf /etc/apache2/conf-available/
COPY configs/httpd/dir.conf /etc/apache2/mods-enabled/
COPY configs/httpd/ports.conf /etc/apache2/

# Copy Server (pub and key) cns.dontesta.it
COPY configs/certs/*_crt.pem /etc/ssl/certs/
COPY configs/certs/*_key.pem /etc/ssl/private/

# Copy php samples script and other
COPY configs/www/*.php /var/www/html/
COPY configs/www/assets /var/www/html/assets
COPY configs/www/secure /var/www/html/secure
COPY images/favicon.ico /var/www/html/favicon.ico

# Copy auto-update-gov-certificates scripts and entrypoint
COPY scripts/auto-update-gov-certificates /auto-update-gov-certificates
COPY scripts/entrypoint /entrypoint

# Set execute flag for entrypoint and crontab entry
# Add Cron entry auto-update-gov-certificates
# Create Project ENV for crontab
RUN chmod +x /entrypoint \
    && chmod +x /auto-update-gov-certificates \
    && echo "30 23 * * * root . /project_env.sh; /auto-update-gov-certificates >> /var/log/cron.log 2>&1" > /etc/cron.d/auto-update-gov-certificates \
    && printenv | sed 's/^\(.*\)$/export \1/g' | grep -E "APACHE_|APPLICATION_URL|GOV_" > /project_env.sh \
    && chmod +x /project_env.sh

# Configure and enabled Apache features
RUN a2enmod ssl \
    && a2enmod headers \
    && a2enmod rewrite \
    && a2ensite default-ssl \
    && a2enconf ssl-params \
    && c_rehash /etc/ssl/certs/

# Expose Apache
EXPOSE ${APACHE_SSL_PORT}

# Define entry for setup contrab
ENTRYPOINT ["/entrypoint"]

# Launch Apache
CMD ["/usr/sbin/apache2ctl", "-DFOREGROUND"]