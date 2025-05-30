#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# Copyright (C) Marco Trevisan
#
# Authors:
#  Marco Trevisan <marco@trevisan.xyz>
#
# Revision for new URL:
#  Andrea Costantino <costan@amg.it>
#
# Revision for new XPath Query (add a filter by service type identifier)
#  Antonio Musarra <antonio.musarra@gmail.com>
#
# This program is free software; you can redistribute it and/or modify it under
# the terms of the GNU General Public License as published by the Free Software
# Foundation; version 3.
#
# This program is distributed in the hope that it will be useful, but WITHOUTa
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
# details.
#
# You should have received a copy of the GNU General Public License along with
# this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
#
# Get Italian government Certification Authority certificates from used by
# by various National Service SmartCards (Carta Nazionale dei Servizi- CNS)
#
# Original URI:
#  - http://www.agid.gov.it/agenda-digitale/infrastrutture-architetture/firme-elettroniche/certificati
#
# Current XML file:
#  - https://eidas.agid.gov.it/TL/TSL-IT.xml

"""
Script to retrieve and process Italian government Certification Authority certificates
from a specified XML file, either local or remote. It extracts relevant certificate
information and saves the certificates in PEM format.

Original URI:
 - https://www.agid.gov.it/agenda-digitale/infrastrutture-architetture/firme-elettroniche/certificati

Current XML file:
 - https://eidas.agid.gov.it/TL/TSL-IT.xml
"""

from cryptography import x509
from cryptography.hazmat.backends import default_backend
from datetime import datetime, timezone
from lxml import etree
import argparse
import re
import sys
import textwrap
import os

DEFAULT_XML_URI = "https://eidas.agid.gov.it/TL/TSL-IT.xml"
EXTENSION = ".pem"


def is_certificate_expired(cert):
    """
    Check if a certificate is expired.

    Args:
        cert (str): X.509 certificate content in base64 format.

    Returns:
        bool: True if the certificate is expired, False otherwise.
    """
    try:
        # Decode the certificate
        cert_data = x509.load_pem_x509_certificate(
            f"-----BEGIN CERTIFICATE-----\n{cert}\n-----END CERTIFICATE-----".encode(),
            default_backend()
        )

        # Convert not_valid_after to timezone-aware
        not_valid_after_aware = cert_data.not_valid_after.replace(tzinfo=timezone.utc)

        # Check expiration date
        return not_valid_after_aware < datetime.now(tz=timezone.utc)
    except Exception as e:
        print(f"Error checking certificate expiration: {e}")
        return True  # Treat as expired if there's an error


def get_certs_xml():
    """
    Fetch the XML file containing certificate information from the default URI.

    Returns:
        HTTPResponse: An HTTP response object containing the XML data.
    """
    import urllib.request
    return urllib.request.urlopen(DEFAULT_XML_URI)


def write_certificate(file, cert):
    """
    Write a certificate to a file in PEM format.

    Args:
        file (file object): File object opened in write mode.
        cert (str): X.509 certificate content in base64 format.
    """
    file.write('-----BEGIN CERTIFICATE-----\n')
    file.writelines(line + '\n' for line in textwrap.wrap(cert, 65))
    file.write('-----END CERTIFICATE-----\n')


def get_service_info(service_name, namespace):
    """
    Extract the service name and X.509 certificate from a service XML element.

    Args:
        service_name (lxml.etree._Element): XML element containing service information.
        namespace (str): XML namespace for locating elements.

    Returns:
        dict: A dictionary containing the service name and X.509 certificate.
    """
    return {
        'name': service_name.find(f"*/{namespace}Name").text,
        'x509_cert': service_name.find(f"*//{namespace}X509Certificate").text
    }


def safe_open(file_path, base_path, mode='r'):
    """
    Safely open a file within a specified base directory to prevent path traversal.

    Args:
        file_path (str): Relative path to the file to open.
        base_path (str): Base directory to ensure the file is within.
        mode (str, optional): File opening mode. Defaults to 'r'.

    Returns:
        file: Opened file object.

    Raises:
        ValueError: If the resolved path is outside the base directory.
    """
    # Get absolute path of the base directory
    base_path = os.path.abspath(base_path)

    # Join the base directory and the user-provided file path
    full_path = os.path.join(base_path, file_path)

    # Get the absolute path of the resulting path
    full_path = os.path.abspath(full_path)

    # Check if the resulting path is still within the base directory
    if not full_path.startswith(base_path):
        raise ValueError("File path is outside allowed area")

    # If everything is okay, open the file
    # file deepcode ignore PT: only point to use this function
    return open(full_path, mode)


def sanitize_certificate_name(service_name):
    """
    Sanitize the certificate name for use as a filename.

    Replaces invalid characters with underscores, removes consecutive underscores,
    and strips leading/trailing underscores and hyphens.

    Args:
        service_name (str): Original certificate name.

    Returns:
        str: Sanitized certificate name.
    """
    sanitized_name = re.sub(r'[A-z]{1,2}=', '_', re.sub(r'[=/\\,\' ".\-@]', '_', service_name))
    sanitized_name = re.sub(r'__+', '_', sanitized_name).strip('_-')
    return sanitized_name


# Command-line argument parsing
parser = argparse.ArgumentParser()
action = parser.add_mutually_exclusive_group(required=True)
action.add_argument("--output-folder", help="Where to save the certs files")
action.add_argument("--output-file", help="File saving certificates")
parser.add_argument("--cert-file", help="Input Xml file, instead of %s" % DEFAULT_XML_URI)
parser.add_argument("--service-type-identifier", help="Save certs by Service Type Identifier, instead of all")
parser.add_argument("--save-expired-certs", action="store_true",
                    help="Save expired certificates with the prefix 'expired_'")
args = parser.parse_args()

if args.output_folder:
    os.makedirs(args.output_folder, exist_ok=True)
elif args.output_file:
    if os.path.exists(args.output_file) and not os.path.isfile(args.output_file):
        print(f"Impossible to write on `{args.output_file}`, it's not a file.")
        sys.exit(1)
    print(f"File `{args.output_file}` will be overwritten...")

if args.cert_file:
    tree = etree.parse(args.cert_file)
    root = tree.getroot()
else:
    root = etree.fromstring(get_certs_xml().read())

default_namespace = re.search(r"{[^}]*}", root.tag)
default_namespace = default_namespace.group(0) if default_namespace else ""

print(f"Namespace: {default_namespace}")

# Dizionario dei namespace
ns = {
    "tsl": default_namespace.strip("{}")
}

if args.service_type_identifier:
    # Definiamo il dizionario delle variabili XPath
    variables = {"service_type_identifier": args.service_type_identifier}

    # Define the XPath query with a placeholder for the parameter
    query = "//tsl:ServiceInformation[tsl:ServiceTypeIdentifier=$service_type_identifier]"

    # Use the query with the parameter
    services = root.xpath(query, namespaces=ns, **variables)
else:
    # Define the XPath query with a placeholder for the parameter
    query = "//tsl:TrustServiceProviderList//tsl:TSPService/tsl:ServiceInformation"

    # Use the query with the parameter
    services = root.xpath(query, namespaces=ns)

if args.output_folder:
    for service in services:
        try:
            info = get_service_info(service, default_namespace)
            filename = sanitize_certificate_name(info['name'])
            cert_expired = is_certificate_expired(info['x509_cert'])

            # Skip expired certificates unless --save-expired-certs is specified
            if cert_expired and not args.save_expired_certs:
                print(f"Skipping expired certificate: {filename}")
                continue

            # Add 'expired_' prefix for expired certificates
            if cert_expired:
                filename = f"expired_{filename}"

            while os.path.exists(os.path.join(args.output_folder, filename + EXTENSION)):
                filename += "1"
            with safe_open(filename + EXTENSION, args.output_folder, 'w') as f:
                write_certificate(f, info['x509_cert'])
            print(f"Added certificate: {filename + EXTENSION}")
        except Exception as e:
            print(f"Error adding file: {e}")
else:
    with safe_open(args.output_file, '/', 'w') as f:
        for service in services:
            try:
                info = get_service_info(service, default_namespace)
                cert_expired = is_certificate_expired(info['x509_cert'])

                # Skip expired certificates unless --save-expired-certs is specified
                if cert_expired and not args.save_expired_certs:
                    print(f"Skipping expired certificate: {info['name']}")
                    continue

                # Add 'expired_' prefix for expired certificates
                if cert_expired:
                    info['name'] = f"expired_{info['name']}"

                write_certificate(f, info['x509_cert'])
                print(f"Added certificate: {info['name']}")
            except Exception as e:
                print(f"Error adding certificate: {e}")
