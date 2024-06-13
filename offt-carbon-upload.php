<?php
/*
Plugin Name: Offt Carbon Upload
Plugin URI:  http://example.com
Description: Плагин загрузки и отображения файлов на статических страницах
Version:     1.0
Author:      Your Name
*/
if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

function my_custom_plugin_check_carbon_fields() {
    if ( ! is_plugin_active( 'carbon-fields/carbon-fields-plugin.php' ) ) {
        // Carbon Fields не активен, добавляем уведомление в админку
        add_action('admin_notices', 'my_custom_plugin_carbon_fields_admin_notice');

        // Прекращаем выполнение основных функций плагина
        add_action('admin_init', 'my_custom_plugin_deactivate');
    } else {

    }
}
add_action('admin_init', 'my_custom_plugin_check_carbon_fields');

function my_custom_plugin_carbon_fields_admin_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('Your Custom Plugin requires the Carbon Fields plugin to be active. Please activate Carbon Fields to use this plugin.', 'your-textdomain'); ?></p>
    </div>
    <?php
}

function my_custom_plugin_deactivate() {
    // Здесь можно деактивировать основные функции вашего плагина
    // Например, отменить регистрацию кастомных полей, настроек и т.д.
}



use Carbon_Fields\Container;
use Carbon_Fields\Field;


add_action('carbon_fields_register_fields', 'crb_attach_theme_options');
function crb_attach_theme_options() {
    Container::make('post_meta', __('Additional Files', 'your-textdomain'))
        ->where('post_type', '=', 'page') // Ограничить поля только для страниц
        
        ->add_fields(array(
            Field::make('complex', 'crb_files', __('Files'))

                // Множественное поле
                ->add_fields( 'folder', 'Папка', array(
                    Field::make( 'text', 'folder', 'Название папки'  ),
                    Field::make( 'rich_text', 'folder_description', 'Описание папки' ),
                    Field::make( 'complex', 'files', 'Вложенные файлы' )
                        ->add_fields( 'file', 'Файл', array(
                            Field::make( 'text', 'file_description', 'Название файла'  ),
                            Field::make( 'file', 'file', 'Файл' )
                        ))
                ))
                

                ->add_fields( 'file', 'Файл', array(
                    Field::make( 'text', 'file_description', 'Название файла'  ),
                    Field::make( 'file', 'file', 'Файл' )
                ))
        ));
}

add_action('after_setup_theme', 'crb_load');
function crb_load() {
    \Carbon_Fields\Carbon_Fields::boot();
}

function formatBytes($bytes, $precision = 2) {
    $units = array('Б', 'Кб', 'Мб', 'Гб', 'Тб');

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
        <a href="<?php echo esc_url($file_url); ?>" class="list-group-item">
            <?php echo esc_html($file_name); ?>
            <span class="attached-file-extension"><?php echo formatBytes($filesize); ?></span>
        </a>
        <?php
    }
}



// Hook to display the files
add_filter('the_content', 'my_custom_display_files');
function my_custom_display_files($content) {
    if (is_page()) { // Проверка, что это страница


        $files = carbon_get_the_post_meta('crb_files');

        echo '<pre>';
        print_r($files);
        echo '</pre>';

        if ($files) {
            ?>

            <h4>Прикрепленные файлы</h4>
            <div class="list-group attached-files-list mb-4">
            <?php
            
            foreach ($files as $file) {
                if ($file['_type'] == 'file') {
                    display_file_item($file);
                }

                if ($file['_type'] == 'folder') {


                    ?>
                        </div>
                        <div class="list-group attached-files-list mb-4">
                        <div class="list-group-item attached-files-list-header"><?php echo $file['folder']; ?></div>
                        <div class="list-group-item attached-files-list-content p-4">
                            <p><?php echo $file['folder_description']; ?></p>
                            
                            <div class="list-group">
                                <?php 
                                    if (isset( $file['files'])) {
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
        }

    }
    
}

add_action('wp_enqueue_scripts', 'check_bootstrap_styles');

function check_bootstrap_styles() {

    wp_enqueue_style( 'attached-files-list', plugin_dir_url( __FILE__ ) . 'styles/style.css' );

    if (!wp_style_is('bootstrap', 'enqueued')) {
        // Если стили Bootstrap не подключены
        wp_enqueue_style( 'bootstrap', plugin_dir_url( __FILE__ ) . 'styles/bootstrap.min.css' );
    }
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
