# Apache HTTP 2.4 per SmartCard TS-CNS (Tessera Sanitaria - Carta Nazionale Servizi)
[![Antonio Musarra's Blog](https://img.shields.io/badge/maintainer-Antonio_Musarra's_Blog-purple.svg?colorB=6e60cc)](https://www.dontesta.it)
[![Twitter Follow](https://img.shields.io/twitter/follow/antonio_musarra.svg?style=social&label=%40antonio_musarra%20on%20Twitter&style=plastic)](https://twitter.com/antonio_musarra)

L'obiettivo di questo progetto è quello di fornire un template pronto all'uso
che realizza un sistema di autenticazione tramite la SmartCard **TS-CNS** basato 
su [Apache HTTP](http://httpd.apache.org/docs/2.4/). Ognuno può poi modificare 
o specializzare questo progetto sulla base delle proprie esigenze.

Si tratta di un progetto [docker](https://www.docker.com/) per la creazione di 
un container che implementa un sistema di **mutua autenticazione o autenticazione bilaterale SSL/TLS**.
Questo meccanismo di autenticazione richiede anche il certificato digitale 
da parte del client, certificato che in questo caso risiede 
all'interno della TS-CNS.

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
ENV APPLICATION_URL https://$APACHE_SERVER_NAME:$APACHE_SSL_PORT
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

La sezione a seguire del Dockerfile, contiene tutte le direttive necessarie per 
l'installazione del software indicato in precedenza. Dato che la 
distribuzione scelta è [**Ubuntu**](https://www.ubuntu.com/), il comando *apt* è
responsabile della gestione dei package, quindi dell'installazione.

```docker
# Install services, packages and do cleanup
RUN apt update \
    && apt install -y apache2 \
    && apt install -y php libapache2-mod-php \
    && rm -rf /var/lib/apt/lists/*
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

La sezione a seguire del Dockerfile, copia tutti i certificati pubblici degli 
enti che sono autorizzati dallo stato Italiano al rilascio di certificati digitali 
per il cittadino e le aziende.

```docker
# Copy CNS certs
COPY configs/certs/cns/*.pem /etc/ssl/certs/
```

Il punto di distribuzione dei certificati (chiamato [Trust Service Status List](http://uri.etsi.org/02231/v3.1.2/)) 
è gestito dall'[Agenzia per l'Italia Digitale o AgID](https://www.agid.gov.it/) e 
raggiungibile al seguente URL https://eidas.agid.gov.it/TL/TSL-IT.xml


La sezione a seguire del Dockerfile, copia il certificato pubblico e la relativa 
chiave privata.

```docker
# Copy Server (pub and key) cns.dontesta.it
COPY configs/certs/*_crt.pem /etc/ssl/certs/
COPY configs/certs/*_key.pem /etc/ssl/private/
``` 

La sezione a seguire del Dockerfile, copia i due script PHP di test sulla 
*document root* standard di Apache.

```docker
# Copy phpinfo test script
COPY configs/test/info.php /var/www/html/info.php
COPY configs/test/index.php /var/www/html/index.php
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
└── configs
    ├── certs
    │   ├── cns [358 entries exceeds filelimit, not opening dir]
    │   ├── cns-dontesta-it_crt.pem
    │   └── cns-dontesta-it_key.pem
    ├── httpd
    │   ├── 000-default.conf
    │   ├── default-ssl.conf
    │   ├── dir.conf
    │   ├── ports.conf
    │   └── ssl-params.conf
    └── test
        ├── index.php
        └── info.php
```

Il folder *configs* contiene al suo interno altri folder e file, in particolare:

1. **certs**
    * contiene il certificato del server (chiave pubblica e chiave privata);
    * il folder *cns* contiene gli attuali 358 certificati pubblici (in formato PEM degli enti autorizzati).
2. **httpd**: contiene tutte le configurazioni di Apache necessarie per attivare l'autenticazione tramite la SmartCard TS-CNS;
3. **test**: contiene gli script PHP di test. 

## 4 - Quickstart
L'immagine di questo progetto docker è disponibile sul mio account docker hub
[amusarra/httpd-cns-dontesta-it](
https://hub.docker.com/r/amusarra/httpd-cns-dontesta-it). Potreste quindi fin
da subito fare un test. A seguire il comando per il pull dell'immagine docker
da docker hub.

```bash
docker run -i -t -d -p 10443:10443 --name=cns amusarra/httpd-cns-dontesta-it:1.0.0
```
Una volta eseguito il pull dell'immagine docker è possibile creare il nuovo
container tramite il comando a seguire.

```bash
docker run -i -t -d -p 10443:10443 --name=cns amusarra/httpd-cns-dontesta-it:1.0.0
```
Utilizzando il comando `docker ps` dovremmo poter vedere in lista il nuovo
container, così come indicato a seguire.

```bash
CONTAINER ID        IMAGE                                  COMMAND                  CREATED             STATUS              PORTS                      NAMES
bb707fb00e89        amusarra/httpd-cns-dontesta-it:1.0.0   "/usr/sbin/apache2ct…"   6 seconds ago       Up 4 seconds        0.0.0.0:10443->10443/tcp   cns
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

A seguire una serie di screenshot del mio caso di test, utilizzando proprio la 
mia TS-CNS.

![Inserimento PIN TS-CNS](images/TS-CNS_InserimentoPINCODE.png)

**Figura 1 - Inserimento del PIN della TS-CNS**


![Selezione del certificato digitale](images/TS-CNS_SelezioneCertificato.png)

**Figura 2 - Selezione del certificato digitale**


![WelcomePage](images/TS-CNS_WelcomePage.png)

**Figura 3 - Pagina di benvenuto dopo l'autenticazione**

## 5 - Conclusioni
Lo stimolo iniziale che ha poi scatenato la nascita di questo progetto, arriva
dalle difficoltà incontrate cercando di accedere ai servizi del [Sistema Informativo Veterinario](https://www.vetinfo.it/) utilizzando la mia TS-CNS su Mac OS.

Credo che questo progetto possa essere utile a coloro che hanno la necessità di
realizzare un servizio di autenticazione basato sulla TS-CNS e non sanno magari
da dove iniziare. **Questo progetto potrebbe essere quindi un buon punto di partenza.**

Per maggiori approfondimenti riguardo questo specifico argomento, ho già in 
preparazione il prossimo articolo per [Antonio Musarra's Blog](https://www.dontesta.it).

## Project License
The MIT License (MIT)

Copyright &copy; 2018 Antonio Musarra's Blog - [https://www.dontesta.it](https://www.dontesta.it "Antonio Musarra's Blog") , 
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