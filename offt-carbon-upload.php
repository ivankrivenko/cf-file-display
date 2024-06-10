<?php
/*
Plugin Name: Offt Carbon Upload
Plugin URI:  http://example.com
Description: Плагин загрузки и отображения файлов на статических страницах
Version:     1.0
Author:      Your Name
*/

use Carbon_Fields\Container;
use Carbon_Fields\Field;


add_action('carbon_fields_register_fields', 'crb_attach_theme_options');
function crb_attach_theme_options() {
    Container::make('post_meta', __('Additional Files', 'your-textdomain'))
        ->where('post_type', '=', 'page') // Ограничить поля только для страниц
        ->add_fields(array(
            Field::make('complex', 'crb_files', __('Files'))
                ->add_fields(array(
                    Field::make('file', 'crb_file', __('File'))
                        ->set_value_type('url') // Сохранять только URL файла
                ))
        ));
}

add_action('after_setup_theme', 'crb_load');
function crb_load() {
    \Carbon_Fields\Carbon_Fields::boot();
}



// Hook to display the files
add_filter('the_content', 'my_custom_display_files');
function my_custom_display_files($content) {
    if (is_page()) { // Проверка, что это страница
        $files = carbon_get_the_post_meta('crb_files');
        if ($files) {
            $file_list = '<ul>';
            foreach ($files as $file) {
                $file_url = esc_url($file['crb_file']);
                $file_name = basename($file_url);
                $file_list .= '<li><a href="' . $file_url . '">' . $file_name . '</a></li>';
            }
            $file_list .= '</ul>';
            // Добавить список файлов перед или после основного контента
            $content .= $file_list;
        }
    }
    return $content;
}