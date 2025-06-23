<?php
add_shortcode('mdw_mis_cupones', 'mdw_mis_cupones_function');

function mdw_mis_cupones_function() {
  wp_enqueue_style('mdw-cupones-style', get_stylesheet_directory_uri() . '/inc/mdw-cupones/style.css');
  
  ob_start();
  $html = '';

  // Obtener los cupones que el usuario ha redimido
  $currentUserID = get_current_user_id();
  $cuponesRedimidos = get_user_meta($currentUserID, 'cupones', true);
  $misCuponesID = [];
  
  if($cuponesRedimidos) {
    foreach($cuponesRedimidos as $cupon) {
      $misCuponesID[] = $cupon['cuponID'];
    }


    $args = array(
      'post_type' => 'cupones',
      'posts_per_page' => -1,
      'post_status' => 'publish',
      'post__in' => $misCuponesID, 
    );
    $cupones_query = new WP_Query($args);


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
    } 
  } else {
    $html .= '<p>Aún no haz redimido ningún cupón.</p>';
  }


  $html .= "<div class='mdw__account_button-more_cupon'><a href='/cupones-disponibles/' class='mdw__button'>Ver más cupones</a></div>";
  wp_reset_postdata();
  ob_get_clean();
  return $html;
}