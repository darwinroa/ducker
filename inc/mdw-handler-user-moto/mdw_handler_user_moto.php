<?php
add_shortcode('mdw_handler_user_moto', 'mdw_handler_user_moto_function');

function mdw_handler_user_moto_function() {
  // Validamos si el usuario está logueado
  if (!is_user_logged_in()) return false;

  $html = '';
  $htmlTBody = '';

  $current_user = is_author() ? get_the_author_meta('ID') : get_current_user_id();
  
  $motos_registradas = get_user_meta($current_user, 'motos', true);
  if ($motos_registradas && is_array($motos_registradas) && count($motos_registradas) > 0) {
    $html .= '<h1>Motos Registradas</h1>';

    foreach ($motos_registradas as $moto) {
      $placa = $moto['placa'];
      $marca = $moto['marca_de_moto'];
      $referencia = $moto['referencia'];
      $modelo = $moto['modelo'];

      $htmlTBody .= "
        <tr>
          <td>
            <button class='elementor-icon hs-eliminar-reserva' data-placa='$placa' data-user='$current_user'>
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
      <table>
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
    ";   
  }
  ob_start();

  return $html;
}