<?php
require_once 'certificate_policy_check.php';
?>

<!DOCTYPE html>
<html >
<head>
  <!-- Site made with Mobirise Website Builder v4.8.10, https://mobirise.com -->
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="generator" content="Mobirise v4.8.10, mobirise.com">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <link rel="shortcut icon" href="../assets/images/iconfinder-21-3319623-1-174x174.png" type="image/x-icon">
  <meta name="description" content="">
  <title>Benvenuto in CIE/TS-CNS Test Environment</title>
  <link rel="stylesheet" href="../assets/web/assets/mobirise-icons2/mobirise2.css">
  <link rel="stylesheet" href="../assets/web/assets/mobirise-icons/mobirise-icons.css">
  <link rel="stylesheet" href="../assets/tether/tether.min.css">
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap-grid.min.css">
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap-reboot.min.css">
  <link rel="stylesheet" href="../assets/dropdown/css/style.css">
  <link rel="stylesheet" href="../assets/socicon/css/styles.css">
  <link rel="stylesheet" href="../assets/animatecss/animate.min.css">
  <link rel="stylesheet" href="../assets/theme/css/style.css">
  <link rel="stylesheet" href="../assets/mobirise/css/mbr-additional.css" type="text/css">
  
  
  
</head>
<body>
  <section class="menu cid-reL6LYwxD6" once="menu" id="menu1-4">

    

    <nav class="navbar navbar-expand beta-menu navbar-dropdown align-items-center navbar-toggleable-sm">
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>
        <div class="menu-logo">
            <div class="navbar-brand">
                <span class="navbar-logo">
                    <a href="<?= getenv('REQUEST_SCHEME') . '://' . getenv('SERVER_NAME') . ':' . getenv('SERVER_PORT') ?>">
                         <img src="../assets/images/iconfinder-21-3319623-1-174x174.png" alt="Mobirise" title="" style="height: 5.6rem;">
                    </a>
                </span>
                <span class="navbar-caption-wrap"><a class="navbar-caption text-white display-4" href="<?= getenv('REQUEST_SCHEME') . '://' . getenv('SERVER_NAME') . ':' . getenv('SERVER_PORT') ?>">CIE/TS-CNS</a></span>
            </div>
        </div>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav nav-dropdown" data-app-modern-menu="true"><li class="nav-item">
                    <a class="nav-link link text-white display-4" href="https://mobirise.com">
                        </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link link text-white display-4" href="https://mobirise.com">
                        </a>
                </li><li class="nav-item"><a class="nav-link link text-white display-4" href="https://www.agid.gov.it/">
                        
                        Agenzia per l'Italia Digitale</a></li><li class="nav-item dropdown"><a class="nav-link link text-white display-4" href="https://developers.italia.it/" aria-expanded="true">
                        
                        Developers Italia</a></li></ul>
            <div class="navbar-buttons mbr-section-btn">
                <a class="btn btn-sm btn-primary display-4" href="<?= getenv('REQUEST_SCHEME') . '://' . getenv('SERVER_NAME') . ':' . getenv('SERVER_PORT') ?>/secure">
                    <span class="mbri-user mbr-iconfont mbr-iconfont-btn"></span><?= getenv('SSL_CLIENT_S_DN_G')?> <?= getenv('SSL_CLIENT_S_DN_S')?>
                </a>
            </div>
        </div>
    </nav>
</section>

<section class="engine"><a href="https://mobirise.info/d">free website maker</a>
</section>
<section class="cid-reL6LZ0RFh mbr-fullscreen mbr-parallax-background" id="header2-5">

    

    <div class="mbr-overlay" style="opacity: 0.5; background-color: rgb(118, 118, 118);"></div>

    
    <div class="container align-center">
        <div class="row justify-content-md-center">
            <div class="mbr-white col-md-10">
                <h1 class="mbr-section-title mbr-bold pb-3 mbr-fonts-style display-1">Ecco i tuoi dati!</h1>
                <p class="mbr-text pb-3 mbr-fonts-style display-7">Codice Fiscale: <?= substr(getenv('SSL_CLIENT_S_DN_CN'), 0, 16)?></p>
                <p class="mbr-text pb-3 mbr-fonts-style display-7">CNS/CIE Rilasciata da (Common Name): <?= getenv('SSL_CLIENT_I_DN_CN')?></p>
                <p class="mbr-text pb-3 mbr-fonts-style display-7">Certificato rilasciato da (Organization): <?= getenv('SSL_CLIENT_I_DN_O')?></p>
                <p class="mbr-text pb-3 mbr-fonts-style display-7">Verifica certificato: <?= getenv('SSL_CLIENT_VERIFY')?></p>
                <p class="mbr-text pb-3 mbr-fonts-style display-7">Validità del certificato: dal <?= getenv('SSL_CLIENT_V_START')?> al <?= getenv('SSL_CLIENT_V_END')?></p>
                <div class="mbr-section-btn">
                    <a class="btn btn-md btn-primary display-4" href="https://github.com/italia/cie-cns-apache-docker">VAI AL PROGETTO</a>
                    <a class="btn btn-md btn-danger display-4" href="#" data-toggle="modal" data-target="#exampleModalButton">DETTAGLI CERTIFICATO</a>
                </div>
            </div>
        </div>
    </div>
    <div class="mbr-arrow hidden-sm-down" aria-hidden="true">
        <a href="#next">
            <i class="mbri-down mbr-iconfont"></i>
        </a>
    </div>
</section>

<section class="cid-reL6LZqzcp" id="footer1-6">

    

    

    <div class="container">
        <div class="media-container-row content text-white">
            <div class="col-12 col-md-3">
                <div class="media-wrap">
                    <a href="<?= getenv('REQUEST_SCHEME') . '://' . getenv('SERVER_NAME') . ':' . getenv('SERVER_PORT') ?>">
                        <img src="../assets/images/iconfinder-21-3319623-1-174x174.png" alt="Mobirise" title="">
                    </a>
                </div>
            </div>
            <div class="col-12 col-md-3 mbr-fonts-style display-7">
                <h5 class="pb-3">AgID</h5>
                <p class="mbr-text"><a href="https://www.agid.gov.it/it/piattaforme/carta-nazionale-servizi">Carta Nazionale dei Servizi</a><br><a href="https://www.agid.gov.it/sites/default/files/repository_files/documentazione_trasparenza/strutturacertificatoautenticazionecns_v1.1_.pdf">Struttura del certificato di autenticazione</a><br><a href="https://www.agid.gov.it/it/piattaforme/carta-nazionale-servizi">Sistema operativo (APDU) v1.1.6</a><br><a href="https://www.agid.gov.it/sites/default/files/repository_files/documentazione_trasparenza/strutturacertificatoautenticazionecns_v1.1_.pdf">Struttura del certificato di autenticazione</a><a href="https://www.agid.gov.it/it/piattaforme/carta-nazionale-servizi"><br></a><a href="https://www.agid.gov.it/it/piattaforme/carta-nazionale-servizi"><br></a><a href="https://www.agid.gov.it/it/piattaforme/carta-nazionale-servizi"><br></a><br></p>
            </div>
            <div class="col-12 col-md-3 mbr-fonts-style display-7">
                <h5 class="pb-3">
                    Ministero dell'interno</h5>
                <p class="mbr-text"><a href="https://www.cartaidentita.interno.gov.it/descrizione/">Progetto CIE</a><br><a href="https://www.cartaidentita.interno.gov.it/caratteristiche-del-documento/">Caratteristiche della CIE</a><br><a href="https://www.cartaidentita.interno.gov.it/il-microprocessore/">Il microprocessore</a></p>
            </div>
            <div class="col-12 col-md-3 mbr-fonts-style display-7">
                <h5 class="pb-3">
                    Links
                </h5>
                <p class="mbr-text"><a href="https://www.dontesta.it">Antonio Musarra's Blog</a>&nbsp;<br><a href="https://github.com/italia/cie-cns-apache-docker">Progetto italia/cie-cns-apache-docker</a>&nbsp;<br><a href="https://hub.docker.com/r/amusarra/cie-cns-apache-httpd">Docker Image</a></p>
            </div>
        </div>
        <div class="footer-lower">
            <div class="media-container-row">
                <div class="col-sm-12">
                    <hr>
                </div>
            </div>
            <div class="media-container-row mbr-white">
                <div class="col-sm-6 copyright">
                    <p class="mbr-text mbr-fonts-style display-7"><a href="https://www.dontesta.it">
                        © Copyright 2018 Antonio Musarra's Blog - All Rights Reserved
                    </a></p>
                </div>
                <div class="col-md-6">
                    <div class="social-list align-right">
                        <div class="soc-item">
                            <a href="https://twitter.com/antonio_musarra" target="_blank">
                                <span class="mbr-iconfont mbr-iconfont-social socicon-twitter socicon"></span>
                            </a>
                        </div>
                        <div class="soc-item">
                            <a href="https://www.facebook.com/antoniomusarrablog/" target="_blank">
                                <span class="mbr-iconfont mbr-iconfont-social socicon-facebook socicon"></span>
                            </a>
                        </div>
                        <div class="soc-item">
                            <a href="https://www.youtube.com/channel/UC5D3_EtVPbZYUhrUcEK_THA" target="_blank">
                                <span class="mbr-iconfont mbr-iconfont-social socicon-youtube socicon"></span>
                            </a>
                        </div>
                        <div class="soc-item">
                            <a href="https://www.linkedin.com/in/amusarra/" target="_blank">
                                <span class="mbr-iconfont mbr-iconfont-social socicon-linkedin socicon"></span>
                            </a>
                        </div>
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="exampleModalButton" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Dettagli del tuo Certificato</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="false">×</span>
        </button>
      </div>
    <div class="modal-body">
        <h2>Parsed Certificato Digitale (client)</h2>
        <pre><?php print_r($ssl)?></pre>

        <h2>PEM Certificato Digitale (client)</h2>
            <pre>
                <?= getenv('SSL_CLIENT_CERT') ?>
            </pre>
    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
      </div>
    </div>
  </div>
</div>

  <script src="../assets/web/assets/jquery/jquery.min.js"></script>
  <script src="../assets/popper/popper.min.js"></script>
  <script src="../assets/tether/tether.min.js"></script>
  <script src="../assets/bootstrap/js/bootstrap.min.js"></script>
  <script src="../assets/dropdown/js/script.min.js"></script>
  <script src="../assets/viewportchecker/jquery.viewportchecker.js"></script>
  <script src="../assets/parallax/jarallax.min.js"></script>
  <script src="../assets/smoothscroll/smooth-scroll.js"></script>
  <script src="../assets/touchswipe/jquery.touch-swipe.min.js"></script>
  <script src="../assets/theme/js/script.js"></script>
  
  
 <div id="scrollToTop" class="scrollToTop mbr-arrow-up"><a style="text-align: center;"><i></i></a></div>
    <input name="animation" type="hidden">
  </body>
</html>