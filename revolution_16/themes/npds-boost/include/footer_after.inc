<?php
/************************************************************************/
/* NPDS DUNE : Net Portal Dynamic System .                              */
/* ===========================                                          */
/*                                                                      */
/* This version name NPDS Copyright (c) 2001-2015 by Philippe Brunier   */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/
   $time_end = getmicrotime();
   $time = round($time_end - $time_start, 3);
   echo '
      <span class="small" id="build_time">'.aff_langue(" [french]Temps :[/french][english]Time:[/english] $time [french]seconde(s)[/french][english]second(s)[/english] ").'</span>
   </div>
</footer>';
?>