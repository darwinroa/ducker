<?php
/**
 * @package mdw-cupones
 * @version 1.0.0
 * Author: Darwin Roa
 * Description: Este shortcode muestra todos los cupones activos en la página donde se inserte. Los cupones se obtienen a través de una consulta personalizada y se muestran utilizando un template de Elementor.
 * Usage: [mdw_cupones_query]
 */
add_shortcode('mdw_cupones_query', 'mdw_cupones_query_shortcode');
function mdw_cupones_query_shortcode() {
  wp_enqueue_style('mdw-cupones-style', get_stylesheet_directory_uri() . '/inc/mdw-cupones/style.css');
  ob_start();
  $html = '';

  $args = array(
    'post_type' => 'cupones',
    'posts_per_page' => -1, 
    'post_status' => 'publish',
  );
  $cupones_query = get_cached_query('mdw_cupones_query_all', $args, 12*3600); // Cache por 12 horas


  if ($cupones_query->have_posts()) {
    $html .= '<div class="mdw__cupones_container">';
    while ($cupones_query->have_posts()) {
      $cupones_query->the_post();
      $fechaVencimiento = get_field('fecha_de_vencimiento_del_cupon');
      if ($fechaVencimiento) {
        // Se valida que el cupón esté activo comparando la fecha de vencimiento con la fecha actual
        $fechaTimestamp = DateTime::createFromFormat('d/m/Y', $fechaVencimiento)->getTimestamp();
        if ($fechaTimestamp >= time()) {
          $html .= do_shortcode('[elementor-template id="4702"]');
        }
      }
    }
    $html .= '</div>';
  } else {
    $html .= '<p>No se encontraron cupones.</p>';
  }
  wp_reset_postdata();
  ob_get_clean();
  return $html;
}