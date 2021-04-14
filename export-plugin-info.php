<?php
/**
 * Plugin Name:     Export Plugins Info
 * Plugin URI:      frontkom.com
 * Description:     This plugin allows u to output info about plugin, theme, wordpress info in json format or simple list
 * Author:          Paweł Witek
 * Author URI:      frontkom.com
 * Text Domain:     export-plugin-info
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Export_Plugin_Info
 */

// Your code starts here.

// use command in ur plugin directory: composer require htmlburger/carbon-fields-plugin


use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action( 'carbon_fields_register_fields', 'frontkom_export_plugins_info' );
function frontkom_export_plugins_info() {
	Container::make( 'theme_options', 'export_plugins_info', __( 'Export Plugins Info' ) )
	->add_fields( array(
		Field::make( 'checkbox', 'fk_option_wordpress', __( 'Export wordpress version' ) )
		->set_option_value( 'yes' )
		->set_default_value( 'yes' ),
		Field::make( 'checkbox', 'fk_option_active_plugins', __( 'Export active plugins' ) )
		->set_option_value( 'yes' )
		->set_default_value( 'yes' ),
		Field::make( 'checkbox', 'fk_option_inactive_plugins', __( 'Export inactive plugins' ) )
		->set_option_value( 'yes' ),
		Field::make( 'checkbox', 'fk_option_themes', __( 'Export active theme' ) )
		->set_option_value( 'yes' )
		->set_default_value( 'yes' ),
		Field::make( 'radio', 'crb_radio', __( 'Choose Format to export' ) )
		->set_options( array(
			1 => 'export options in wp-cli-build.json format',
			2 => 'export options in list ',
		) )
	) );
}

add_action( 'after_setup_theme', 'crb_load' );
function crb_load() {
	$pathToLoader = plugin_dir_path(__FILE__) . '/vendor/autoload.php';
	require_once( $pathToLoader );
	\Carbon_Fields\Carbon_Fields::boot();
}

add_action('carbon_fields_container_export_plugins_info_after_fields', 'fk_print_generate_button');
function fk_print_generate_button(){
	echo '<button id="generate-info" class="button button-primary button-large">Generate Info</button>';
	?>
	<div id="output">
		<?php 
		global $wp_version;
		$obj = [
			"core" => [
				"download" => [
					"version" => $wp_version,
					"locale" => get_locale()
				],
			],
			"plugins" => [],
		];
		$wordpressArr = [];
		$inactivePluginsArr = [];
		$themesArr = [];
		$plugins = get_plugins();
		$activePlugins = get_option('active_plugins');



		foreach ($plugins as $pluginName => $plugin) {
			if( in_array($pluginName, $activePlugins) && $pluginName !== 'export-plugin-info/export-plugin-info.php' && ( strpos($pluginName, 'disabled') === false ) ) {				
				$pluginNameArr = explode('/', $pluginName);
				$pluginName = $pluginNameArr[0];
				$pluginVersion = $plugin['Version'];

				$pluginInfo = ['version' => $pluginVersion];
				$obj['plugins'][$pluginName] = $pluginInfo;
			};
		}
		$obj = json_encode( $obj, JSON_PRETTY_PRINT );

		echo '<pre style="background:lightgrey;color:black;padding:20px;">';
		print_r($obj);
		echo '</pre>';

		?>


	</div>
	<script>
		jQuery(function($) {
			function generateInfo(e){
				e.preventDefault();
				var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
				jQuery.post(
					ajaxurl, 
					{
						'action': 'fkepi_generate',
					}, 
					function(response) {
						console.log('The server responded: ', response);
						jQuery('#output').html(response);
					}
					);
			}
			jQuery('#generate-info').on('click', generateInfo);
		});
	</script>

	<?php 
}

add_action( 'wp_ajax_fkepi_generate', 'fkepi_generate_plugins_info' );
function fkepi_generate_plugins_info() {
    // Make your response and echo it.
	$plugins = get_plugins();
	$activePlugins = get_option('active_plugins');

	foreach ($plugins as $index => $plugin) {
		if( in_array($plugin, $activePlugins) ){
			echo '<pre style="background:blue;color:white;">';
			print_r($plugin);
			echo '</pre>';
		};
	}
	
	// echo '<pre style="background:blue;color:white;">';
	// print_r($plugins);
	// echo '</pre>';

	// echo '<pre style="background:blue;color:white;">';
	// print_r($activePlugins);
	// echo '</pre>';

    // Don't forget to stop execution afterward.
	wp_die();
}