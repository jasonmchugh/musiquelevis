<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once( plugin_dir_path( __FILE__ ) . 'header/plugin-header.php' ); ?>
<div class="wcblu-main-table res-cl">
    <div class="heading_div">
        <h2><?php echo esc_html__( 'Import &amp; Export Settings', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></h2>
    </div>
    <table class="form-table table-outer import-export-settings res-cl">
        <tbody>
        <tr>
            <th scope="row" class="titledesc"><label
                        for="blogname"><?php echo esc_html__( 'Export Settings Data', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
            </th>
            <td>

                <form method="post">
                    <div class="wcblu_main_container">
                        <p class="wcblu_button_container">
                            <button class="wcblu_export_settings button button-primary"><?php esc_html_e( 'Export', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></button>
                        </p>
                        <p class="wcblu_content_container">
                            <input type="hidden" name="wcblu_export_action" value="export_settings"/>
                            <strong><?php esc_html_e( 'Export the plugin settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></strong>
                        </p>
                    </div>
                </form>

            </td>
        </tr>
        <tr>
            <th scope="row" class="titledesc"><label
                        for="blogname"><?php echo esc_html__( 'Import Settings Data', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></label>
            </th>
            <td>
                <form method="post" enctype="multipart/form-data">
                    <div class="wcblu_main_container">
                        <p class="wcblu_button_container">
                            <input type="file" name="import_file"/>
                        </p>
                        <p class="wcblu_content_container">
                            <button class="wcblu_import_setting button button-primary"><?php esc_html_e( 'Import', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></button>
                        </p>
                        <p class="wcblu_content_container">
                            <input type="hidden" name="wcblu_import_action" value="import_settings"/>
                            <strong><?php esc_html_e( 'Import the plugin settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'woo-blocker-lite-prevent-fake-orders-and-blacklist-fraud-customers' ); ?></strong>
                        </p>
                    </div>
                </form>
            </td>
        </tr>
        </tbody>
    </table>
</div>

</div>
</div>
</div>
</div>