<?php
add_shortcode('mdw_handler_user_moto', 'mdw_handler_user_moto_function');

function mdw_handler_user_moto_function() {
  // Validamos si el usuario está logueado
  if (!is_user_logged_in()) return false;

  $html = '';
  $htmlTBody = '';
  $htmlPopUp = '
    <div id="popup-confirmacion" class="popup-confirmacion">
      <div class="popup-confirmacion__content">
        <p class="popup-confirmacion__message">
          ¿Estás seguro de que deseas eliminar esta moto?
        </p>
        <div class="popup-confirmacion__buttons">
          <button id="btn-confirmar-eliminar" class="popup-confirmacion__button popup-confirmacion__button--confirm">
            Sí, eliminar
          </button>
          <button id="btn-cancelar-eliminar" class="popup-confirmacion__button popup-confirmacion__button--cancel">
            Cancelar
          </button>
        </div>
      </div>
    </div>';

  $current_user = is_author() ? get_the_author_meta('ID') : get_current_user_id();

  //Carga CSS
  wp_enqueue_style('handler-user-moto', get_stylesheet_directory_uri() . '/inc/mdw-handler-user-moto/handler_user_moto.css');

  //Carga JS
  wp_enqueue_script('handler-user-moto', get_stylesheet_directory_uri() . '/inc/mdw-handler-user-moto/handler_user_moto.js', array('jquery'), '1.0', true);

  // Pasar la URL de admin-ajax.php al script
  wp_localize_script('handler-user-moto', 'eliminarmoto', array(
    'ajaxurl' => admin_url('admin-ajax.php'),
    'user_id' => $current_user,
  ));
  
  ob_start();
  $motos_registradas = get_user_meta($current_user, 'motos', true);
  if ($motos_registradas && is_array($motos_registradas) && count($motos_registradas) > 0) {


    $html .= '<h2 class="mdw__title_table">Motos Registradas</h2>';

    foreach ($motos_registradas as $moto) {
      $placa = $moto['placa'];
      $marca = $moto['marca_de_moto'];
      $referencia = $moto['referencia'];
      $modelo = $moto['modelo'];

      $htmlTBody .= "
        <tr>
          <td>
            <button class='elementor-icon mdw-eliminar-moto' data-placa='$placa'>
              <i aria-hidden='true' class='fas fa-trash-alt'></i>
            </button>
          </td>
          <td>$placa</td>
          <td>$marca</td>
          <td>$referencia</td>
          <td>$modelo</td>
        </tr>
      ";
    }
  
    $html .= "
      <table class='mdw__handler_moto-table'>
        <thead>
          <tr>
            <th>[X]</th>
            <th>Placa</th>
            <th>Marca</th>
            <th>Referencia</th>
            <th>Modelo</th>
          </tr>
        </thead>
        <tbody>
          $htmlTBody
        </tbody>
      </table>
      $htmlPopUp
    ";   
  } else $html .= '<p>No tienes ninguna moto registrada. Registra una moto en <a href="/mi-cuenta/registrar-moto/">Mi cuenta > Registrar Moto</a></p>';

  ob_get_clean();
  return $html;
}

function eliminar_moto()
{
  if (isset($_POST['placa'], $_POST['current_user'])) {
    $current_user = $_POST['current_user'];
    $placa = $_POST['placa'];
    $motos_registradas = get_user_meta($current_user, 'motos', true);
    $motos_filtradas = array_values(array_filter($motos_registradas, function ($moto) use ($placa) {
    return $moto['placa'] != $placa;
    }));


    update_user_meta($current_user, 'motos', $motos_filtradas);

    wp_send_json(array('message' => 'Moto eliminada con éxito', 'type' => 'success'));
  }
}
add_action('wp_ajax_eliminar_moto', 'eliminar_moto');
add_action('wp_ajax_nopriv_eliminar_moto', 'eliminar_moto');



