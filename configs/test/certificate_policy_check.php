<?php
$cnsCertificatePolicies = 'Policy: 1.3.76.16.2.1';
$cieCertificatePolicies = 'Policy: 1.3.76.47.4';

$ssl = openssl_x509_parse(getenv('SSL_CLIENT_CERT'));

if((preg_match("/$cnsCertificatePolicies$/m", $ssl['extensions']['certificatePolicies']) == 0) && 
    (preg_match("/$cieCertificatePolicies$/m", $ssl['extensions']['certificatePolicies']) == 0)) {
    
    die("Il certificato è valido ma non dispone della Certification Policy 
        che dovrebbe avere una CNS - Carta Nazionale Servizi o la CIE - Carta d'Identità Elettronica. 
        <br>La Certification Policy per la CNS è definita dall'OID 1.3.76.16.2.1 mentre per la CIE
        è definita dall'OID 1.3.76.47.4.

        <ul>
            <li>OID 1.3.76.16.2.1 => <a href='http://oid-info.com/cgi-bin/display?oid=1.3.76.16.2.1&action=display'
                >{iso(1) identified-organization(3) uninfo(76) agid(16) authentication(2) cns(1)}
                </a>
            </li>
            <li>OID 1.3.76.47.4 => <a href='http://oid-info.com/get/1.3.76.47'
                >{iso(1) identified-organization(3) uninfo(76) 47}
                </a>
            </li>
        </ul>");
}
?>