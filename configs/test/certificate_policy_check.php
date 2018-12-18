<?php
$cnsCertificatePolicies = 'Policy: 1.3.76.16.2.1';
$ssl = openssl_x509_parse(getenv('SSL_CLIENT_CERT'));

if(preg_match("/$cnsCertificatePolicies$/m", $ssl['extensions']['certificatePolicies']) == 0) {
    die("Il certificato è valido ma non dispone della Certification Policy 
    che dovrebbe avere una CNS - Carta Nazionale Servizi. La Certification Policy
    è definita dall'OID 1.3.76.16.2.1 
    <a href='http://oid-info.com/cgi-bin/display?oid=1.3.76.16.2.1&action=display'
    >{iso(1) identified-organization(3) uninfo(76) agid(16) authentication(2) cns(1)}
    </a>");
}
?>