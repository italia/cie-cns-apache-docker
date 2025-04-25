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

from pathlib import Path
from lxml import etree
import argparse
import re
import sys
import textwrap
import os

DEFAULT_XML_URI = "https://eidas.agid.gov.it/TL/TSL-IT.xml"
EXTENSION = ".pem"


def get_certs_xml():
    if sys.version_info[0] == 2:
        import urllib2
        request = urllib2
    else:
        import urllib.request
        request = urllib.request

    return request.urlopen(DEFAULT_XML_URI)


def write_certificate(f, x509_cert):
    f.write('-----BEGIN CERTIFICATE-----\n')
    for line in textwrap.wrap(x509_cert, 65):
        f.write(line + '\n')
    f.write('-----END CERTIFICATE-----\n')


def get_service_info(service, namespace):
    name = service.find("*/" + namespace + "Name").text
    x509_cert = service.find("*//" + namespace + "X509Certificate").text
    return {'name': name, 'x509_cert': x509_cert}


def safe_open(file_path, base_path, mode='r'):
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


parser = argparse.ArgumentParser()
action = parser.add_mutually_exclusive_group(required=True)
action.add_argument("--output-folder", help="Where to save the certs files")
action.add_argument("--output-file", help="File saving certificates")
parser.add_argument("--cert-file", help="Input Xml file, instead of %s" % DEFAULT_XML_URI)
parser.add_argument("--service-type-identifier", help="Save certs by Service Type Identifier, instead of all")
args = parser.parse_args()

if args.output_folder:
    if os.path.exists(args.output_folder):
        if not os.path.isdir(args.output_folder):
            print("Impossible to save certificates in `%s': file exists and is not a folder." % args.output_folder)
            sys.exit(1)
    else:
        os.makedirs(args.output_folder)
elif args.output_file:
    if os.path.exists(args.output_file):
        if not os.path.isfile(args.output_file):
            print("Impossible to write on `%s', it's not a file." % args.output_file)
            sys.exit(1)

        print("File `%s' will be overwritten..." % args.output_file)

if args.cert_file:
    tree = etree.parse(args.cert_file)
    root = tree.getroot()
else:
    root = etree.fromstring(get_certs_xml().read())

try:
    [default_namespace] = re.findall("({[^}]*}).*", root.tag)
except:
    default_namespace = ""

print("Namespace: `%s`", default_namespace)

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
            name = re.sub('[A-z]{1,2}=', '_', re.sub('[/\,\' "]', '_', info['name'])).replace('__', '_').strip('_- ')
            filename = name

            idx = 1
            tmpname = filename
            while os.path.exists(os.path.join(args.output_folder, tmpname + EXTENSION)):
                tmpname = filename + str(idx)
                idx += 1
            filename = tmpname + EXTENSION

            f = safe_open(filename, args.output_folder, 'w')
            write_certificate(f, info['x509_cert'])
            f.close()
            print("Added certificate: %s" % filename)

        except Exception as e:
            print("Impossible to add file: %s" % e)
            pass

else:
    f = safe_open(args.output_file, '/', 'w')

    for service in services:
        try:
            info = get_service_info(service)
            write_certificate(f, info['x509_cert'])

            print("Added certificate %s" % info['name'])

        except Exception as e:
            print("Impossible to add certificate to file: %s" % e)
            pass

    f.close()
