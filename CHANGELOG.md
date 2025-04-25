# Changelog
Tutte le modifiche importanti a questo progetto saranno documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e questo progetto aderisce a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.3.0] - 2025-04-25
### Added
- Sullo script `parse-gov-certs.py` è stata aggiunta la funzionalità di controllo della validità del certificato (scadenza)
- Aggiunto il file requirements.txt per la gestione delle dipendenze Python qualora si volesse eseguire lo script `parse-gov-certs.py` al di fuori del container Docker

### Changed
- Aggiornamento della documentazione (README)
- Aggiornamento della documentazione doc-style dello script `parse-gov-certs.py`
- Refactoring dello script `parse-gov-certs.py` per rimuovere vecchio codice di compatibilità con Python 2
- Aggiornamento del Dockerfile per l'installazione delle dipendenze Python aggiuntive

### Fixed
- Revisione della funzione di sanificazione del nome del certificato

## [2.2.2] - 2025-04-24
### Fixed
- Sovrascrittura dei certificati con lo stesso CN (Common Name) [#28](https://github.com/italia/cie-cns-apache-docker/issues/28)

## [2.2.1] - 2024-02-09
### Fixed
- CWE-23: Relative Path Traversal
- CWE-643: Improper Neutralization of Data within XPath Expressions ('XPath Injection')
- CWE-611: Improper Restriction of XML External Entity Reference ('XXE')
- SC2086: Double quote to prevent globbing and word splitting
- SC2129: Consider using { cmd1; cmd2; } >> file instead of individual redirects


## [2.2.0] - 2022-01-16
### Changed
- Aggiornamento versione di Ubunto da 20.04 a 22.04

## [2.1.0] - 2021-08-05
### Changed
- Rinnovo certificato SSL via ZeroSSL
- Rimozione integrazione Travis CI
- Cambio FQDN da cns.dontesta.it entra-cns-cie.dontesta.it
### Added
- Integrazione con GitHub action per build immagine docker e pubblicazione su DockerHub

## [2.0.3] - 2021-01-24
### Changed
- Rinnovo certificato SSL via ZeroSSL

## [2.0.2] - 2020-09-19
### Changed
- Create docker-compose-play-with-docker.yml per PWD

## [2.0.1] - 2020-09-18
### Changed
- Fixed the volume path on docker-compose

## [2.0.0] - 2020-09-18
### Added
- Nuovo design grafico basato sul progetto [Bootstrap Italia](https://italia.github.io/bootstrap-italia/)

### Changed
- Aggiornamento della documentazione (README)
- Aggiornamento del docker-compose per il supporto dei local volumes

## [1.3.9] - 2020-09-15
### Changed
- Aggiornamento Certificati SSL/TLS via ZeroSSL
- Aggiunta del carattere di fine linea allo script parse-gov-certs.py
- Aggiornamento di Ubuntu alla versione 20.04 
- Aggiornamento della documentazione README

## [1.3.8] - 2020-02-16
### Changed
- Use parse-gov-certs.py from the local repo (by [@bfabio](https://github.com/bfabio))
- Set pipefail in order to have the piped RUNs fail even if the
last command exits successfully (by [@bfabio](https://github.com/bfabio)). 

## [1.3.7] - 2020-02-16
### Changed
- Travis CI: Moved docker login on deploy master section 

## [1.3.6] - 2020-01-25
### Changed
- Aggiornamento badge sul file README
- Aggiunta icona repository GitHub sul footer
- Aggiornamento link DockerHub

## [1.3.5] - 2020-01-24
### Changed
- Aggiornamento Certificati SSL/TLS by Let's Encrypt Authority X3

## [1.3.4] - 2019-05-22
### Changed
- Aggiornamento Certificati SSL/TLS by Let's Encrypt Authority X3

## [1.3.3] - 2019-01-11
### Changed
- Aggiornamento README circa l'OCSP

## [1.3.2] - 2019-01-11
### Changed
- Fix generazione URL

## [1.3.1] - 2019-01-11
### Added
- Aggiunta interfaccia utente meno "spartana"
- Aggiunta possibilità per configurare il parametro SSLVerifyClient
- Aggiunta la configurazione Apache per la separazione di una zona pubblica e una zona sicura

## [1.3.0] - 2019-01-08
### Added
- Supporto per la CIE

## [1.2.3] - 2019-01-04
### Changed
- Fix crontab line per aggiornamento certificati CA

## [1.2.2] - 2018-12-28
### Added
- Aggiunto Makefile per semplificazione di build, run e push dell'immagine docker

## [1.2.1] - 2018-12-20
### Added
- Aggiornamento giornaliero dei certificati GOV tramite job cron.
- File di log (access e error log) Apache per virtual host (esempio: cns.dontesta.it_access.log e cns.dontesta.it_error.log).

## [1.2.0] - 2018-12-18
### Added
- Verifica della Certificate Policies che sia quella della CNS (issue #1)
- Impostazione SSLUserName con SSL_CLIENT_S_DN_CN
- Aggiunta sull'access log di Apache la versione del protocollo SSL
- Aggiunte due env sul Dockerfile per modificare il livello di log standard di Apache e del modulo SSL

### Changed
- Modifica della Welcome Page che mostra il certificato digitale in formato PEM e in formato leggibile (human readable)
- Aggiunto lo script PHP certificate_policy_check che esegue il check della Certificate Policies

## [1.1.0] - 2018-12-17
### Added
- Download automatico dei certificati CA Governativi e copia in /etc/ssl/certs (issue #2)
- Filtro delle sole CA che sono dedicate al rilascio delle CNS (issue #2)

## [1.0.0] - 2018-12-15
Prima release del progetto. Fare riferimento al README.md per maggiori dettagli
circa il progetto.
