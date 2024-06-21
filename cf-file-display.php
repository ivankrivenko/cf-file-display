<?php
/*
Plugin Name: CF File Display
Plugin URI:  https://github.com/ivankrivenko/cf-file-display
Description: Uploading and displaying a list of files in posts and static pages based on Carbon Fields
Version:     1.0
Author:      Ivan Krivenko
Text Domain: cf-file-display
Domain Path: /languages
*/

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

function cffd__load_textdomain() {
    load_plugin_textdomain('cf-file-display', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'cffd__load_textdomain');

function cffd_check_carbon_fields() {
    if ( ! is_plugin_active( 'carbon-fields/carbon-fields-plugin.php' ) ) {
        add_action('admin_notices', 'cffd_carbon_fields_admin_notice');
        add_action('admin_init', 'cffd_deactivate');
        return; // Prevent further execution if Carbon Fields is not active
    }
}
add_action('admin_init', 'cffd_check_carbon_fields');

function cffd_carbon_fields_admin_notice() {
    ?>
<div class="notice notice-error">
    <p><?php _e('CF File Display Plugin requires the <a href="https://carbonfields.net/release-archive/" target="_blank">Carbon Fields plugin</a> to be active. Please activate Carbon Fields to use this plugin.', 'cf-file-display'); ?>
    </p>
</div>
<?php
}

function cffd_deactivate() {
    // Deactivate main plugin functions if Carbon Fields is not active
}

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('carbon_fields_register_fields', 'crb_attach_file');
function crb_attach_file() {
    Container::make('post_meta', __('Attached files', 'cf-file-display'))
        ->where('post_type', 'IN', ['post', 'page']) // Restrict fields to posts and pages
        ->add_fields(array(
            Field::make('complex', 'crb_files', __('Files', 'cf-file-display'))
                ->add_fields('folder', __('Folder', 'cf-file-display'), array(
                    Field::make('text', 'folder', __('Folder name', 'cf-file-display'))
                        ->set_attribute('type', 'text')
                        ->set_required(true),
                    Field::make('rich_text', 'folder_description', __('Folder description', 'cf-file-display')),
                    Field::make('complex', 'files', __('Attached files', 'cf-file-display'))
                        ->add_fields('file', __('File', 'cf-file-display'), array(
                            Field::make('text', 'file_description', __('File description', 'cf-file-display')),
                            Field::make('file', 'file', __('File', 'cf-file-display'))
                        ))
                ))
                ->add_fields('file', __('File', 'cf-file-display'), array(
                    Field::make('text', 'file_description', __('File description', 'cf-file-display')),
                    Field::make('file', 'file', __('File', 'cf-file-display'))
                ))
        ));
}

function formatBytes($bytes, $precision = 2) {
    $units = array(
        __('B', 'cf-file-display'),
        __('KB', 'cf-file-display'),
        __('MB', 'cf-file-display'),
        __('GB', 'cf-file-display'),
        __('TB', 'cf-file-display')
    );

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

function display_file_item($file) {
    $file_id = $file['file'];
    $file_url = wp_get_attachment_url($file_id);

    if ($file_url) {
        $file_metadata = wp_get_attachment_metadata($file_id);
        $filesize = isset($file_metadata['filesize']) ? $file_metadata['filesize'] : 0;

        $file_name = !empty($file['file_description']) ? $file['file_description'] : get_the_title($file_id); // Get file name

        ?>
<a href="<?php echo esc_url($file_url); ?>" target="_blank" class="attached-files-list-item">
    <?php echo esc_html($file_name); ?>
    <?php if ($filesize != 0) { ?>
    <span class="attached-file-extension"><?php echo formatBytes($filesize); ?></span>
    <?php } ?>
</a>
<?php
    }
}

add_filter('the_content', 'cffd_display_files');
function cffd_display_files($content) {
    if (is_page() || is_single()) { // Check if it's a page or post
        $files = carbon_get_the_post_meta('crb_files');

        if ($files) {
            ob_start();
            ?>
<h4><?php echo __('Attached files', 'cf-file-display'); ?></h4>
<div class="attached-files-list">
    <?php
            $folder = false;
            foreach ($files as $file) {
                if ($file['_type'] == 'file') {
                    if ($folder) {
                        $folder = false;
                        ?>
</div>
<div class="attached-files-list">
    <?php
                    }
                    display_file_item($file);
                }

                if ($file['_type'] == 'folder') {
                    $folder = true;
                    ?>
</div>
<div class="attached-files-list">
    <div class="attached-files-list-header"><?php echo esc_html($file['folder']); ?></div>
    <div class="attached-files-list-content">
        <?php if (!empty($file['folder_description'])) { ?>
        <p><?php echo esc_html($file['folder_description']); ?></p>
        <?php } ?>
        <div class="attached-files-list">
            <?php 
                            if (isset($file['files'])) {
                                foreach ($file['files'] as $folders_file) {
                                    display_file_item($folders_file);
                                }
                            }
                            ?>
        </div>
    </div>
    <?php
                }
            }
            ?>
</div>
<?php
            $content .= ob_get_clean();
        }
    }

    return $content;
}

add_action('wp_enqueue_scripts', 'check_bootstrap_styles');
function check_bootstrap_styles() {
    wp_enqueue_style('attached-files-list', plugin_dir_url(__FILE__) . 'styles/style.css');
}

function enqueue_attached_files_script() {
    wp_enqueue_script(
        'attached-files-script',
        plugin_dir_url(__FILE__) . 'js/attached-files.js',
        array(),
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_attached_files_script');