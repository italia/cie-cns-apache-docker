# Apache HTTP 2.4 per SmartCard TS-CNS (Tessera Sanitaria - Carta Nazionale Servizi)
[![Antonio Musarra's Blog](https://img.shields.io/badge/maintainer-Antonio_Musarra's_Blog-purple.svg?colorB=6e60cc)](https://www.dontesta.it)
[![](https://images.microbadger.com/badges/image/amusarra/httpd-cns-dontesta-it:1.2.2.svg)](https://microbadger.com/images/amusarra/httpd-cns-dontesta-it:1.2.2 "Get your own image badge on microbadger.com")
[![](https://images.microbadger.com/badges/version/amusarra/httpd-cns-dontesta-it:1.2.2.svg)](https://microbadger.com/images/amusarra/httpd-cns-dontesta-it:1.2.2 "Get your own version badge on microbadger.com")
[![](https://images.microbadger.com/badges/commit/amusarra/httpd-cns-dontesta-it:1.2.2.svg)](https://microbadger.com/images/amusarra/httpd-cns-dontesta-it:1.2.2 "Get your own commit badge on microbadger.com")
[![Twitter Follow](https://img.shields.io/twitter/follow/antonio_musarra.svg?style=social&label=%40antonio_musarra%20on%20Twitter&style=plastic)](https://twitter.com/antonio_musarra)

L'obiettivo di questo progetto è quello di fornire un **template** pronto all'uso
che realizza un sistema di autenticazione tramite la SmartCard **TS-CNS** basato 
su [Apache HTTP](http://httpd.apache.org/docs/2.4/). **Ognuno può poi modificare 
o specializzare questo progetto sulla base delle proprie esigenze**

Si tratta di un progetto [docker](https://www.docker.com/) per la creazione di 
un container che implementa un sistema di **mutua autenticazione o autenticazione bilaterale SSL/TLS**.
Questo meccanismo di autenticazione richiede anche il certificato digitale 
da parte del client, certificato che in questo caso risiede all'interno della TS-CNS.

La particolarità del sistema implementato (attraverso questo container) è quella 
di consentire l'autenticazione tramite la propria SmartCard
**TS-CNS (Tessera Sanitaria - Carta Nazionale Servizi)**, rilasciata dalla 
regione di appartenenza.

La mia regione di appartenenza è la Regione Lazio il cui portale di riferimento
per la TS-CNS è https://cns.regione.lazio.it/. Ogni regione ha il suo portale
di riferimento dov'è possibile trovare tutte le informazioni utili che riguardano
appunto la TS-CNS.

## 1 - Overview
Questo container parte dall'immagine base di [*ubuntu:18.04*](https://hub.docker.com/_/ubuntu), 
poi specializzato al fine di soddisfare i requisiti minimi per un sistema di 
autenticazione basato sulla TS-CNS.

Il software di base installato è:

* Apache HTTP 2.4 (2.4.29)
* PHP 7 (7.2.10-0ubuntu0.18.04.1)
* Modulo PHP per Apache

_L'installazione di PHP e del modulo per Apache è del tutto opzionale_. I due 
moduli sono stati installati esclusivamente per costruire la pagina di atterraggio
dell'utente dopo la fase di autenticazione. Questa pagina mostra le informazioni
estratte dal certificato digitale.

## 2 - Struttura del Docker File
Cerchiamo di capire quali sono le sezioni più significative del Dockefile. 
La prima riga del file (come anticipato in precedenza) fa in modo che il 
container parta dall'immagine docker *ubuntu:18.04*.

```docker
FROM ubuntu:18.04
```

A seguire c'è la sezione delle variabili di ambiente che sono prettamente 
specifiche di Apache HTTP. I valori di queste variabili d'ambiente possono
essere modificate in base alle proprie esigenze.

```docker
# Apache ENVs
ENV APACHE_SERVER_NAME cns.dontesta.it
ENV APACHE_SERVER_ADMIN cns@dontesta.it
ENV APACHE_SSL_CERTS cns-dontesta-it_crt.pem
ENV APACHE_SSL_PRIVATE cns-dontesta-it_key.pem
ENV APACHE_SSL_PORT 10443
ENV APPLICATION_URL https://${APACHE_SERVER_NAME}:${APACHE_SSL_PORT}
ENV APACHE_LOG_LEVEL info
ENV APACHE_SSL_LOG_LEVEL info
```

Le prime due variabili sono molto esplicative, la prima in particolare,
imposta il server name, che in questo caso è: cns.dontesta.it.

Le due variabili `APACHE_SSL_CERTS` e `APACHE_SSL_PRIVATE` impostano:

1. il nome del file che contiene il certificato pubblico del server in formato PEM;
2. il nome del file che contiene la chiave privata (in formato PEM) del certificato pubblico.

Il certificato utilizzato in questo progetto è stato rilasciato da 
[Let's Encrypt](https://letsencrypt.org/) e richiesto tramite il servizio offerto
da [ZeroSSL](https://zerossl.com).

Il CN di questo specifico certificato è impostato a *cns.dontesta.it*. La 
scadenza prevista per questo certificato è il 13 marzo 2019.

Di default della porta *HTTPS* è impostata a **10443** dalla variabile `APACHE_SSL_PORT`.
La variabile `APPLICATION_URL` definisce il path di redirect qualora si accedesse 
via protocollo HTTP e non HTTPS.

Le variabili `APACHE_LOG_LEVEL`e `APACHE_SSL_LOG_LEVEL`, consentono di modificare
il livello log generale e quello specifico per il modulo SSL. Il valore di default
è impostato a INFO. Per maggiori informazioni potete consultare la documentazione su
[LogLevel Directive](https://httpd.apache.org/docs/2.4/mod/core.html#loglevel).

A seguire c'è la sezione delle variabili di ambiente che sono prettamente 
specifiche per lo script di download dei certificati pubblici degli enti che 
sono autorizzati dallo stato Italiano al rilascio di certificati digitali 
per il cittadino e le aziende.

La variabile d'ambiente `GOV_TRUST_CERTS_SERVICE_TYPE_IDENTIFIER` applica il filtro
sul Service Type Identifier, il cui valore assunto nel caso della CNS è
http://uri.etsi.org/TrstSvc/Svctype/IdV


```docker
# Env for Trusted CA certificate
ENV GOV_TRUST_CERTS_DOWNLOAD_SCRIPT_URL https://raw.githubusercontent.com/amusarra/apache-httpd-ts-cns-docker/master/scripts/parse-gov-certs.py
ENV GOV_TRUST_CERTS_OUTPUT_PATH /tmp/gov/trust/certs
ENV GOV_TRUST_CERTS_SERVICE_TYPE_IDENTIFIER http://uri.etsi.org/TrstSvc/Svctype/IdV
```

La sezione a seguire del Dockerfile, contiene tutte le direttive necessarie per 
l'installazione del software indicato in precedenza. Dato che la 
distribuzione scelta è [**Ubuntu**](https://www.ubuntu.com/), il comando *apt* è
responsabile della gestione dei package, quindi dell'installazione.

```docker
# Install services, packages and do cleanup
RUN apt update \
    && apt install -y apache2 \
    && apt install -y php libapache2-mod-php \
    && apt install -y curl \
    && apt install -y python \
    && rm -rf /var/lib/apt/lists/*
```

L'installazione di cURL è necessaria per scaricare lo script `parse-gov-certs.py`,
mentre python per eseguire lo script. La sezione a seguire scarica e copia tutti
i certificati pubblici degli enti che sono autorizzati dallo stato Italiano al 
rilascio di certificati digitali per il cittadino e le aziende.

Il punto di distribuzione dei certificati (chiamato [Trust Service Status List](http://uri.etsi.org/02231/v3.1.2/)) 
è gestito dall'[Agenzia per l'Italia Digitale o AgID](https://www.agid.gov.it/) e 
raggiungibile al seguente URL https://eidas.agid.gov.it/TL/TSL-IT.xml


```docker
# Download Trusted CA certificate and copy to ssl system path
RUN rm -rf ${GOV_TRUST_CERTS_OUTPUT_PATH} \
    && curl ${GOV_TRUST_CERTS_DOWNLOAD_SCRIPT_URL} \
    | python /dev/stdin --output-folder ${GOV_TRUST_CERTS_OUTPUT_PATH} \
    && cp ${GOV_TRUST_CERTS_OUTPUT_PATH}/*.pem /etc/ssl/certs/
```
 
La sezione a seguire del Dockerfile, anch'essa esplicativa, copia le 
configurazioni di Apache opportunamente modificate al fine di abilitare 
la mutua autenticazione.


```docker
# Copy Apache configuration file
COPY configs/httpd/000-default.conf /etc/apache2/sites-available/
COPY configs/httpd/default-ssl.conf /etc/apache2/sites-available/
COPY configs/httpd/ssl-params.conf /etc/apache2/conf-available/
COPY configs/httpd/dir.conf /etc/apache2/mods-enabled/
COPY configs/httpd/ports.conf /etc/apache2/
```

La sezione a seguire del Dockerfile, copia il certificato pubblico e la relativa 
chiave privata.

```docker
# Copy Server (pub and key) cns.dontesta.it
COPY configs/certs/*_crt.pem /etc/ssl/certs/
COPY configs/certs/*_key.pem /etc/ssl/private/
``` 

La sezione a seguire del Dockerfile, copia tre script PHP a scopo di test sulla 
*document root* standard di Apache.

```docker
# Copy phpinfo test script
COPY configs/test/*.php /var/www/html/
COPY images/favicon.ico /var/www/html/favicon.ico
```

La sezione a seguire del Dockerfile, copia gli script necessari attivare il cron
e consentire l'aggiornamento dei certificati della CNS una volta al giorno.

```docker
# Copy auto-update-gov-certificates scripts and entrypoint
COPY scripts/auto-update-gov-certificates /auto-update-gov-certificates
COPY scripts/entrypoint /entrypoint
```

La sezione a seguire del Dockerfile, esegue una serie di comandi con l'obiettivo
finale di abilitare l'aggiornamento dei certificati della CNS.

```docker
# Set execute flag for entrypoint and crontab entry
# Add Cron entry auto-update-gov-certificates
# Create Project ENV for crontab
RUN chmod +x /entrypoint \
    && chmod +x /auto-update-gov-certificates \
    && echo "0 0 6 1/1 * ? * root . /project_env.sh; /auto-update-gov-certificates >> /var/log/cron.log 2>&1" > /etc/cron.d/auto-update-gov-certificates \
    && printenv | sed 's/^\(.*\)$/export \1/g' | grep -E "APACHE_|APPLICATION_URL|GOV_" > /project_env.sh \
    && chmod +x /project_env.sh
```

La sezione a seguire del Dockerfile esegue le seguenti attività:

1. abilita il modulo SSL
2. abilita il modulo headers
3. abilita il site ssl di default con la configurazione per la TS-CNS
4. abilita delle opzioni di configurazione al fine di rafforzare la sicurezza SSL/TLS
5. esegue il re-hash dei certificati. Operazione necessaria affinché Apache sia in grado di leggere i nuovi certificati


```docker
RUN a2enmod ssl \
    && a2enmod headers \
    && a2ensite default-ssl \
    && a2enconf ssl-params \
    && c_rehash /etc/ssl/certs/
```

Le due ultime direttive indicate sul Dockerfile, dichiarano la porta HTTPS 
(`APACHE_SSL_PORT`) che deve essere pubblica e il comando da eseguire per mettere 
in listen (o ascolto) il nuovo servizio Apache HTTP.

## 3 - Organizzazione
In termini di directory e file, il progetto è organizzato così come mostrato a 
seguire. Il cuore di tutto è il folder **configs**.

```
├── Dockerfile
├── configs
│    ├── certs
│    │   ├── cns-dontesta-it_crt.pem
│    │   └── cns-dontesta-it_key.pem
│    ├── httpd
│    │   ├── 000-default.conf
│    │   ├── default-ssl.conf
│    │   ├── dir.conf
│    │   ├── ports.conf
│    │   └── ssl-params.conf
│    └── test
│        ├── index.php
│        ├── certificate_policy_check.php
│        └── info.php
└── scripts
    ├── auto-update-gov-certificates
    ├── parse-gov-certs.py
    └── entrypoint
```

Il folder *configs* contiene al suo interno altri folder e file, in particolare:

1. **certs**
    * contiene il certificato del server (chiave pubblica e chiave privata);
2. **httpd**: contiene tutte le configurazioni di Apache necessarie per attivare l'autenticazione tramite la SmartCard TS-CNS;
3. **test**: contiene gli script PHP di test;
4. **scripts**: contiene gli scripts di aggiornamento certificati e abiliatazione del servizio cron

## 4 - Quickstart
L'immagine di questo progetto docker è disponibile sul mio account docker hub
[amusarra/httpd-cns-dontesta-it](
https://hub.docker.com/r/amusarra/httpd-cns-dontesta-it). Potreste quindi fin
da subito fare un test. A seguire il comando per il pull dell'immagine docker
da docker hub. Il primo comando esegue il pull dell'ultima versione (tag latest),
mentre il secondo comando esegue il pull della specifica versione dell'immagine,
in questo caso la versione 1.2.2.

```bash
docker pull amusarra/httpd-cns-dontesta-it
docker pull amusarra/httpd-cns-dontesta-it:1.2.2
```
Una volta eseguito il pull dell'immagine docker (versione 1.2.2) è possibile creare il nuovo
container tramite il comando a seguire.

```bash
docker run -i -t -d -p 10443:10443 --name=cns amusarra/httpd-cns-dontesta-it:1.2.2
```
Utilizzando il comando `docker ps` dovremmo poter vedere in lista il nuovo
container, così come indicato a seguire.

```bash
CONTAINER ID        IMAGE                                  COMMAND                  CREATED             STATUS              PORTS                      NAMES
bb707fb00e89        amusarra/httpd-cns-dontesta-it:1.2.2   "/usr/sbin/apache2ct…"   6 seconds ago       Up 4 seconds        0.0.0.0:10443->10443/tcp   cns
```

Nel caso in cui vogliate apportare delle modifiche, dovreste poi procedere con 
la build della nuova immagine e al termine della build lanciare l'immagine ottenuta. 
A seguire sono indicati i comandi *docker* da lanciare dal proprio terminale.

_I comandi docker di build e run devono essere lanciati dalla root della directory 
di progetto dopo aver fatto il clone di questo repository._

```bash
docker build -t httpd-cns-dontesta-it .
docker run -i -t -d -p 10443:10443 --name=cns httpd-cns-dontesta-it:latest
```

A questo punto sul nostro sistema dovremmo avere la nuova immagine con il 
nome **httpd-cns-dontesta-it** e in esecuzione il nuovo container chiamato
**cns**. 

Utilizzando il comando `docker images` dovremmo poter vedere in lista la nuova
immagine, così come indicato a seguire.

```
REPOSITORY                                      TAG                 IMAGE ID            CREATED             SIZE
httpd-cns-dontesta-it                           latest              1a145475d1f1        30 minutes ago      208MB
```

Utilizzando il comando `docker ps` dovremmo poter vedere in lista il nuovo
container, così come indicato a seguire.

```
CONTAINER ID        IMAGE                          COMMAND                  CREATED             STATUS              PORTS                      NAMES
65c874216624        httpd-cns-dontesta-it:latest   "/usr/sbin/apache2ct…"   36 minutes ago      Up 36 minutes       0.0.0.0:10443->10443/tcp   cns
```

Da questo momento è possibile raggiungere il servizio di autenticazione basato
sulla TS-CNS utilizzando il browser. 

Per evitare l'errore `SSL_ERROR_BAD_CERT_DOMAIN` da parte del browser, raggiungendo 
il servizio tramite la URL https://127.0.0.1:10443/, bisogna aggiungere al proprio
file di _hosts_ la riga a seguire.

```
##
# Servizio di autenticazione via TS-CNS
##
127.0.0.1       cns.dontesta.it
```

In ambiente di collaudo e/o produzione il nome del servizio o FQDN sarà registrato 
su un DNS.

Lato **server-side** è tutto pronto, non resta fare altro che un test. 
Nel caso disponiate di una vostra SmartCard TS-CNS e il vostro PC già 
configurato per l'utilizzo, potreste eseguire da subito un test puntando il 
vostro browser alla URL https://cns.dontesta.it:10443/

Puntando all'indirizzo https://cns.dontesta.it:10443/ dovrebbe accadere quanto 
segue:

1. Richiesta del PIN CODE della vostra TS-CNS;
2. Richiesta di selezione del vostro certificato digitale contenuto all'interno della CNS;
3. Visualizzazione della pagina di benvenuto.

Oltre a verificare che il certificato digitale sulla CNS sia valido, è anche
eseguito il controllo per cui tra le **Certificate Policies (Object ID: 2.5.29.32)** 
ci sia quella specifica della CNS, identificata dall'OID [1.3.76.16.2.1](http://oid-info.com/cgi-bin/display?oid=1.3.76.16.2.1&action=display).

Questo check è demandato allo script PHP `configs/test/certificate_policy_check.php` 
mostrato di seguito.

```php
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
```

Purtroppo la funzione [PeerExtList(object-ID)](https://httpd.apache.org/docs/2.4/mod/mod_ssl.html) 
del modulo *mod_ssl* non permette il check dell'estensione *CertificatePolicies*
perché strutturata.


A seguire una serie di screenshot del mio caso di test, utilizzando proprio la 
mia TS-CNS.

![Inserimento PIN TS-CNS](images/TS-CNS_InserimentoPINCODE.png)

**Figura 1 - Inserimento del PIN della TS-CNS**


![Selezione del certificato digitale](images/TS-CNS_SelezioneCertificato.png)

**Figura 2 - Selezione del certificato digitale**


![WelcomePage](images/TS-CNS_WelcomePage_1.png)

**Figura 3 - Pagina di benvenuto dopo l'autenticazione**

![WelcomePage](images/TS-CNS_WelcomePage_2.png)

**Figura 4 - Pagina di benvenuto dopo l'autenticazione**


![ErrorPage](images/TS-CNS_CertificationPolicyFailed.png)

**Figura 5 - Notifica di errore per check Policy CNS fallito**

Accedendo agli access log di Apache è possibile notare queste due informazioni utili al tracciamento delle operazioni eseguite dall'utente:

* Il protocollo SSL
* Il SSL_CLIENT_S_DN_CN 

```log
172.17.0.1 TLSv1.2 - MSRNTN77H15C351X/6120016461039008.i1ZpZfaCX/eKyikBfnF8to+M2T8= [18/Dec/2018:17:48:53 +0000] "GET / HTTP/1.1" 200 2787 "-" "Mozilla/5.0 (Macintosh; Intel Mac OSX 10.14; rv:64.0) Gecko/20100101 Firefox/64.0"
```

Il valore di `SSL_CLIENT_S_DN_CN` è inoltre impostato come **SSLUserName**, questo
fa in modo che la variabile `REMOTE_USER` sia impostata con il CN del certificato digitale 
che identifica univocamente l'utente. 

## 5 - Build, Run e Push docker image via Makefile
Al fine di semplificare le operazioni di build, run e push dell'immagine docker, 
è stato introdotto il [Makefile](https://github.com/amusarra/apache-httpd-ts-cns-docker/blob/develop/Makefile) sulla versione [1.2.2](https://github.com/amusarra/apache-httpd-ts-cns-docker/tree/v1.2.2) del progetto.

Per utilizzare il Makefile, occorre che sulla propria macchina siano installati
correttamente i tools di build.

I target disponibili sono i seguenti:

1. **build**: Target di _default_ che esegue il build dell'immagine;
2. **debug**: Esegue la build dell'immagine e successivamente apre un shell bash sul container; 
3. **run**: Esegue la build dell'immagine e successivamente crea il container lanciando l'applicazione (Apache HTTPD 2.4);
4. **clean**: Esegue un prune delle immagini;
5. **remove**: Rimuove l'ultima immagine creata;
6. **release**: Esegue la build dell'imaggine e successivamente effettua il push su dockerhub.

## 6 - Conclusioni
Lo stimolo iniziale che ha poi scatenato la nascita di questo progetto, arriva
dalle difficoltà incontrate cercando di accedere ai servizi del [Sistema Informativo Veterinario](https://www.vetinfo.it/) utilizzando la mia TS-CNS su Mac OS.

Credo che questo progetto possa essere utile a coloro che hanno la necessità di
realizzare un servizio di autenticazione basato sulla TS-CNS e non sanno magari
da dove iniziare. **Questo progetto potrebbe essere quindi un buon punto di partenza.**

Ogni suggerimento e/o segnalazione di bug è gradito; consiglio eventualmente di 
aprire una [issue](https://github.com/amusarra/apache-httpd-ts-cns-docker/issues)

Per maggiori approfondimenti riguardo questo specifico argomento, ho già in 
preparazione il prossimo articolo per [Antonio Musarra's Blog](https://www.dontesta.it).

## Project License
The MIT License (MIT)

Copyright &copy; 2018 Antonio Musarra's Blog - [https://www.dontesta.it](https://www.dontesta.it "Antonio Musarra's Blog"), 
[antonio.musarra@gmail.com](mailto:antonio.musarra@gmail.com "Antonio Musarra Email")

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

<span style="color:#D83410">
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	SOFTWARE.
<span>