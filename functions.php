<?php
/*
 * This is the child theme for Hub theme, generated with Generate Child Theme plugin by catchthemes.
 *
 * (Please see https://developer.wordpress.org/themes/advanced-topics/child-themes/#how-to-create-a-child-theme)
 */
add_action('wp_enqueue_scripts', 'hub_child_enqueue_styles');
function hub_child_enqueue_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style')
    );
}

//////////////////////////////////////////////////////////
///////////////////// CODE SNIPPETS //////////////////////
//////////////////////////////////////////////////////////
require 'inc/dk-query-maintenance/dk-query-maintenance.php'; // Consulta sobre el mantenimiento
require "inc/dk-user-register/dk_user_register.php"; // Handler user register
require "inc/dk-moto-register/dk_moto_register.php"; // Handler moto register
require "inc/mdw-lists-users-csv/mdw_lists_users_csv.php"; // Handler lists users csv
require "inc/mdw-handler-user-moto/mdw_handler_user_moto.php"; // Handler user moto


// Shortcode para obtener solo el ID del usuario actual
function user_id_shortcode($atts)
{
    // Verifica si el usuario está conectado
    if (is_user_logged_in()) {
        $user = wp_get_current_user(); // Obtiene el usuario actual
        return $user->ID; // Devuelve solo el ID del usuario
    }
    return ''; // Si no hay usuario conectado, devuelve vacío
}
add_shortcode('user_id', 'user_id_shortcode');


// Shortcode para obtener solo el Correo Electrónico de un usuario específico
function user_email_shortcode()
{
    $author_email = get_the_author_meta('user_email');

    return esc_html($author_email);

}

add_shortcode('user_email', 'user_email_shortcode');