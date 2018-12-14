<!DOCTYPE html>
<html>
    <body>
        <section>
            <h1>Benvenuto <?= getenv('SSL_CLIENT_S_DN_G')?> <?= getenv('SSL_CLIENT_S_DN_S')?></h1>
            <p>Autenticazione avvenuta con successo</p>
            <p>Codice Fiscale: <?= substr(getenv('SSL_CLIENT_S_DN_CN'), 0, 16)?></p>
            <p>CNS Rilasciata da: <?= getenv('SSL_CLIENT_I_DN_CN')?></p>
            <p>Certificato rilasciato da: <?= getenv('SSL_CLIENT_I_DN_O')?></p>
            <p>Verifica certificato: <?= getenv('SSL_CLIENT_VERIFY')?></p>
            <p>Validità del certificato: dal <?= getenv('SSL_CLIENT_V_START')?> al <?= getenv('SSL_CLIENT_V_END')?></p>
        </section>
    </body>
</html>
