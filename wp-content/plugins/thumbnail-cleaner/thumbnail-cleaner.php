<?php
   /**
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

   /**
    * Plugin Name:   Thumbnail Cleaner
    * Plugin URI:    https://www.koljanolte.com/wordpress/plugins/thumbnail-cleaner/
    * Description:   Cleans up your WordPress installation by deleting and regenerating all thumbnails to remove unused ones.
    * Version:       1.4.2
    * Author:        Kolja Nolte
    * Author URI:    https://www.koljanolte.com
    * License:       GPLv2 or later
    * License URI:   http://www.gnu.org/licenses/gpl-2.0.html
    * Text Domain:   thumbnail-cleaner
    * Domain Path:   /languages
    */

   /**
    * Stop script when the file is called directly.
    */
   if(!function_exists("add_action")) {
      return false;
   }

   define("THUMBNAIL_CLEANER_VERSION", "1.4.2");

   /**
    * Includes all files from the "includes" directory.
    */
   $include_directories = array(
      "admin",
      "classes",
      "includes"
   );

   foreach((array)$include_directories as $include_directory) {
      $directory_path = plugin_dir_path(__FILE__) . "/$include_directory";

      if(!is_dir($directory_path)) {
         continue;
      }

      $include_files = glob("$directory_path/*.php");
      foreach((array)$include_files as $include_file) {
         if(!is_file($include_file)) {
            continue;
         }

         include_once($include_file);
      }
   }

   /**
    * Loads translation file.
    */
   function thumbnail_cleaner_translations() {
      load_plugin_textdomain(
         "thumbnail-cleaner",
         false,
         basename(dirname(__FILE__)) . "/languages/"
      );
   }

