<?php
// Registrar Nuevas Motos
add_action('wpcf7_before_send_mail', 'registrar_nueva_moto');

function registrar_nueva_moto($cf7)
{
  if ($cf7->id() == '4430') { // Verificar que sea el formulario correcto
    $submission = WPCF7_Submission::get_instance();

    if ($submission) {
      $posted_data = $submission->get_posted_data();

      // Obtener el ID del usuario desde el campo oculto
      $currentUserID = isset($posted_data['user_id']) ? $posted_data['user_id'] : 0;

      // Verificar si el ID del usuario es válido
      if ($currentUserID == 0) {
        error_log('No se ha enviado un ID de usuario válido.');
        return;
      }

      // Verificar que el usuario está logueado
      $user = get_user_by('id', $currentUserID);
      if (!$user) {
        error_log('El usuario con ID ' . $currentUserID . ' no existe.');
        return;
      }

      // Obtener los datos del formulario
      $marca = isset($posted_data['marca']) ? $posted_data['marca'] : '';
      $placa = isset($posted_data['placa']) ? $posted_data['placa'] : '';
      $modelo = isset($posted_data['modelo']) ? $posted_data['modelo'] : '';

      $marca_clean = isset($marca[0]) ? $marca[0] : '';
      $slugMarca = str_replace(' ', '-', $marca_clean);
      $referenciaInputName = "referencia-$slugMarca";
      $referencia = isset($posted_data[$referenciaInputName]) ? $posted_data[$referenciaInputName] : '';
      $referencia_clean = isset($referencia[0]) ? $referencia[0] : '';

      // Obtener los valores ya registrados de "motos" en el usuario
      $motosRegistradas = get_user_meta($currentUserID, 'motos', true);

      // Asegurarnos de que $motosRegistradas sea un array
      if (!is_array($motosRegistradas)) {
        $motosRegistradas = [];
      }

      // Verificar si la placa ya está registrada
      foreach ($motosRegistradas as $moto) {
        if ($moto['placa'] == $placa) {
          error_log('La moto con placa ' . $placa . ' ya se encuentra registrada, registra otra moto.');
          return;
        }
      }

      // Crear un nuevo array con los datos de la moto
      $nuevaMoto = [
        'marca_de_moto' => $marca_clean,
        'referencia' => $referencia_clean,
        'placa' => $placa,
        'modelo' => $modelo,
      ];

      // Añadir la nueva moto al array
      $motosRegistradas[] = $nuevaMoto;

      // Serializar el array y guardarlo usando update_user_meta
      update_user_meta($currentUserID, 'motos', $motosRegistradas);
    }
  }
}



/**
 * Validar que la placa de la moto no esté registrada
 */
add_filter('wpcf7_validate_text*', 'validar_placa_moto', 10, 2);
add_filter('wpcf7_validate_text', 'validar_placa_moto', 10, 2);

function validar_placa_moto($result, $tag)
{
  if ( is_user_logged_in() ){
    // Verificamos que el campo validado sea 'placa'
    if ($tag['name'] !== 'placa') {
        return $result;
    }

    // Obtenemos los datos enviados en el formulario
    $submission = WPCF7_Submission::get_instance();
    if (!$submission) {
        return $result;
    }

    $posted_data = $submission->get_posted_data();
    
    $placa = isset($posted_data['placa']) ? sanitize_text_field($posted_data['placa']) : '';
    $currentUserID = isset($posted_data['user_id']) ? intval($posted_data['user_id']) : 0;

    // Si no hay un usuario válido, mostramos un error
    if ($currentUserID === 0) {
        $result->invalidate($tag, 'Error: Usuario no válido.');
        return $result;
    }

    // Obtenemos los datos del usuario en WordPress
    $user = get_user_by('id', $currentUserID);
    if (!$user) {
        $result->invalidate($tag, 'Error: Usuario no encontrado.');
        return $result;
    }

    // Recuperamos las motos registradas
    $motosRegistradas = get_user_meta($currentUserID, 'motos', true);
    if (!is_array($motosRegistradas)) {
        $motosRegistradas = [];
    }

    // Verificamos si la placa ya está registrada
    foreach ($motosRegistradas as $moto) {
        if ($moto['placa'] === $placa) {
            $result->invalidate($tag, 'Esta placa ya está registrada. Intente con otra.');
            return $result;
        }
    }

  }
    return $result;
}


