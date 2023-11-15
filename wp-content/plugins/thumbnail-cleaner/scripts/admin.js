/*
 * (C) 2018 by Kolja Nolte
 * kolja.nolte@gmail.com
 * https://www.koljanolte.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * @project Thumbnail Cleaner
 */

jQuery(document).ready(function () {
   "use strict";

   if(jQuery(".thumbnail-cleaner").length > 0) {
      jQuery("a").click(function () {
         if(jQuery(this).attr("href") === "#") {
            return false;
         }
      });

      jQuery("#clean-thumbnails").click(function () {
         if(jQuery("#has-backups").attr("value") === "true") {
            return true;
         }
         var text = jQuery("#has-backups-text").attr("value");
         if(confirm(text) === false) {
            return false;
         }
      });
   }
});