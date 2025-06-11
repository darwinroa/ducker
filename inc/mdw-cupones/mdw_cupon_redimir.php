<?php
add_shortcode('mdw_redimir_cupon', 'mdw_redimir_cupon_shortcode');
function mdw_redimir_cupon_shortcode() {
  $html = '';
  $currentUserID = get_current_user_id();
  $cuponID = get_the_ID();
  $redimido = false;

  // Obtener los cupones que el usuario ha redimido
  $cuponesRedimidos = get_user_meta($currentUserID, 'cupones', true);
  var_dump($cuponesRedimidos);
  
  if($cuponesRedimidos) {
    foreach($cuponesRedimidos as $cupon) {
      if($cupon['cuponID'] == $cuponID) {
        $redimido = true;
        break;
      }
    }
  }

  if (!$currentUserID) {
    return $html; // No se muestra nada si el usuario no está logueado
  }

  $htmlRedimido = "
    <button disabled>Redimir Cupón</button>
    <div class='mdw__message'>
      <span>
        Ya has redimido este cupón. Podrás ver más información de este cupón en <a href='/mi-cuenta/cupones' class='mdw__message_link'>Mi Cuenta > Cupones</a>
      </span>
    </div>  
  ";

  $htmlRedimir = "<button>Redimir Cupón</button>";
  
  wp_enqueue_script('mdw-cupon-redimir-script', get_stylesheet_directory_uri() . '/inc/mdw-cupones/mdw_cupon_redimir.js', array('jquery'), null, true);
  wp_localize_script('mdw-cupon-redimir-script', 'wp_ajax', array(
    'ajax_url'          => admin_url('admin-ajax.php'),
    'nonce'             => wp_create_nonce('load_more_nonce'),
    'cupon_id'          => $cuponID,
  ));
  ob_start();
  $html .= $redimido ? $htmlRedimido : $htmlRedimir;

  // $html .= $redimido ? "" : mdw_popup_redimir_cupon($currentUserID);
  $html .= mdw_popup_redimir_cupon($currentUserID);
  ob_get_clean();
  return $html;
}

function mdw_popup_redimir_cupon($currentUserID) {
  $motosRegistradas = get_user_meta($currentUserID, 'motos', true);

  // Generar el select con las placas.
  $options = "<option value='' selected disabled>Seleccione una placa</option>";
  foreach ($motosRegistradas as $moto) {
    $placaMoto = htmlspecialchars($moto["placa"]);
    $options .= "<option value=' $placaMoto'>$placaMoto</option>";
  }
  
  $html = '';
  $html .= "
    <div id='mdw__redimir_popup' class='mdw__redimir_popup'>
      <div class='mdw__redimir_popup-container'>
        <select id='mdw__select_placa'>
          $options
        </select>
        <div class='mdw__buttons_container'>
          <button type='button'>Cancelar</button>
          <button type='button' id='mdw__redimir_cupon-button'>Redimir</button>
        </div>
      </div>
    </div>";
  return $html;
}

if (!function_exists('mdw_redimir_cupon_ajax')) {
  add_action('wp_ajax_nopriv_mdw_redimir_cupon_ajax', 'mdw_redimir_cupon_ajax');
  add_action('wp_ajax_mdw_redimir_cupon_ajax', 'mdw_redimir_cupon_ajax');

  function mdw_redimir_cupon_ajax()
  {
    check_ajax_referer('load_more_nonce', 'nonce');

    $placa = isset($_POST['placa']) ? sanitize_text_field($_POST['placa']) : ''; // Recibe el dato suministrado por el usuario
    $cuponID = isset($_POST['cupon_id']) ? sanitize_text_field($_POST['cupon_id']) : ''; // Recibe el dato suministrado por el usuario

    //Datos del usuario
    $currentUser = wp_get_current_user();
    $currentUserID = $currentUser->ID;
    $userName = $currentUser->display_name;
    $userCedula = get_field('numero_cedula', 'user_' . $currentUserID);

    $cuponName = get_the_title($cuponID);

    $apiKeyID = 'AKfycbw6mrKZKM2IP0LHy2F0u7WvzwGETxNxfrlYS-hEzrTsx9ECYCvKZ7sqdO78FbfmclIKwg';

    // URL del Web App de Google Script
    $url = "https://script.google.com/macros/s/$apiKeyID/exec";

    // Datos a enviar
    $data = [
        'post_name'     => $cuponName,
        'user_name'     => $userName,
        'numero_cedula' => $userCedula,
        'placa_moto'    => $placa
    ];

    // Enviar solicitud POST
    wp_remote_post($url, [
        'method' => 'POST',
        'body' => $data
    ]);
    
    // Obtener los cupones que el usuario ha redimido
    $cuponesRedimidos = get_user_meta($currentUserID, 'cupones', true);

    // Asegurarnos de que $cuponesRedimidos sea un array
    if (!is_array($cuponesRedimidos)) {
      $cuponesRedimidos = [];
    }

    // Crear un nuevo array con los datos del cupón
    $nuevoCupon = [
      'cuponID' => $cuponID,
      'placa' => $placa,
    ];

    // Añadir el nuevo cupón al array
    $cuponesRedimidos[] = $nuevoCupon;

    // Serializar el array y guardarlo usando update_user_meta
    update_user_meta($currentUserID, 'cupones', $cuponesRedimidos);

    $html = 'si funciona';
    wp_send_json_success($html);
    wp_die();
  }
}

?>

