<?php
// Registrar Nuevos Usuarios
function registrar_nuevo_usuario($cf7)
{

  if ($cf7->id() == '4242') {
    $submission = WPCF7_Submission::get_instance();


    if ($submission) {
      $posted_data = $submission->get_posted_data();

      // Datos del usuario
      $firstName   = isset($posted_data['nombres']) ? $posted_data['nombres'] : '';
      $lastName   = isset($posted_data['apellidos']) ? $posted_data['apellidos'] : '';
      $email      = isset($posted_data['your-email'])  ? $posted_data['your-email'] : '';
      $genero     = isset($posted_data['genero'])  ? $posted_data['genero'] : '';
      // Datos de la moto
      $marca      = isset($posted_data['marca'])  ? $posted_data['marca']   : '';
      $placa      = isset($posted_data['placa'])  ? $posted_data['placa']   : '';
      $modelo     = isset($posted_data['modelo']) ? $posted_data['modelo']  : '';

      // Decodificar el string JSON y obtener el primer elemento del array
      $genero_clean     = $genero[0];
      $marca_clean      = $marca[0];
      $slugMarca = str_replace(' ', '-', $marca_clean);
      $referenciaInputName = "referencia-$slugMarca";
      $referencia = $posted_data[$referenciaInputName];
      $referencia_clean = $referencia[0];

      $user = get_user_by('email', $email);
      if ($user) $user_id = $user->ID;

      if (!is_wp_error($user_id)) {
        // El usuario ha sido creado correctamente

        // Guarda los datos del usuario
        update_user_meta($user_id, 'first_name', $firstName);
        update_user_meta($user_id, 'last_name', $lastName);
        update_user_meta($user_id, 'genero', $genero_clean);

        // Guardo los datos de la moto
        // Crear un nuevo array con los datos de la moto
        $motoInfo = [
          'marca_de_moto' => $marca_clean,
          'referencia' => $referencia_clean,
          'placa' => $placa,
          'modelo' => $modelo,
        ];

        // Añadir la nueva moto al array
        $nuevaMoto[] = $motoInfo;

        // Depuración: ver el array de motos después de agregar la nueva moto
        error_log('Primera Moto registrada por el usuario: ' . print_r($nuevaMoto, true));

        // Serializar el array y guardarlo usando update_user_meta
        update_user_meta($user_id, 'motos', $nuevaMoto);

        // Depuración: ver si se ha actualizado correctamente
        error_log('Moto guardada correctamente para el usuario ID: ' . $user_id);
      } else {
        // Ha ocurrido un error al crear el usuario
      }
    }
  }
}
add_action('wpcf7_before_send_mail', 'registrar_nuevo_usuario');
