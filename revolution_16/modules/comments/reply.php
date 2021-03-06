<?php
/************************************************************************/
/* DUNE by NPDS                                                         */
/* ===========================                                          */
/*                                                                      */
/* Based on PhpNuke 4.x source code                                     */
/* Based on Parts of phpBB                                              */
/*                                                                      */
/* NPDS Copyright (c) 2002-2015 by Philippe Brunier                     */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/
if (!function_exists("Mysql_Connexion")) {
   die();
}

include('functions.php');
include('auth.php');

filtre_module($file_name);
if (file_exists("modules/comments/$file_name.conf.php")) {
   include ("modules/comments/$file_name.conf.php");
} else {
   die();
}

settype($cancel,'string');
settype($url_ret,'string');
if ($cancel) {
   header("Location: $url_ret");
}

settype($forum,"integer");
if ($forum>=0)
   die();

// gestion des params du 'forum' : type, acc�s, mod�rateur ...
$forum_name = 'comments';
$forum_type=0;
$allow_to_post=false;
if ($anonpost) $forum_access=0;
else $forum_access=1;

global $user;
if (($moderate==1) and $admin)
   $Mmod=true;
elseif ($moderate==2) {
   $userX=base64_decode($user);
   $userdata=explode(":", $userX);
   $result=sql_query("SELECT level FROM ".$NPDS_Prefix."users_status WHERE uid='".$userdata[0]."'");
   list($level)=sql_fetch_row($result);
   if ($level>=2)
      $Mmod=true;
   else
      $Mmod=false;
} else
   $Mmod=false;
// gestion des params du 'forum' : type, acc�s, mod�rateur ...

if (isset($submitS)) {
   $stop=0;
   if ($message=='') $stop=1;
   if (!$user) {
      if ($forum_access==0) {
         $userdata = array("uid" => 1);
         include("header.php");
      } else {
         if (($username=="") or ($password=="")) {
            forumerror('0027');
         } else {
            $result = sql_query("SELECT pass FROM ".$NPDS_Prefix."users WHERE uname='$username'");
            list($pass) = sql_fetch_row($result);
            if (!$system) {
               $passwd=crypt($password,$pass);
            } else {
               $passwd=$password;
            }
            if ((strcmp($passwd,$pass)==0) and ($pass != "")) {
               $userdata = get_userdata($username);
               include("header.php");
            } else {
               forumerror('0028');
            }
         }
      }
   } else {
      $userX = base64_decode($user);
      $userdata = explode(':', $userX);
      $userdata = get_userdata($userdata[1]);
      include("header.php");
   }

   // Either valid user/pass, or valid session. continue with post.
   if ($stop != 1) {
      $poster_ip =  getip();
      if ($dns_verif)
         $hostname=@gethostbyaddr($poster_ip);
      else
         $hostname=$poster_ip;

      // anti flood
      anti_flood ($Mmod, $anti_flood, $poster_ip, $userdata, $gmt);
      //anti_spambot
      if (isset($asb_question) and isset($asb_reponse)) {
         if (!R_spambot($asb_question, $asb_reponse, $message)) {
            Ecr_Log("security", "Forum Anti-Spam : forum=".$forum." / topic=".$topic, "");
            redirect_url("$url_ret");
            die();
         }
      }

      if ($formulaire!='') {
         include ("modules/comments/comments_extender.php");
      }

      if ($allow_html == 0 || isset($html)) $message = htmlspecialchars($message,ENT_COMPAT|ENT_HTML401,cur_charset);
      if (isset($sig) && $userdata['uid'] != 1) $message .= ' [addsig]';
      $message = aff_code($message);
      $message = str_replace("\n", "<br />", $message);
      if ($allow_bbcode) {
         $message = smile($message);
      }
      $message = make_clickable($message);
      $message = removeHack($message);
      $image_subject='';
      $message = addslashes($message);
      $time = date("Y-m-d H:i:s",time()+($gmt*3600));
      $sql = "INSERT INTO ".$NPDS_Prefix."posts (post_idH, topic_id, image, forum_id, poster_id, post_text, post_time, poster_ip, poster_dns) VALUES ('0', '$topic', '$image_subject', '$forum', '".$userdata['uid']."', '$message', '$time', '$poster_ip', '$hostname')";
      if (!$result = sql_query($sql)) {
         forumerror('0020');
      } else {
         $IdPost=sql_last_id();
      }

      $sql = "UPDATE ".$NPDS_Prefix."users_status SET posts=posts+1 WHERE (uid = '".$userdata['uid']."')";
      $result = sql_query($sql);
      if (!$result) {
         forumerror('0029');
      }

      // ordre de mise � jour d'un champ externe ?
      if ($comments_req_add!="")
         sql_query("UPDATE ".$NPDS_Prefix.$comments_req_add);

      redirect_url("$url_ret");
   } else {
      echo '<p class="text-xs-center">'.translate("You must type a message to post.").'<br /><br />';
      echo "[ <a href=\"javascript:history.go(-1)\" class=\"noir\">".translate("Go Back")."</a> ]</p>";
   }
} else {
   include('header.php');
   if ($allow_bbcode==1) {
      include("lib/formhelp.java.php");
   }
   if ($formulaire=='')
      echo '
			<div class="row">
			<div class="col-md-12">
			<form class="form-horizontal" role="form" action="modules.php" method="post" name="coolsus">';
   echo '<div class="form-group">';
   $allow_to_reply=false;
   if ($forum_access==0) {
      $allow_to_reply=true;
   } else {
      if (isset($user)) {
         $allow_to_reply=true;
      }
   }
   if ($allow_to_reply) {
     if (isset($submitP)) {
        $acc = 'reply';
        $message=stripslashes($message);
        include ("preview.php");
     } else {
        $message='';
     }
     if ($formulaire!='') {
        include ("modules/comments/comments_extender.php");
     } else {
        if ($allow_bbcode)
           $xJava = 'name="message" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);" onfocus="storeForm(this)"';

        echo '<div class="col-sm-2">
              <label class="control-label">';
        echo '<b>'.translate("Message: ").'</b><br />';

        echo 'HTML : ';
        if ($allow_html==1) {
           echo translate("On").'<br />';
           echo HTML_Add(false);
        } else
           echo translate("Off")."<br />";
        if (isset($citation) && !isset($submitP)) {
           $sql = "SELECT p.post_text, p.post_time, u.uname FROM ".$NPDS_Prefix."posts p, ".$NPDS_Prefix."users u WHERE post_id='$post' AND p.poster_id = u.uid";
           if ($r = sql_query($sql)) {
              $m = sql_fetch_assoc($r);
              $text = $m['post_text'];
              $text = smile($text);
              $text = str_replace("<br />", "\n", $text);
              $text = stripslashes($text);
              if ($m['post_time']!="" && $m['uname']!="") {
                 $reply = '<div class="quote">'.translate("Quote").' : <b>'.$m['uname'].'</b>&nbsp;'.$text.'&nbsp;</div>';
              } else {
                 $reply = $text."\n";
              }
           } else {
              $reply = translate("Error Connecting to DB")."\n";
           }
        }
        if (!isset($reply)) {$reply=$message;}

   echo '
      </label>
      </div>
      <div class="col-sm-10">';
     echo '<textarea class="form-control" '.$xJava.' name="message" rows="12">'.$reply.'</textarea>';
   
   echo '</div></div>';
    
     echo '
      <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10">';
        if ($allow_bbcode)
           putitems();

      echo '
      <div class="form-group row">
      <div class="col-sm-3">
         <label class="form-control-label">'.translate("Options: ").'</label>
      </div>';
        if ($allow_html==1) {
        if (isset($html)) {$sethtml = 'checked';} else {$sethtml = '';}
           echo '
      <div class="col-sm-9">
         <div class="checkbox">
            <label class="" for="html">
               <input type="checkbox" name="html" '.$sethtml.' /> '.translate("Disable HTML on this Post").'
            </label>
         </div>';
        }
        if ($user) {
           if ($allow_sig == 1||isset($sig)) {
              $asig = sql_query("SELECT attachsig FROM ".$NPDS_Prefix."users_status WHERE uid='$cookie[0]'");
              list($attachsig) = sql_fetch_row($asig);
              if ($attachsig == 1 or isset($sig)) {$s = 'checked="checked"';} else $s='';
               echo '
         <div class="checkbox">
            <label class="">
               <input type="checkbox" name="sig" '.$s.' /> '.translate("Show signature").' :<br /><small>'.translate("This can be altered or added in your profile").'</small>
            </label>
         </div>';
           }
        }
      echo '</div></div>';

        echo ''.Q_spambot().'';
        echo '
      <input type="hidden" name="ModPath" value="comments" />
      <input type="hidden" name="ModStart" value="reply" />
      <input type="hidden" name="topic" value="'.$topic.'" />
      <input type="hidden" name="file_name" value="'.$file_name.'" />
      <input type="hidden" name="archive" value="'.$archive.'" />';

      echo '
         <!--<div class="form-group text-xs-center">
         <div class="col-sm-offset-2 col-sm-10">-->
            <div class="btn-group-sm text-xs-center">
         <br />
         <input class="btn btn-primary" type="submit" name="submitS" value="'.translate("Submit").'" />
         <input class="btn btn-default" type="submit" name="submitP" value="'.translate("Preview").'" />
         <input class="btn btn-warning" type="reset" value="'.translate("Clear").'" />
         <input class="btn btn-danger" type="submit" name="cancel" value="'.translate("Cancel Post").'" />
         </div>		
         </div>
         </div>';
     }
   } else {
     echo ''.translate("You are not allowed to reply in this forum").'';
   }
   if ($formulaire=='')
      echo '
      </form>
   </div>
   </div>';
   if ($allow_to_reply) {
      if ($Mmod) {
         $post_aff='';
      } else {
         $post_aff=" AND post_aff='1' ";
      }
      $sql = "SELECT * FROM ".$NPDS_Prefix."posts WHERE topic_id='$topic'".$post_aff." AND forum='$forum' ORDER BY post_id DESC limit 0,10";
      $result = sql_query($sql);
      if (sql_num_rows($result)) {
         echo "".translate("Topic Review")."";

         while($myrow = sql_fetch_assoc($result)) {

            $posterdata = get_userdata_from_id($myrow['poster_id']);
            if ($posterdata['uname']!=$anonymous) {
               echo "<a href=\"powerpack.php?op=instant_message&amp;to_userid=".$posterdata['uname']."\" class=\"noir\">".$posterdata['uname']."</a>";
            } else {
               echo $posterdata['uname'];
            }
            echo "<br />";
            $posts = $posterdata['posts'];
            echo member_qualif($posterdata['uname'], $posts, $posterdata['rank']);
            echo "<br /><br />";
            if ($smilies) {
               if ($posterdata['user_avatar'] != '') {
                  if (stristr($posterdata['user_avatar'],"users_private")) {
                     $imgtmp=$posterdata['user_avatar'];
                  } else {
                     if ($ibid=theme_image("forum/avatar/".$posterdata['user_avatar'])) {$imgtmp=$ibid;} else {$imgtmp="images/forum/avatar/".$posterdata['user_avatar'];}
                  }
                  echo '<div class="avatar_cadre"><img src="'.$imgtmp.'" alt="'.$posterdata['uname'].'" border="0" /></div>';
               }
            }

            if ($myrow['image'] != "") {
               if ($ibid=theme_image("forum/subject/".$myrow['image'])) {$imgtmp=$ibid;} else {$imgtmp="images/forum/subject/".$myrow['image'];}
               echo '<img src="'.$imgtmp.'" alt="" />';
            } else {
               if ($ibid=theme_image("forum/subject/icons/posticon.gif")) {$imgtmp=$ibid;} else {$imgtmp="images/forum/icons/posticon.gif";}
               echo '<img src="'.$imgtmp.'" border="0" alt="" />';
            }
            echo "&nbsp;".translate("Posted: ").convertdate($myrow['post_time']);
            echo '<hr noshade="noshade" size="1" class="ongl" />';
            $message = stripslashes($myrow['post_text']);
            if ($allow_bbcode) {
               $message = smilie($message);
            }
            // <a href in the message
            if (stristr($message,"<a href")) {
               $message=preg_replace('#_blank(")#i','_blank\1 class=\1noir\1',$message);
            }
            $message=split_string_without_space($message, 80);
            $message = str_replace("[addsig]", "<br /><br />" . nl2br($posterdata['user_sig']), $message);
            echo $message."<br />";
         }
      }
   }
}
include('footer.php');
?>