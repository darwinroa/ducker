<?php
// Registrar Nuevas Motos
// add_action('wpcf7_before_send_mail', 'registrar_nueva_moto');

// function registrar_nueva_moto($cf7)
// {
//   if ($cf7->id() == '4430') { // Verificar que sea el formulario correcto
//     $submission = WPCF7_Submission::get_instance();

//     if ($submission) {
//       $posted_data = $submission->get_posted_data();

//       // Obtener el ID del usuario desde el campo oculto
//       // $currentUserID = 14;
//       $currentUserID = isset($posted_data['user_id']) ? $posted_data['user_id'] : 0;

//       // Verificar si el ID del usuario es válido
//       if ($currentUserID == 0) {
//         error_log('No se ha enviado un ID de usuario válido.');
//         return;
//       }

//       // Verificar que el usuario está logueado
//       $user = get_user_by('id', $currentUserID);
//       if (!$user) {
//         error_log('El usuario con ID ' . $currentUserID . ' no existe.');
//         return;
//       }

//       error_log('ID del usuario actual: ' . $currentUserID);

//       // Obtener los datos del formulario
//       $marca = isset($posted_data['marca']) ? $posted_data['marca'] : '';
//       $placa = isset($posted_data['placa']) ? $posted_data['placa'] : '';
//       $modelo = isset($posted_data['modelo']) ? $posted_data['modelo'] : '';

//       $marca_clean = isset($marca[0]) ? $marca[0] : '';
//       $slugMarca = str_replace(' ', '-', $marca_clean);
//       $referenciaInputName = "referencia-$slugMarca";
//       $referencia = isset($posted_data[$referenciaInputName]) ? $posted_data[$referenciaInputName] : '';
//       $referencia_clean = isset($referencia[0]) ? $referencia[0] : '';

//       // Obtener los valores ya registrados de "motos" en el usuario
//       $motosRegistradas = get_user_meta($currentUserID, 'motos', true);
//       // $totalMotos = count($motosRegistradas);

//       if (empty($motosRegistradas)) {
//         $motosRegistradas = [];
//       }

//       // Crear un nuevo array con los datos de la moto
//       $nuevaMoto = [
//         'marca_de_moto' => $marca_clean,
//         'referencia' => $referencia_clean,
//         'placa' => $placa,
//         'modelo' => $modelo,
//       ];

//       // Añadir la nueva moto al array
//       // $motosRegistradas[] = $nuevaMoto;

//       // $motosRegistradas[0]['marca_de_moto'] = $marca_clean;
//       // $motosRegistradas[0]['referencia'] = $referencia_clean;
//       // $motosRegistradas[0]['placa'] = $placa;
//       // $motosRegistradas[0]['modelo'] = $modelo;
//       update_user_meta($currentUserID, 'placa', $placa);
//       // update_field('motos', $motosRegistradas, $currentUserID);
//       // Guardar los datos de las motos en el usuario
//       // update_user_meta($currentUserID, 'motos', $motosRegistradas);

//       error_log('Moto guardada correctamente para el usuario ID: ' . $currentUserID);
//     }
//   }
// }



// Registrar Nuevas Motos
// add_action('wpcf7_before_send_mail', 'registrar_nueva_moto');

// function registrar_nueva_moto($cf7)
// {
//   if ($cf7->id() == '4430') { // Verificar que sea el formulario correcto
//     $submission = WPCF7_Submission::get_instance();

//     if ($submission) {
//       $posted_data = $submission->get_posted_data();

//       // Obtener el ID del usuario desde el campo oculto
//       $currentUserID = isset($posted_data['user_id']) ? $posted_data['user_id'] : 0;

//       // Verificar si el ID del usuario es válido
//       if ($currentUserID == 0) {
//         error_log('No se ha enviado un ID de usuario válido.');
//         return;
//       }

//       // Verificar que el usuario está logueado
//       $user = get_user_by('id', $currentUserID);
//       if (!$user) {
//         error_log('El usuario con ID ' . $currentUserID . ' no existe.');
//         return;
//       }

//       // Obtener los datos del formulario
//       $marca = isset($posted_data['marca']) ? $posted_data['marca'] : '';
//       $placa = isset($posted_data['placa']) ? $posted_data['placa'] : '';
//       $modelo = isset($posted_data['modelo']) ? $posted_data['modelo'] : '';

//       $marca_clean = isset($marca[0]) ? $marca[0] : '';
//       $slugMarca = str_replace(' ', '-', $marca_clean);
//       $referenciaInputName = "referencia-$slugMarca";
//       $referencia = isset($posted_data[$referenciaInputName]) ? $posted_data[$referenciaInputName] : '';
//       $referencia_clean = isset($referencia[0]) ? $referencia[0] : '';

//       // Obtener los valores ya registrados de "motos" en el usuario
//       $motosRegistradas = get_field('motos', $currentUserID); // Usar ACF para obtener el campo Repeater

//       // Depuración
//       error_log('Motos registradas antes de agregar la nueva: ' . print_r($motosRegistradas, true));

//       if (empty($motosRegistradas)) {
//         $motosRegistradas = [];
//       }

//       // Crear un nuevo array con los datos de la moto
//       $nuevaMoto = [
//         'marca_de_moto' => $marca_clean,
//         'referencia' => $referencia_clean,
//         'placa' => $placa,
//         'modelo' => $modelo,
//       ];

//       // Añadir la nueva moto al array
//       $motosRegistradas[] = $nuevaMoto;

//       // Depuración: ver el array de motos después de agregar la nueva moto
//       error_log('Motos registradas después de agregar la nueva: ' . print_r($motosRegistradas, true));

//       // Guardar los datos de las motos en el campo Repeater de ACF
//       $resultado = update_field('motos', $motosRegistradas, $currentUserID); // Usar ACF para actualizar el campo Repeater

//       // Depuración: ver si se ha actualizado correctamente
//       if ($resultado) {
//         error_log('Moto guardada correctamente para el usuario ID: ' . $currentUserID);
//       } else {
//         error_log('Hubo un error al guardar la moto para el usuario ID: ' . $currentUserID);
//       }
//     }
//   }
// }



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
        // Si no es un array (puede ser una cadena serializada o vacío), lo inicializamos como array vacío
        $motosRegistradas = [];
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

      // Depuración: ver el array de motos después de agregar la nueva moto
      error_log('Motos registradas después de agregar la nueva: ' . print_r($motosRegistradas, true));

      // Serializar el array y guardarlo usando update_user_meta
      update_user_meta($currentUserID, 'motos', $motosRegistradas);

      // Depuración: ver si se ha actualizado correctamente
      error_log('Moto guardada correctamente para el usuario ID: ' . $currentUserID);
    }
  }
}
