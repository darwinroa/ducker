<?php
add_shortcode('mdw_redimir_cupon', 'mdw_redimir_cupon_shortcode');
function mdw_redimir_cupon_shortcode() {
  $html = '';
  $currentUserID = get_current_user_id();
  $cuponID = get_the_ID();
  $redimido = false;

  // Obtener los cupones que el usuario ha redimido
  $cuponesRedimidos = get_user_meta($currentUserID, 'cupones', true);
  
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
    <div class='mdw__button_redimir-container'>
      <button disabled class='mdw__button_redimir mdw__button'>Redimir Cupón</button>
      <div class='mdw__message'>
        <span>
          Ya has redimido este cupón. Podrás ver más información de este cupón en <a href='/mi-cuenta/cupones' class='mdw__message_link'>Mi Cuenta > Cupones</a>
        </span>
      </div>  
    </div>
  ";

  $htmlRedimir = "
    <div class='mdw__button_redimir-container'>
      <button id='mdw__button_redimir' class='mdw__button_redimir mdw__button'>Redimir Cupón</button> 
    </div>
  ";
  
  wp_enqueue_style('mdw-cupones-style', get_stylesheet_directory_uri() . '/inc/mdw-cupones/style.css');

  wp_enqueue_script('mdw-cupon-redimir-script', get_stylesheet_directory_uri() . '/inc/mdw-cupones/mdw_cupon_redimir.js', array('jquery'), null, true);
  wp_localize_script('mdw-cupon-redimir-script', 'wp_ajax', array(
    'ajax_url'            => admin_url('admin-ajax.php'),
    'nonce'               => wp_create_nonce('load_more_nonce'),
    'cupon_id'            => $cuponID,
    'theme_directory_uri' => get_stylesheet_directory_uri(),
  ));
  ob_start();
  $html .= $redimido ? $htmlRedimido : $htmlRedimir;

  $html .= $redimido ? "" : mdw_popup_redimir_cupon($currentUserID);
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
    <div id='mdw__redimir_popup' class='mdw__redimir_popup' hidden>
      <div class='mdw__redimir_popup-overlay'></div>
      <div class='mdw__redimir_popup-container'>
        <h3 class='mdw__redimir_popup-title'>Redimir Cupón</h3>
        <span class='mdw__redimir_popup-description'>Debes seleccionar la placa de la moto para la cuál deseas utilizar éste cupón.</span>
        <div class='mdw__redimir_popup-placa_container'>
          <label for='mdw__select_placa'>Placa Moto</label>
          <select id='mdw__select_placa'>
            $options
          </select>
        </div>
        <div class='mdw__buttons_container'>
          <button type='button' id='mdw__redimir_cupon-button' class='mdw__button' disabled>Redimir</button>
          <button type='button' id='mdw__cancelar_cupon' class='mdw__button'>Cancelar</button>
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
    // Datos recibidos por la solicitud Ajax
    $placa = isset($_POST['placa']) ? sanitize_text_field($_POST['placa']) : ''; // Recibe el dato suministrado por el usuario
    $cuponID = isset($_POST['cupon_id']) ? sanitize_text_field($_POST['cupon_id']) : ''; // Recibe el dato suministrado por el usuario

    //Datos del usuario
    $currentUser = wp_get_current_user();
    $currentUserID = $currentUser->ID;
    $userCedula = get_field('numero_cedula', 'user_' . $currentUserID);
    $first_name = $currentUser->first_name;
    $last_name  = $currentUser->last_name;

    $userName = trim($last_name . ' ' . $first_name);

    $cuponName = get_the_title($cuponID); // Nombre del cupón

    $apiKeyID = get_field('api_key_google_sheet', $cuponID); // API Key de google sheet

    // URL del Web App de Google Script
    $url = "https://script.google.com/macros/s/$apiKeyID/exec";

    // Datos a enviar a Google Sheet
    $data = [
        'post_name'     => $cuponName,
        'user_name'     => $userName,
        'numero_cedula' => $userCedula,
        'placa_moto'    => $placa
    ];

    // Envia solicitud POST a Google Sheet
    $response = wp_remote_post($url, [
      'method' => 'POST',
      'body'   => $data,
    ]);
    
    // Obtiene los cupones que el usuario ha redimido
    $cuponesRedimidos = get_user_meta($currentUserID, 'cupones', true);

    // Asegurarnos de que $cuponesRedimidos sea un array
    if (!is_array($cuponesRedimidos)) {
      $cuponesRedimidos = [];
    }

    // Crea un nuevo array con los datos del cupón
    $nuevoCupon = [
      'cuponID' => $cuponID,
      'placa' => $placa,
    ];

    // Añade el nuevo cupón al array
    $cuponesRedimidos[] = $nuevoCupon;

    // Serializa el array y lo guardar
    update_user_meta($currentUserID, 'cupones', $cuponesRedimidos);

    // Validar si fue exitoso (código 200)
    wp_send_json_success([
        'message' => 'Cupón redimido correctamente.',
    ]);
    wp_die();
  }
}

?>

