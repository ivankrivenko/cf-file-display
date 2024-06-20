<?php
/*
Plugin Name: CF File Display
Plugin URI:  https://ivankrivenko.ru
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
        // Carbon Fields не активен, добавляем уведомление в админку
        add_action('admin_notices', 'cffd_carbon_fields_admin_notice');

        // Прекращаем выполнение основных функций плагина
        add_action('admin_init', 'cffd_deactivate');
    } else {

    }
}
add_action('admin_init', 'cffd_check_carbon_fields');

function cffd_carbon_fields_admin_notice() {
    ?>
<div class="notice notice-error">
    <p><?php _e('CF File Display Plugin requires the Carbon Fields plugin to be active. Please activate Carbon Fields to use this plugin.', 'cf-file-display'); ?>
    </p>
</div>
<?php
}

function cffd_deactivate() {
    // Здесь можно деактивировать основные функции вашего плагина
    // Например, отменить регистрацию кастомных полей, настроек и т.д.
}



use Carbon_Fields\Container;
use Carbon_Fields\Field;


add_action('carbon_fields_register_fields', 'crb_attach_file');
function crb_attach_file() {
    Container::make('post_meta', __('Attached files', 'cf-file-display'))
        ->where('post_type', '=', 'page') // Ограничить поля только для страниц
        
        ->add_fields(array(
            Field::make('complex', 'crb_files', __('Files'))

                // Множественное поле
                ->add_fields( 'folder', __('Folder', 'cf-file-display'), array(
                    Field::make( 'text', 'folder', __('Folder name', 'cf-file-display')  ),
                    Field::make( 'rich_text', 'folder_description', __('Folder description', 'cf-file-display') ),
                    Field::make( 'complex', 'files', __('Attached files', 'cf-file-display') )
                        ->add_fields( 'file', __('File', 'cf-file-display'), array(
                            Field::make( 'text', 'file_description', __('File description', 'cf-file-display') ),
                            Field::make( 'file', 'file', __('File', 'cf-file-display') )
                        ))
                ))
                

                ->add_fields( 'file', __('File', 'cf-file-display'), array(
                    Field::make( 'text', 'file_description', __('File description', 'cf-file-display')  ),
                    Field::make( 'file', 'file', __('File', 'cf-file-display') )
                ))
        ));
}
/*
add_action('after_setup_theme', 'crb_load');
function crb_load() {
    \Carbon_Fields\Carbon_Fields::boot();
}
*/
function formatBytes($bytes, $precision = 2) {

    $byte = __('B', 'cf-file-display');
    $kb = __('KB', 'cf-file-display');
    $mb = __('MB', 'cf-file-display');
    $gb = __('GB', 'cf-file-display');
    $tb = __('TB', 'cf-file-display');

    $units = array($byte, $kb, $mb, $gb, $tb);

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

        if (!empty( $file['file_description'])) {
            $file_name = $file['file_description'];
        } else {
            $file_name = get_the_title($file_id); // Получение имени файла
        }
       
        $file_extension = pathinfo($file_url, PATHINFO_EXTENSION); // Получение расширения файла
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



// Hook to display the files
add_filter('the_content', 'cffd_display_files');
function cffd_display_files($content) {
    if (is_page()) { // Проверка, что это страница


        $files = carbon_get_the_post_meta('crb_files');

        /*
        echo '<pre>';
        print_r($files);
        echo '</pre>';
        */
        if ($files) {
            ?>

<h4><?php echo __('Attached files', 'cf-file-display') ?></h4>
<div class="attached-files-list">
    <?php
            $folder = false;
            foreach ($files as $file) {
                if ($file['_type'] == 'file') {

                    // Если показываем файлы после папки
                    if ($folder == true) {
                        $folder = false;
                        ?>
</div>
<div class="attached-files-list">
    <?php
                    }

                    display_file_item($file);

                }

                if ($file['_type'] == 'folder') {

                    // Переключаемся на отображение папок
                    $folder = true;

                    ?>
</div>
<div class="attached-files-list">
    <div class="attached-files-list-header"><?php echo $file['folder']; ?></div>
    <div class="attached-files-list-content">
        <?php if (!empty($file['folder_description'])) { ?>

        <p><?php echo $file['folder_description']; ?></p>

        <?php } ?>

        <div class="attached-files-list">
            <?php 
                                    if (isset( $file['files'])) {
                                        foreach ($file['files'] as $folders_file) {
                                            display_file_item($folders_file);

                                            /*
                                            if ($folders_file === end($file['files'])) {
                                                echo " - это последний элемент.\n";
                                            }*/
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
        }

    }
    
}

add_action('wp_enqueue_scripts', 'check_bootstrap_styles');

function check_bootstrap_styles() {

    wp_enqueue_style( 'attached-files-list', plugin_dir_url( __FILE__ ) . 'styles/style.css' );

    /*
    if (!wp_style_is('bootstrap', 'enqueued')) {
        // Если стили Bootstrap не подключены
        wp_enqueue_style( 'bootstrap', plugin_dir_url( __FILE__ ) . 'styles/bootstrap.min.css' );
    }
    */
}


function enqueue_attached_files_script() {
    // Получаем путь к файлу attached-files.js
    $script_path = plugin_dir_url( __FILE__ ) . 'js/attached-files.js';

    // Регистрируем и подключаем скрипт
    wp_enqueue_script(
        'attached-files-script',   // Уникальный идентификатор скрипта
        $script_path,              // URL к файлу скрипта
        array(),                   // Зависимости (оставляем пустым массивом, если нет зависимостей)
        null,                      // Версия (можно указать версию файла или null для отключения версионирования)
        true                       // Загружаем в подвале сайта
    );
}

// Хук для подключения скриптов и стилей
add_action('wp_enqueue_scripts', 'enqueue_attached_files_script');