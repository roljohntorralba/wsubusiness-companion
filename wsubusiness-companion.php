<?php
/**
 * Plugin Name: WebsiteSetup Business Companion
 * Plugin URI: https://wordpress.org/plugins/wsubusiness-companion/
 * Description: Extends the WebsiteSetup Business theme\&#39;s functionality. Adding the ability to create lead texts and sidebar content per page.
 * Version: 1.3.0
 * Requires at least: 5.2
 * Requires PHP: 5.6
 * Author: WebsiteSetup
 * Author URI: https://websitesetup.org/about-and-contact/
 * Text Domain: wsubusiness-companion
 * Domain Path: /languages
 * License: GPL2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 
 WebsiteSetup Business Companion is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version.
 
 WebsiteSetup Business Companion is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License along with WebsiteSetup Business Companion. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.

 */

//* Block direct access to the file
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function wsubc_blocks() {
    $asset_file = include( plugin_dir_path( __FILE__ ) . 'build/index.asset.php');

    wp_register_script(
        'wsubc-gutenberg-blocks',
        plugins_url( 'build/index.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-editor' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'build/index.js' ),
        $asset_file['dependencies'],
        $asset_file['version']
    );

    wp_register_style(
        'wsubc-gutenberg-blocks-editor',
        plugins_url( 'css/editor.css', __FILE__ ),
        array( 'wp-edit-blocks' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'css/editor.css' )
    );
 
    wp_register_style(
        'wsubc-gutenberg-blocks-style',
        plugins_url( 'css/style.css', __FILE__ ),
        array( ),
        filemtime( plugin_dir_path( __FILE__ ) . 'css/style.css' )
    );
 
    register_block_type( 'wsubc/panel', array(
        'style' => 'wsubc-gutenberg-blocks-style',
        'editor_style' => 'wsubc-gutenberg-blocks-editor',
        'editor_script' => 'wsubc-gutenberg-blocks',
    ) );
 
}
add_action( 'init', 'wsubc_blocks' );

//* Prints the Leading Text custom field below the title
function wsubc_add_hero_text() {
	$lead_text = get_post_meta( get_the_id(), '_wsubc_lead_text_value', true);
    if( $lead_text ) {
        echo '<p class="lead">' . $lead_text . '</p>';
    }
}
add_action( 'in_entry_header_bottom', 'wsubc_add_hero_text');

//* Prints the Sidebar Text custom field on top of the sidebar widgets
function wsubc_render_sidebar_text() {
	$sidebar_text = get_post_meta( get_the_id(), '_wsubc_sidebar_text_value', true);
    if( $sidebar_text ) {
    	$output = '<section id="custom-sidebar-text" class="widget widget-custom-sidebar-text">';
    	$output .= $sidebar_text;
    	$output .= '</section>';
    	echo $output;
    }
}
add_action( 'in_sidebar_top', 'wsubc_render_sidebar_text');

//* Leading text 
abstract class WSUBC_Lead_Text {
    public static function add() {
        $screens = ['post', 'page'];
        foreach ($screens as $screen) {
            add_meta_box(
                'wsubc_lead_text_box',          // Unique ID
                __('Custom fields', 'wsubusiness-companion'), // Box title
                [self::class, 'lead_text_html'],   // Content callback, must be of type callable
                $screen,                  // Post type
                'side'
            );
            add_meta_box(
                'wsubc_sidebar_text_box',          // Unique ID
                __('Sidebar text', 'wsubusiness-companion'), // Box title
                [self::class, 'sidebar_text_html'],   // Content callback, must be of type callable
                $screen,                  // Post type
                'side'
            );
        }
    }
 
    public static function lead_text_save($post_id) {
        if (array_key_exists('wsubc_lead_text_field', $_POST)) {
            update_post_meta(
                $post_id,
                '_wsubc_lead_text_value',
                sanitize_text_field($_POST['wsubc_lead_text_field'])
            );
        }
    }
 
    public static function lead_text_html($post) {
    	wp_nonce_field( basename( __FILE__ ), 'wsubc_nonce' );
	    $wsubc_stored_meta = get_post_meta( $post->ID );
	    ?>
	    <p>
        <label for="wsubc_lead_text_field" class="components-text-control__label"><?php _e( 'Leading text', 'wsubusiness-companion' )?></label>
        <input type="text" class="components-text-control__input" name="wsubc_lead_text_field" id="wsubc_lead_text_field" value="<?php if ( isset ( $wsubc_stored_meta['_wsubc_lead_text_value'] ) ) echo $wsubc_stored_meta['_wsubc_lead_text_value'][0]; ?>" />
	    </p>
	    <?php
    }

    public static function sidebar_text_save($post_id) {
        if (array_key_exists('wsubc_sidebar_editor', $_POST)) {
    		$data = $_POST['wsubc_sidebar_editor'];
    		update_post_meta(
                $post_id,
                '_wsubc_sidebar_text_value',
                $data
            );
        }
    }

    public static function sidebar_text_html($post) {
    	wp_nonce_field( basename( __FILE__ ), 'wsubc_nonce' );
	    $wsubc_stored_meta = get_post_meta( $post->ID );
	    wp_editor($wsubc_stored_meta['_wsubc_sidebar_text_value'][0], 'wsubc_sidebar_editor', array( 'media_buttons' => true, 'tinymce' => false, 'quicktags' => true, ));
    }
}
add_action('add_meta_boxes', ['WSUBC_Lead_Text', 'add']);
add_action('save_post', ['WSUBC_Lead_Text', 'lead_text_save']);
add_action('save_post', ['WSUBC_Lead_Text', 'sidebar_text_save']);

/**
 * Plugin Update Checker
 */
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/rolzan/wsubusiness_companion',
    __FILE__,
    'wsubusiness_companion'
);
//Optional: Set the branch that contains the stable release.
$myUpdateChecker->setBranch('stable');

?>