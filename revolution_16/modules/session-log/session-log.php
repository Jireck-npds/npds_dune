<?php
/************************************************************************/
/* DUNE by NPDS                                                         */
/* ===========================                                          */
/*                                                                      */
/* Session and log Viewer Copyright (c) 2009 - Tribal-Dolphin           */
/*                                                                      */
/* NPDS Copyright (c) 2002-2012 by Philippe Brunier                     */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

if (!stristr($_SERVER['PHP_SELF'],"admin.php")) { Access_Error(); }
$f_meta_nom ='session_log';
$f_titre = adm_translate("Gestion des Logs");
//==> controle droit
admindroits($aid,$f_meta_nom);
//<== controle droit

global $language, $ModPath, $ModStart;
$old_language=$language;
include_once("modules/upload/upload.conf.php");
if ($DOCUMENTROOT=="") {
   global $DOCUMENT_ROOT;
   if ($DOCUMENT_ROOT) {
      $DOCUMENTROOT=$DOCUMENT_ROOT;
   } else {
      $DOCUMENTROOT=$_SERVER['DOCUMENT_ROOT'];
   }
}
$FileSecure = $DOCUMENTROOT.$racine."/slogs/security.log";
$FileUpload = $DOCUMENTROOT.$rep_log;
$RepTempFil = $DOCUMENT_ROOT.$rep_cache;


$language=$old_language;
include ("modules/$ModPath/lang/session-log-$language.php");
$ThisFile="admin.php?op=Extend-Admin-SubModule&amp;ModPath=".$ModPath."&amp;ModStart=".$ModStart;

function action_log($ThisFile,$classe) {
   echo '<p align="center"><br />
   <a class="btn btn-danger btn-sm" href="'.$ThisFile.'&amp;subop=vidlog&amp;log='.$classe.'" >'.SessionLog_translate("Vider le fichier").'</a>
   <a class="btn btn-primary btn-sm" href="'.$ThisFile.'&amp;subop=mailog&amp;log='.$classe.'">'.SessionLog_translate("Recevoir le fichier par mail").'</a>
   <a class="btn btn-danger" href="'.$ThisFile.'&amp;subop=vidtemp">'.SessionLog_translate("Effacer les fichiers temporaires").'</a>
   </p>';
}
    adminhead ($f_meta_nom, $f_titre, $adminimg);
echo "
<ol class=\"breadcrumb\">
  <li><a href=\"".$ThisFile."&subop=session\" class=\"box\">".SessionLog_translate("Liste des Sessions")."</a></li>
  <li><a href=\"".$ThisFile."&subop=security\" class=\"box\">".SessionLog_translate("Liste des Logs")." : ".SessionLog_translate("SECURITE")."</a></li>
</ol>";

   if ($FileUpload!=$FileSecure) {
      echo " | <a href=\"".$ThisFile."&subop=upload\" class=\"box\">".SessionLog_translate("Liste des Logs")." : ".SessionLog_translate("TELECHARGEMENT")."</a>";
   }
//   echo "</td></tr></table>\n";
//   echo "<br />\n";

   // Voir les sessions
   if ($subop=="session") {
      echo '
      <h3>'.SessionLog_translate("Liste des Sessions").'</h3>
      <table id="tad_ses" data-toggle="table" data-striped="true" data-show-toggle="true" data-search="true" data-mobile-responsive="true">
         <thead>
            <tr>
               <th data-sortable="true">'.SessionLog_translate("Nom").'</th>
               <th data-sortable="true">'.SessionLog_translate("@ IP").'</th>
               <th data-sortable="true">'.SessionLog_translate("@ IP r�solue").'</th>
               <th data-sortable="true">'.SessionLog_translate("URI").'</th>
               <th data-sortable="true">'.SessionLog_translate("Agent").'</th>
            </tr>
         </thead>
         <tbody>';
//      echo "<td width=\"10%\" style=\"font-size: 10px;\">".SessionLog_translate("Nom")."</td><td width=\"5%\" style=\"font-size: 10px;\">".SessionLog_translate("@ IP")."</td><td width=\"15%\" style=\"font-size: 10px;\">".SessionLog_translate("@ IP r�solue")."</td><td style=\"font-size: 10px;\">URI</td><td style=\"font-size: 10px;\">Agent</td></tr>";
      $result=sql_query("SELECT username, host_addr, guest, uri, agent FROM ".$NPDS_Prefix."session");
      while (list($username, $host_addr, $guest, $uri, $agent)=sql_fetch_row($result)) {
         if ($username==$host_addr) {global $anonymous; $username=$anonymous;}
         if (preg_match('#(crawl|bot|spider|yahoo)#',strtolower($agent))) $agent="Bot"; else $agent="Browser";
         echo '
            <tr>
               <td class="small">'.$username.'</td>
               <td class="small">'.$host_addr.'</td>
               <td class="small">'.gethostbyaddr($host_addr).'</td>
               <td class="small">'.split_string_without_space($uri,40).'</td>
               <td class="small">'.$agent.'</td>
           </tr>';
      }
      echo '
      </tbody>
      </table>'."\n";
   }

   // D�tails @IP
   if ($subop=="info") {
      echo '<h3>'.SessionLog_translate("Informations sur l'IP").'</h3>';
      $hostname = gethostbyaddr($theip);
      if ($theip != $hostname) {
         $domfai = explode(".",$hostname);
         $prov = $domfai[count($domfai)-2].'.'.$domfai[count($domfai)-1];
         if ($prov == "co.jp" or $prov == "co.uk" ) {
            $provider = $domfai[sizeof($domfai)-3].".".$prov;
         } else {
            $provider = $prov;
         }
      } else {
         $hostname = $theip;
      }
      echo "<p align=\"center\">";
      echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\">\n";
      echo "<tr><td align=\"center\">".SessionLog_translate("@ IP")."</td>";
      echo "<td align=\"center\">".SessionLog_translate("@ IP r�solue")."</td>";
      echo "<td align=\"center\">".SessionLog_translate("Fournisseur")."</td>";
      echo "</tr><tr>";
      echo "<td align=\"center\">".$theip."</td>";
      echo "<td align=\"center\">".$hostname."</td>";
      echo "<td align=\"center\">".$provider;
      echo "</tr></table>";
      $subop="security";
      echo "</p><br />";
   }

   // Vider les Logs
   if ($subop=="vidlog") {
      if ($log=="security") {
         if (file_exists($FileSecure)) {
            @fopen($FileSecure, "w");
            @fclose($FileSecure);
         }
      }
      if ($log=="upload") {
         if (file_exists($FileUpload)) {
            @fopen($FileUpload, "w");
            @fclose($FileUpload);
         }
      }
   }

   // Email du contenu des Logs
   if ($subop=="mailog") {
      if ($log=="security") {
         if (file_exists($FileSecure)) {
            $Mylog=$FileSecure;
         }
      }
      if ($log=="upload") {
         if (file_exists($FileUpload)) {
            $Mylog=$FileUpload;
         }
      }
      $subject = SessionLog_translate("Fichier de Log de")." ".$sitename;
      send_email($adminmail, $subject, $Mylog, "", true, "mixed");
   }

   // Vider le r�pertoire temporaire
   if ($subop=="vidtemp") {
      if (is_dir($RepTempFil)) {
         $dh = opendir($RepTempFil);
         $i = 0;
         while(false!==($filename = readdir($dh))) {
            if ($filename === '.' OR $filename === '..' OR $filename === 'index.html') continue;
            @unlink($RepTempFil.$filename);
         }
      }
   }

   // Voir le contenu du fichier security.log
   if ($subop=="security") {
      if (file_exists($FileSecure)) {
         if (filesize($FileSecure) != 0) {
            $fd = fopen($FileSecure, "r");
            while (!feof ($fd)) {
               $buffer = fgets($fd, 4096);
               if (strlen($buffer)>10) {
                  if (stristr($buffer,'Upload')) {
                     $UpLog.='<tr><td style="font-size:10px;">'.$buffer.'</td></tr>'."\n";
                  } else {
                    $ip=substr(strrchr($buffer,"=>"),2);
                    $SecLog.='
                    <tr>
                    <td style="font-size:10px;">'.$buffer.'</td>
                    <td><a href="'.$ThisFile.'&amp;subop=info&amp;theip='.$ip.'" class="noir">'.SessionLog_translate("Infos").'</a></td>
                    </tr>'."\n";
                  }
               }
            }
            fclose($fd);
         }
      }
      echo '
      <h3><a class="btn" data-toggle="collapse" href="#tog_tad_slog" aria-expanded="false" aria-controls="tog_tad_slog"><i class="fa fa-bars fa-lg"></i></a>'.SessionLog_translate("Liste des Logs").' '.SessionLog_translate("SECURITE").' : <i>security.log</i></h3>
      <div id="tog_tad_slog" class="collapse">
      <table id="tad_slog" data-toggle="table" data-striped="true" data-search="true" data-mobile-responsive="true">
      <thead>
         <tr>
            <th data-sortable="true">Logs</th>
            <th>Fonctions</th>
         </tr>
      </thead>
      <tbody>'."\n";
      echo $SecLog;
      echo '
      </tbody>
      </table>
      </div>
      <h3><a class="btn" data-toggle="collapse" href="#tog_tad_tlog" aria-expanded="false" aria-controls="tog_tad_tlog"><i class="fa fa-bars fa-lg"></i></a>'.SessionLog_translate("Liste des Logs").' '.SessionLog_translate("TELECHARGEMENT").' : <i>security.log</i></h3>
      <div id="tog_tad_tlog" class="collapse">
      <table id="tad_tlog" data-toggle="table" data-striped="true" data-search="true" data-mobile-responsive="true">
      <thead>
         <tr>
            <th>Logs</th>
         </tr>
      </thead>
      <tbody>'."\n";
      echo $UpLog;
      echo '
      </tbody>
      </table>
      </div>'."\n";
      action_log($ThisFile,"security");
   }

   // Voir le contenu du fichier d'upload si diff�rent de security.log (upload.conf.php)
   if ($subop=="upload") {
      if ($FileUpload!=$FileSecure) {
         if (file_exists($FileUpload)) {
            if (filesize($FileUpload) != 0) {
               $fd = @fopen($FileUpload, "r");
               while (!feof ($fd)) {
                  $rowcolor=tablos();
                  $buffer = fgets($fd, 4096);
                  $UpLog.="<tr $rowcolor><td style=\"font-size:10px;\">".$buffer."</td></tr>";
               }
               @fclose($fd);
            }
         }
         echo "<table><tr><td class=\"header\">\n";
         echo SessionLog_translate("Liste des Logs")." ".SessionLog_translate("TELECHARGEMENT")." : <i>$FileUpload</i>";
         echo "</td></tr></table>\n";
         echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\">\n";
         echo $UpLog;
         echo "</table>";
         action_log($ThisFile,"upload");
      }
   }
closetable();
?>