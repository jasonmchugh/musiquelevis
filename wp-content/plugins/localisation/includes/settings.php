<?php
/**
 * Localisation Settings
 *
 * @package localisation
 * @version 0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


// create custom plugin settings menu
add_action('admin_menu', 'localisation_create_menu');

function localisation_create_menu() {

	//create new top-level menu
	add_menu_page('localisation', 'Localisation', 'administrator', __FILE__, 'localisation_settings_page' , plugins_url('/images/icon.png', __FILE__) );

	//call register settings function
	add_action( 'admin_init', 'register_localisation_settings' );
}


function register_localisation_settings() {
	//register settings for Reorder Function
	register_setting( 'localisation-settings-group', 'reorder_active' );
	register_setting( 'localisation-settings-group', 'reorder_option_1' );
	register_setting( 'localisation-settings-group', 'reorder_option_2' );
	register_setting( 'localisation-settings-group', 'reorder_option_3' );

	//register settings for Duplicate Function
	register_setting( 'localisation-settings-group2', 'duplicate_active' );
	register_setting( 'localisation-settings-group2', 'duplicate_option_1' );
}

function localisation_settings_page() {
?>
<div class="wrap">
	<h1>Localisation Settings</h1>

	<div class="localisation-tabs-nav">

	</div>
	<div class="localisation-tabs">
		<div class="tabcontent1">
			<form method="post" action="options.php">
			    <?php settings_fields( 'localisation-settings-group' ); ?>
			    <?php do_settings_sections( 'localisation-settings-group' ); ?>
			    <table class="form-table">
				    <tr valign="top">
			        <th scope="row">Active?</th>
			        <td><input type="checkbox" name="reorder_active" value="1" <?php echo checked( 1, get_option('reorder_active'), false ); ?> /></td>
			        </tr>

			        <tr valign="top">
			        <th scope="row">Reorder Option 1</th>
			        <td><input type="text" name="reorder_option_1" value="<?php echo esc_attr( get_option('reorder_option_1') ); ?>" /></td>
			        </tr>

			        <tr valign="top">
			        <th scope="row">Reorder Option 2</th>
			        <td><input type="text" name="reorder_option_2" value="<?php echo esc_attr( get_option('reorder_option_2') ); ?>" /></td>
			        </tr>

			        <tr valign="top">
			        <th scope="row">Reorder Option 3</th>
			        <td><input type="text" name="reorder_option_3" value="<?php echo esc_attr( get_option('reorder_option_3') ); ?>" /></td>
			        </tr>
			    </table>
			    <?php submit_button(); ?>
			</form>
		</div>
		<div class="tabcontent2" style="display: none;">
			<form method="post" action="options.php">
				<?php settings_fields( 'localisation-settings-group2' ); ?>
			    <?php do_settings_sections( 'localisation-settings-group2' ); ?>
			    <table class="form-table">
				    <tr valign="top">
			        <th scope="row">Active?</th>
			        <td><input type="checkbox" name="duplicate_active" value="1" <?php echo checked( 1, get_option('duplicate_active'), false ); ?> /></td>
			        </tr>
			        <tr valign="top">
			        <th scope="row">Duplicate Option 1</th>
			        <td><input type="text" name="duplicate_option_1" value="<?php echo esc_attr( get_option('duplicate_option_1') ); ?>" /></td>
			        </tr>
			    </table>
			    <?php submit_button(); ?>
			</form>
		</div>
	</div>
</div>
<?php } ?>