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
include_once('functions.php');
include_once('auth.php');

settype($forum,"integer");
if ($forum>=0) die();

// gestion des params du 'forum' : type, acc�s, mod�rateur ...
$forum_name = 'comments';
$forum_type=0;
$allow_to_post=false;
if ($anonpost)
   $forum_access=0;
else
   $forum_access=1;

if (($moderate==1) and $admin)
   $Mmod=true;
elseif ($moderate==2) {
   $userX=base64_decode($user);
   $userdata=explode(':', $userX);
   $result=sql_query("SELECT level FROM ".$NPDS_Prefix."users_status WHERE uid='".$userdata[0]."'");
   list($level)=sql_fetch_row($result);
   if ($level>=2)
      $Mmod=true;
} else
   $Mmod=false;

function Caff_pub($topic, $file_name, $archive) {
   global $language;
//   if ($ibid=theme_image("menu/$language/comment.gif")) {$imgtmpR=$ibid;} else {$imgtmpR="images/menu/$language/comment.gif";}
   $tmp='<a href="modules.php?ModPath=comments&amp;ModStart=reply&amp;topic='.$topic.'&amp;file_name='.$file_name.'&amp;archive='.$archive.'" class="btn btn-primary btn-sm" role="button">Commenter</a>';
   return ($tmp);
}
   if ($forum_access==0) {
      $allow_to_post=true;
   } else {
      if ($user) {
         $allow_to_post=true;
      }
   }
   global $anonymous;
   settype($archive,"integer");
   if ($allow_to_post) {
      echo '<nav class="pull-right">'.Caff_pub($topic,$file_name, $archive).'</nav>';
   }

   // Pagination
   settype($C_start,"integer");
   settype($comments_per_page,"integer");
   $result=sql_query ("SELECT COUNT(*) AS total FROM ".$NPDS_Prefix."posts WHERE forum_id='$forum' AND topic_id='$topic' AND post_aff='1'");
   list($total)=sql_fetch_row($result);
   if ($total > $comments_per_page) {
      $times = 1; $current_page=0;
      echo '
      <div id="fo-postpagi">
         <ul class="pagination pagination-sm">
            <li class="page-item">
               <a class="page-link" href="#botofpage"><i class="fa fa-angle-double-down" title="'.translate("Bottom page").'" data-toggle="tooltip"></i></a>
            </li>
            <li class="page-item disabled">
               <a class="page-link" href="#" aria-label="'.translate("Comments").'">'.$total.' '.translate("Comments").'</a>
            </li>
            <li class="page-item disabled">
               <a class="page-link"href="#" aria-label="'.translate("pages").'">'.$pages.' '.translate("pages").'</a>
            </li>';
       $pages_rapide='';
      for ($x = 0; $x < $total; $x += $comments_per_page) {
         if (($x>=$C_start) and ($current_page==0)) {
            $current_page=$times;
         }
         if ($current_page!=$times)
            $pages_rapide.='
             <li class="page-item"><a class="page-link" href="'.rawurldecode($url_ret).'&amp;C_start='.$x.'">'.$times.'</a></li>';
         else
            $pages_rapide.='
             <li class="page-item active"><a class="page-link" href="#">'.$times.'</a></li>';
         $times++;
      }
      echo $pages_rapide.'
         </ul>
      </div>';
   }

   if ($Mmod) {
      $post_aff=' ';
   } else {
      $post_aff=" AND post_aff='1' ";
   }
   $sql = "SELECT * FROM ".$NPDS_Prefix."posts WHERE forum_id='$forum' AND topic_id = '$topic'".$post_aff."ORDER BY post_id LIMIT $C_start, $comments_per_page";
   if (!$result = sql_query($sql)) forumerror(0001);
   $mycount = sql_num_rows($result);
   $myrow = sql_fetch_assoc($result);
   $count = 0;

if ($mycount) {
   if ($ibid=theme_image("forum/icons/posticon.gif")) {$imgtmpPI=$ibid;} else {$imgtmpPI="images/forum/icons/posticon.gif";}
   if ($ibid=theme_image("forum/icons/profile.gif")) {$imgtmpPR=$ibid;} else {$imgtmpPR="images/forum/icons/profile.gif";}
   if ($ibid=theme_image("forum/icons/www_icon.gif")) {$imgtmpWW=$ibid;} else {$imgtmpWW="images/forum/icons/www_icon.gif";}
   if ($ibid=theme_image("forum/icons/quote.gif")) {$imgtmpQU=$ibid;} else {$imgtmpQU="images/forum/icons/quote.gif";}
   if ($ibid=theme_image("forum/icons/ip_logged.gif")) {$imgtmpIP=$ibid;} else {$imgtmpIP="images/forum/icons/ip_logged.gif";}
   if ($ibid=theme_image("forum/icons/unlock_post.gif")) {$imgtmpUP=$ibid;} else {$imgtmpUP="images/forum/icons/unlock_post.gif";}
   if ($ibid=theme_image("forum/icons/lock_post.gif")) {$imgtmpLP=$ibid;} else {$imgtmpLP="images/forum/icons/lock_post.gif";}

   do {
      $posterdata = get_userdata_from_id($myrow['poster_id']);
      $posts = $posterdata['posts'];
      
   $useroutils = '';
   $useroutils .= '<hr />';
      if ($posterdata['uid']!= 1 and $posterdata['uid']!="") {
         $useroutils .= '<a href="user.php?op=userinfo&amp;uname='.$posterdata['uname'].'" target="_blank" title="'.translate("Profile").'" data-toggle="tooltip"><i class="fa fa-lg fa-user"></i>&nbsp;'.translate("Profile").'</a>';
      }
      if ($posterdata['uname']!=$anonymous) {
         $useroutils .= '<br /><a href="powerpack.php?op=instant_message&amp;to_userid='.$posterdata["uname"].'" title="'.translate("Send internal Message").'" data-toggle="tooltip"><i class="fa fa-lg fa-envelope-o"></i>&nbsp;'.translate("Send internal Message").'</a>';
      }
      if ($posterdata['femail']!="") {
         $useroutils .= '<br /><a href="mailto:'.anti_spam($posterdata['femail'],1).'" target="_blank" title="'.translate("Email").'" data-toggle="tooltip"><i class="fa fa-at fa-lg"></i>&nbsp;'.translate("Email").'</a>';
      }
      if ($posterdata['url']!="") {
         if (strstr("http://", $posterdata['url']))
            $posterdata['url'] = "http://" . $posterdata['url'];
         $useroutils .= '<br /><a href="'.$posterdata['url'].'" target="_blank" title="'.translate("Visit this Website").'" data-toggle="tooltip"><i class="fa fa-lg fa-external-link"></i>&nbsp;'.translate("Visit this Website").'</a>';
      }
      if ($posterdata['mns']) {
          $useroutils .= '<br /><a href="minisite.php?op='.$posterdata['uname'].'" target="_blank" target="_blank" title="'.translate("Visit the Mini Web Site !").'" data-toggle="tooltip"><i class="fa fa-lg fa-desktop"></i>&nbsp;'.translate("Visit the Mini Web Site !").'</a>';
      }
      echo '
      <div class="row">
         <a name="'.$forum.$topic.$myrow['post_id'].'"></a>';
      if (($count+2)==$mycount) echo '<a name="last-post"></a>';
      echo '
         <div class="col-xs-12">
            <div class="card">
               <div class="card-header">';
      global $smilies;
      if ($smilies) {
          if ($posterdata['user_avatar'] != '') {
             if (stristr($posterdata['user_avatar'],"users_private")) {
                $imgtmp=$posterdata['user_avatar'];
             } else {
                if ($ibid=theme_image("forum/avatar/".$posterdata['user_avatar'])) {$imgtmp=$ibid;} else {$imgtmp="images/forum/avatar/".$posterdata['user_avatar'];}
             }
              echo '<img width="48" height="48" class=" img-thumbnail img-fluid n-ava" src="'.$imgtmp.'" alt="'.$posterdata['uname'].'"  data-toggle="popover" data-html="true" data-title="'.$posterdata['uname'].'" data-content=\''.member_qualif($posterdata['uname'], $posts, $posterdata['rank']).'<br />'.$useroutils.'\' />';
          }
      }
             echo '&nbsp;<span class="text-muted"><strong>'.$posterdata['uname'].'</strong></span>';
      
      echo '
               </div>';
      $message=stripslashes($myrow['post_text']);
      $date_post=convertdateTOtimestamp($myrow['post_time']);
      echo '
               <div class="card-block">
                  <div class="card-text">
                     <img class="smil" src="'.$imgtmpPI.'" alt="" />
                     <span class="text-muted pull-right small">'.translate("Posted: ").post_convertdate($date_post).'</span>
                  </div>
                  <hr />
                  <div class="card-text">';

      if ($allow_bbcode) {
         $message = smilie($message);
         $message = aff_video_yt($message);
      }
      // <a href in the message
      if (stristr($message,"<a href")) {
         $message=preg_replace('#_blank(")#i','_blank\1 class=\1\1',$message);
      }
      $message=split_string_without_space($message, 80);
      $message=str_replace("[addsig]", "<br /><br />" . nl2br($posterdata['user_sig']), $message);
         echo '<div class="card-text fo-post-mes">';
         echo $message;
         echo '
               </div>
               </div>
            </div>
         <div class="card-footer text-xs-right">';

      if ($allow_to_post and $posterdata['uid']!='') {
         if ($formulaire=='') {
            echo '&nbsp;<a href="modules.php?ModPath=comments&amp;ModStart=reply&amp;topic='.$topic.'&amp;file_name='.$file_name.'&amp;post='.$myrow['post_id'].'&amp;citation=1&amp;archive='.$archive.'" title="'.translate("Quote").'" data-toggle="tooltip" ><i class="fa fa-lg fa-quote-left"></i></a>&nbsp;';
         } else
            echo '&nbsp;&nbsp;';
      }

      if ($Mmod) {
         if ($formulaire=='')
            echo '&nbsp;|&nbsp;';
            
         echo '<a href="modules.php?ModPath=comments&amp;ModStart=admin&amp;mode=viewip&amp;topic='.$topic.'&amp;post='.$myrow['post_id'].'&amp;file_name='.$file_name.'&amp;archive='.$archive.'" title="IP" data-toggle="tooltip"><i class="fa fa-lg fa-laptop"></i></a>&nbsp;';
         if (!$myrow['post_aff']) {
            echo '&nbsp;<a href="modules.php?ModPath=comments&amp;ModStart=admin&amp;mode=aff&amp;topic='.$topic.'&amp;post='.$myrow['post_id'].'&amp;ordre=1&amp;file_name='.$file_name.'&amp;archive='.$archive.'" title="'.translate("Show this post").'" data-toggle="tooltip"><i class="fa fa-lg fa-eye text-danger"></i></a>&nbsp;';
         } else {
            echo '&nbsp;<a href="modules.php?ModPath=comments&amp;ModStart=admin&amp;mode=aff&amp;topic='.$topic.'&amp;post='.$myrow['post_id'].'&amp;ordre=0&amp;file_name='.$file_name.'&amp;archive='.$archive.'" title="'.translate("Hide this post").'" data-toggle="tooltip"><i class="fa fa-lg fa-eye-slash"></i></a>&nbsp;';
         }
      }
      echo '
            </div>
         </div>
      </div>
   </div>';
      $count++;
    } while($myrow = sql_fetch_assoc($result));
    unset ($tmp_imp);

   if ($total > $comments_per_page) {
      echo '
   <nav>
      <ul class="pagination pagination-sm">
         <li class="page-item">
            <a class="page-link" href="#topofpage"><i class="fa fa-angle-double-up" title="'.translate("Back to Top").'" data-toggle="tooltip"></i></a>
         </li>
         <li class="page-item disabled">
            <a class="page-link" href="#">'.translate("Goto Page").'</a>
         </li>';
      echo $pages_rapide.'
      </ul>
   </nav>';
   }

   if ($ibid=theme_image("forum/icons/lock_topic.gif")) {$imgtmpLT=$ibid;} else {$imgtmpLT="images/forum/icons/lock_topic.gif";}
   if ($allow_to_post) {
      echo Caff_pub($topic,$file_name, $archive)."\n";
   }
   echo '<blockquote class="blockquote">'.translate("The comments are owned by the poster. We aren't responsible for their content.").'</blockquote>';

   if ($Mmod) {
       echo '
   <nav class="text-xs-center">
      <ul class="pagination pagination-sm">
         <li class="page-item disabled">
            <a class="page-link" href="#"><i class="fa fa-cogs fa-lg"></i>&nbsp;'.translate("Administration Tools").'</a>
         </li>
         <li class="page-item">
            <a class="page-link text-danger" href="modules.php?ModPath=comments&amp;ModStart=admin&amp;mode=del&amp;topic='.$topic.'&amp;file_name='.$file_name.'&amp;archive='.$archive.' " title="'.translate("Delete this Topic").'" data-toggle="tooltip"><i class="fa fa-remove fa-lg" aria-hidden="true"></i></a>
         </li>
      </ul>
   </nav>';
   }
}
?>