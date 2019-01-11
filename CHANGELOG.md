# Changelog
Tutte le modifiche importanti a questo progetto saranno documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e questo progetto aderisce a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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