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

/**
 * Personaliza el dashboard de la página de "Mi Cuenta"
 */
// Agregar nueva pestaña al menú de "Mi Cuenta"
function agregar_nueva_pestana_mi_cuenta( $items ) {
    // Eliminar pestañas no deseadas
    unset( $items['orders'] );     // Elimina la pestaña "Pedidos"
    unset( $items['downloads'] );  // Elimina la pestaña "Descargas"

    // Agregar nueva pestaña
    $items['consulta-mantenimiento'] = __('Mantenimiento', 'woocommerce');
    $items['mis-motos'] = __('Mis motos', 'woocommerce');
    $items['registrar-moto'] = __('Registrar moto', 'woocommerce');

    // Reordenar los elementos
    $items_ordenados = array(
        'consulta-mantenimiento'    => __('Mantenimiento', 'woocommerce'),
        'mis-motos'                 => __('Mis motos', 'woocommerce'),
        'registrar-moto'            => __('Registrar moto', 'woocommerce'),
        'edit-account'              => __('Detalles de la cuenta', 'woocommerce'),
        'customer-logout'           => __('Cerrar sesión', 'woocommerce'),
    );

    return $items_ordenados;
}
add_filter( 'woocommerce_account_menu_items', 'agregar_nueva_pestana_mi_cuenta' );

// Agregar el endpoint para las nuevas pestañas
function registrar_endpoint_nueva_pestana() {
    add_rewrite_endpoint( 'consulta-mantenimiento', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'mis-motos', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'registrar-moto', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'registrar_endpoint_nueva_pestana' );

// Mostrar contenido de las nuevas pestañas
function mdw_consulta_mantenimiento_content() {
    echo do_shortcode('[consulta_mantenimiento]');
}
add_action( 'woocommerce_account_consulta-mantenimiento_endpoint', 'mdw_consulta_mantenimiento_content' );

// Mostrar contenido de las nuevas pestañas
function mdw_listar_motos_content() {
    echo do_shortcode('[mdw_handler_user_moto]');
}
add_action( 'woocommerce_account_mis-motos_endpoint', 'mdw_listar_motos_content' );

// Mostrar contenido de las nuevas pestañas
function mdw_registrar_moto_content() {
    echo do_shortcode('[contact-form-7 id="3cba2ff" title="Registrar Moto"]');
}
add_action( 'woocommerce_account_registrar-moto_endpoint', 'mdw_registrar_moto_content' );

// Redirigir a la página de consulta de mantenimiento después de iniciar sesión
function custom_woocommerce_login_redirect($redirect, $user) {
    // Asegúrate de que el usuario esté autenticado
    if (isset($user->ID)) {
        // Cambia la URL según sea necesario
        return site_url('/mi-cuenta/consulta-mantenimiento/');
    }

    // Redirección por defecto si no se cumple la condición
    return $redirect;
}

add_filter('woocommerce_login_redirect', 'custom_woocommerce_login_redirect', 10, 2);