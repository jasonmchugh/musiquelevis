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
    * Stop script when the file is called directly.
    */
   if(!function_exists("add_action")) {
      return;
   }

   /** Load translation files */
   add_action("plugins_loaded", "thumbnail_cleaner_translations");

   /**
    * Loads .css and .js files.
    *
    * @since 1.0.0
    */
   function thumbnail_cleaner_scripts_and_styles() {
      $root = plugin_dir_url(dirname(__FILE__));

      /** Styles */

      wp_enqueue_style(
         "thumbnail-cleaner-styles-admin",
         "$root/styles/admin.css",
         array(),
         THUMBNAIL_CLEANER_VERSION
      );

      /** Fonts */

      wp_enqueue_style(
         "thumbnail-cleaner-fonts",
         $root . "styles/fonts.min.css",
         array(),
         THUMBNAIL_CLEANER_VERSION
      );

      /** Scripts */

      wp_enqueue_script(
         "thumbnail-cleaner-scripts-admin",
         "$root/scripts/admin.js",
         array("jquery"),
         THUMBNAIL_CLEANER_VERSION,
         false
      );
   }

   add_action("admin_enqueue_scripts", "thumbnail_cleaner_scripts_and_styles");

   /**
    * Registers Thumbnail Cleaner's menu page.
    *
    * @since 1.0.0
    */
   function thumbnail_cleaner_register_menu_page() {
      add_management_page(
         "Thumbnail Cleaner",
         "Thumbnail Cleaner",
         "manage_options",
         "thumbnail-cleaner",
         "thumbnail_cleaner_menu_page"
      );
   }

   add_action("admin_menu", "thumbnail_cleaner_register_menu_page");