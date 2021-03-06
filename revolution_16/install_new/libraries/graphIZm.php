<?php
/************************************************************************/
/* DUNE by NPDS                                                         */
/* ===========================                                          */
/*                                                                      */
/* NPDS Copyright (c) 2002-2016 by Philippe Brunier                     */
/* IZ-Xinstall version : 1.2                                            */
/*                                                                      */
/* Auteurs : v.0.1.0 EBH (plan.net@free.fr)                             */
/*         : v.1.1.1 jpb, phr                                           */
/*         : v.1.1.2 jpb, phr, dev, boris                               */
/*         : v.1.1.3 dev - 2013                                         */
/*         : v.1.2 phr, jpb - 2016                                      */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

function entete() {
   global $cms_logo, $cms_name, $stage;
   echo '<html>
<head>
<meta charset="utf-8">
<title>NPDS IZ-Xinstall - Installation &amp; Configuration</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="content-script-type" content="text/javascript" />
  <meta http-equiv="content-style-type" content="text/css" />
  <meta http-equiv="expires" content="0" />
  <meta http-equiv="pragma" content="no-cache" />
  <meta http-equiv="identifier-url" content="" />
  <meta name="author" content="Developpeur, EBH, jpb, phr" />
  <meta name="owner" content="npds.org" />
  <meta name="reply-to" content="developpeur@npds.org" />
  <meta name="language" content="fr" />
  <meta http-equiv="content-language" content="fr, fr-be, fr-ca, fr-lu, fr-ch" />
  <meta name="description" content="NPDS IZ-Xinstall" />
  <meta name="keywords" content="NPDS, Installateur automatique" />
  <meta name="rating" content="general" />
  <meta name="distribution" content="global" />
  <meta name="copyright" content="npds.org 2001-2016" />
  <meta name="revisit-after" content="15 days" />
  <meta name="resource-type" content="document" />
  <meta name="robots" content="none" />
  <meta name="generator" content="NPDS IZ-Xinstall" />
<link rel="stylesheet" href="lib/font-awesome-4.5.0/css/font-awesome.min.css" />
<link rel="stylesheet" href="lib/bootstrap-4.0.0-alpha.2/dist/css/bootstrap.css" />
<script type="text/javascript" src="lib/bootstrap-4.0.0-alpha.2/dist/js/bootstrap.min.js"></script>
<style>
body {
background-color: #cccccc;
}
</style>
</head>
<body>
<div class="container">
   <div class="page-header">
      <div class="row">
         <div class="col-md-10"></div>
         <div class="col-md-2">'.$cms_name.'</div>
      </div>
      <div class="row">
      <div class="col-sm-2 hidden-xs-down"><img class="img-fluid" src="install/images/header.png" /></div>
         <div id="logo_header" class="col-sm-10">
         <h1 class="display-3">REVOLUTION 2016<br /><small class="text-muted"><em>installation automatique</em></small></h1>
         </div>
      </div>
   </div>
<hr class="lead" />';
}
function pied_depage() {
   global $stage;
   echo '
   <div class="row">
      <div class="col-md-12 text-xs-center">2016 - NPDS IZ-Xinstall version : 1.2</div>
   </div>
</div>
</body>
</html>';
   exit();
}
function page_message($chaine) {
   entete();
   echo '
   <h2>'.$chaine.'</h2>';
   pied_depage();
}
function menu() {
   global $langue, $colorst1, $colorst2, $colorst3, $colorst4, $colorst5, $colorst6, $colorst7, $colorst8, $colorst9, $colorst10;
   $lang_symb = substr($langue, 0, 3);
   if(file_exists($fichier_lang = 'install/languages/'.$langue.'/install-'.$lang_symb.'.php')) {
      @include $fichier_lang;
   }
   else {
      @include('install/languages/francais/install-fre.php');
   }
   echo '<div class="row">
      <div class="col-md-3"><strong>
         <ul class="list-unstyled">
            <li style="color: '.$colorst1.'">'.ins_translate('Langue').'</li>
            <li style="color: '.$colorst2.'">'.ins_translate('Bienvenue').'</li>
            <li style="color: '.$colorst3.'">'.ins_translate('Licence').'</li>
            <li style="color: '.$colorst4.'">'.ins_translate('Vérification des fichiers').'</li>
            <li style="color: '.$colorst5.'">'.ins_translate('Paramètres de connexion').'</li>
            <li style="color: '.$colorst6.'">'.ins_translate('Autres paramètres').'</li>
            <li style="color: '.$colorst7.'">'.ins_translate('Base de données').'</li>
            <li style="color: '.$colorst8.'">'.ins_translate('Compte Admin').'</li>
            <li style="color: '.$colorst9.'">'.ins_translate('Module UPload').'</li>
            <li style="color: '.$colorst10.'">'.ins_translate('Fin').'</li>
         </ul></strong>
      </div>
      <div class="col-md-9">';
}
?> 