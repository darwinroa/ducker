<?php

/**
 * La siguiente clase convierte la data del CSV en un objeto facil de recorrer y de consultar
 * Ademas crea los métodos necesarios para manipular la data de manera eficás
 */
class CSVData
{
  private $data;

  public function __construct($csvFile)
  {
    $this->data = $this->readCSV($csvFile);
  }

  private function readCSV($csvFile)
  {
    $rows = [];
    if (($handle = fopen($csvFile, "r")) !== false) {
      $header = fgetcsv($handle, 1000, ",");
      $header = $this->generateHeaders($header);
      while (($row = fgetcsv($handle, 1000, ",")) !== false) {
        $rows[] = array_combine($header, $row);
      }
      fclose($handle);
    }
    return $rows;
  }

  // Genera un header para las columnas que no tienen un nombre asignado en el CSV
  private function generateHeaders($header)
  {
    foreach ($header as $index => $name) {
      if (empty($name)) {
        $header[$index] = 'Cambio_' . ($index - 5);
      }
    }
    return $header;
  }

  // Retorna toda la data
  public function getData()
  {
    return $this->data;
  }

  // Retorna la data de una fila específicada en el atributo $index
  public function getRow($index)
  {
    return isset($this->data[$index]) ? $this->data[$index] : null;
  }

  // Retorna la data de una columana especificada el atributo $columnName
  public function getColumn($columnName)
  {
    return array_column($this->data, $columnName);
  }

  // Retorna la data especificada por una función $callback
  public function filterData($callback)
  {
    return array_filter($this->data, $callback);
  }
}

/**
 * Shortcode: Crea el formulario para la consulta del 
 * mantenimiento programado de la moto registrada por el usuario
 */
add_shortcode('consulta_mantenimiento', 'dk_query_maintenance_function');

function dk_query_maintenance_function()
{
  if (!is_user_logged_in()) return;
  wp_enqueue_style('dc-members-style', get_stylesheet_directory_uri() . '/inc/dk-query-maintenance/dk-query-maintenance.css', array(), '1.0');
  wp_enqueue_script('dk-query-maintenance-script', get_stylesheet_directory_uri() . '/inc/dk-query-maintenance/dk-query-maintenance.js', array('jquery'), null, true);
  wp_localize_script('dk-query-maintenance-script', 'wp_ajax', array(
    'ajax_url'          => admin_url('admin-ajax.php'),
    'nonce'             => wp_create_nonce('load_more_nonce'),
    'theme_directory_uri' => get_stylesheet_directory_uri(),
  ));
  // Obtiene datos de marca, referencia, placa y modelo de la moto suministrada por el usuario al momento de registrarse
  $currentUserID = get_current_user_id(); // ID del usuario actual 

  $motosRegistradas = get_user_meta($currentUserID, 'motos', true);

  // Generar el select con las placas.
  $options = "<option value='' selected disabled>Seleccione una placa</option>";
  foreach ($motosRegistradas as $moto) {
    $placaMoto = htmlspecialchars($moto["placa"]);
    $options .= "<option value=' $placaMoto'>$placaMoto</option>";
  }

  ob_start();
  // Construir el mensaje de respuesta con los datos del usuario
  $html = '';
  $html .= "
    <div class='dk__container_moto-info'>
      <h5 class='dk__moto-info'>Selecciona una placa:</h5>
      <select name='placas' id='placas'>$options</select>
    </div>
  ";
  $html .= "
    <div class='dk__container_consulta-moto'>
      <div class='dk__consulta_moto-description'>
        <p>Consulta cuando será tu próximo mantenimiento programado</p>
      </div>
      <div class='dk__consulta_moto-form'>
        <label for='kilometer'>Ingrese Kilometraje de su moto:</label>
        <input type='text' id='kilometer' name='kilometer' required>
        <button type='button' id='query-maintenance' disabled>Consultar</button>
      </div>
    </div>
    <div id='dk_result' class='dk_result'></div>
  ";
  ob_get_clean();
  return $html;
}

/**
 * Realiza la consulta del mantenimiento programado via ajax. 
 * Usa el kilometraje ingresado por el usuario en el formulario
 */
if (!function_exists('dk_query_maintenance_ajax')) {
  add_action('wp_ajax_nopriv_dk_query_maintenance_ajax', 'dk_query_maintenance_ajax');
  add_action('wp_ajax_dk_query_maintenance_ajax', 'dk_query_maintenance_ajax');

  function dk_query_maintenance_ajax()
  {
    check_ajax_referer('load_more_nonce', 'nonce');

    $kilometer = isset($_POST['kilometer']) ? sanitize_text_field($_POST['kilometer']) : ''; // Recibe el dato suministrado por el usuario
    $placa = isset($_POST['placa']) ? sanitize_text_field($_POST['placa']) : ''; // Recibe el dato suministrado por el usuario
    // Verificar si el kilometraje es un número entero y multiplo de 500
    if (is_numeric($kilometer)) {
      $kilometer = (int)$kilometer;
      $kilometer = roundToNearest500($kilometer);
      $kilometer = number_format($kilometer);
    }

    // Obtiene datos de marca, referencia, placa y modelo de la moto suministrada por el usuario al momento de registrarse
    $currentUserID = get_current_user_id(); // ID del usuario actual 

    // Obtener los valores ya registrados de "motos" en el usuario
    $motosRegistradas = get_user_meta($currentUserID, 'motos', true);

    $motoConsulta = array_filter($motosRegistradas, function ($moto) use ($placa) {
        return $moto['placa'] === $placa;
    });

    // Verifica si se encontró algún resultado
    if (!empty($motoConsulta)) {
        $motoEncontrada = reset($motoConsulta); 
    }

    $marca = $motoEncontrada['marca_de_moto'];
    $referencia = $motoEncontrada['referencia'];
    $modelo = $motoEncontrada['modelo'];

    $fileName = normalizeMarca($marca); // Se normaliza la marca de la moto para un formato uniforme
    $csvFile = get_stylesheet_directory_uri() . '/csv/' . $fileName . '.csv'; // Obtiene el archivo csv correspondiente a la marca de la moto

    // Obtener todos los datos del CSV en un objeto
    $csvData = new CSVData($csvFile);
    $html = '';
    // Se obtiene el número de fila donde se encontró el kilometraje indicado
    $rowDataFind = getCSVDataNumberRow($csvData, $referencia, $kilometer);
    $rowNumber = $rowDataFind[0];
    $isFind = $rowDataFind[1];
    $hasMaintenance = $rowDataFind[2];
    // Si se encuentra la moto con el kilometraje introducido por el usuario
    // entonces cargar la data para imprimir en pantalla
    if ($isFind) {
      $prev = false;
      $next =  true;
      $htmlRowPrev = '';
      $htmlRowNext = '';
      $rowNext1 = rowPrevNext($csvData, $rowNumber, $next); // Obtiene número de fila siguiente más próxima al km ingresado
      $rowNext2 = rowPrevNext($csvData, $rowNext1, $next); // Obtiene número de fila siguiente más próxima a la fila anterior
      $rowDataNext1 = $csvData->getRow($rowNext1); // Obtiene la data de la fila 
      $rowDataNext2 = $csvData->getRow($rowNext2); // Obtiene la data de la fila
      $htmlRowNext .= rowDataMaintenanceHTML($rowDataNext1); // Obtiene el html de la fila
      $htmlRowNext .= rowDataMaintenanceHTML($rowDataNext2); // Obtiene el html de la fila
      if ($rowNumber >= 1) { // Valida que el km introducido no esté en las primeras 2 filas
        $rowPrev1 = rowPrevNext($csvData, $rowNumber, $prev);
        $rowDataPrev1 = $csvData->getRow($rowPrev1);
        $htmlPrev1 = rowDataMaintenanceHTML($rowDataPrev1);
        if ($rowNumber >= 2) {
          $rowPrev2 = rowPrevNext($csvData, $rowPrev1, $prev);
          $rowDataPrev2 = $csvData->getRow($rowPrev2);
          $htmlPrev2 = rowDataMaintenanceHTML($rowDataPrev2);
        }
        $htmlRowPrev .= $htmlPrev2;
        $htmlRowPrev .= $htmlPrev1;
      } else { // Si el km introducido está en las primeras 2 filas, entonces debe mostrar 2 datos siguientes más a los que ya mostraba
        $rowNext3 = rowPrevNext($csvData, $rowNext2, $next);
        $rowDataNext3 = $csvData->getRow($rowNext3);
        $rowNext4 = rowPrevNext($csvData, $rowNext3, $next);
        $rowDataNext4 = $csvData->getRow($rowNext4);
        $htmlRowNext .= rowDataMaintenanceHTML($rowDataNext3);
        $htmlRowNext .= rowDataMaintenanceHTML($rowDataNext4);
      }
      $rowData = $csvData->getRow($rowNumber);
      // Valida si el km introducido por el usuario tiene mantenimiento programado o no
      $htmlDataRow = $hasMaintenance ?
        rowDataMaintenanceHTML($rowData, true) :
        "<div class='dk__maintenance-row mdw__no-maintenance'>
          <div class='dk__maintenance-col'>$kilometer</div>
          <div class='dk__nomaintenance-col'><b>Sin mantenimiento</b></div>
        </div>";

      // Construlle el html para la tabla que se imprime en pantalla
      $htmlRowTable = $htmlRowPrev;
      $htmlRowTable .= $htmlDataRow;
      $htmlRowTable .= $htmlRowNext;

      $html .= "
          <div class='dk__container_moto-info'>
            <h5 class='dk__moto-info'>Marca: <span>$marca</span></h5>
            <h5 class='dk__moto-info'>Referencia: <span>$referencia</span></h5>
            <h5 class='dk__moto-info'>Modelo: <span>$modelo</span></h5>
            <h5 class='dk__moto-info'>Placa: <span>$placa</span></h5>
          </div>
        ";
      $html .= "<div class='mdw__div_maintenance-result'>";
      $html .= "
        <div class='dk__maintenance-header'>
          <div class='dk__maintenance-col mdw__kilometer'>Kilometraje</div>
          <div class='dk__maintenance-col mdw__maintenance'>Mantenimiento</div>
        </div>
      ";

      $html .= "<div class='dk__maintenance-body'>";
      $html .= $htmlRowTable;
      $html .= "</div>";
      $html .= "</div>";
    } else {
      $html = "<div class='dk__not-found'>Moto no encontrada en la base de datos.</div>";
    }

    wp_send_json_success($html);
    wp_die();
  }
}

/**
 * La marca de la moto se normaliza para eliminar acentos, 
 * y reemplazar los espacios por guión bajo
 */
function normalizeMarca($marca)
{
  // Convertir a minúsculas
  $marca = strtolower($marca);

  // Eliminar acentos y caracteres especiales
  $unwantedArray = [
    'á' => 'a',
    'é' => 'e',
    'í' => 'i',
    'ó' => 'o',
    'ú' => 'u',
    'à' => 'a',
    'è' => 'e',
    'ì' => 'i',
    'ò' => 'o',
    'ù' => 'u',
    'ä' => 'a',
    'ë' => 'e',
    'ï' => 'i',
    'ö' => 'o',
    'ü' => 'u',
    'â' => 'a',
    'ê' => 'e',
    'î' => 'i',
    'ô' => 'o',
    'û' => 'u',
    'ñ' => 'n',
    'ç' => 'c'
  ];
  $marca = strtr($marca, $unwantedArray);

  // Reemplazar espacios por guiones bajos
  $marca = str_replace(' ', '_', $marca);

  // Eliminar cualquier otro carácter no deseado (opcional)
  $marca = preg_replace('/[^a-z0-9_]/', '', $marca);

  return $marca;
}

/**
 * Realiza la búsqueda de la fila que coincida con la moto registrada por el usuario
 * y con el kilometraje introducido en el formulario de consulta.
 * Recibe como atributos el objeto con la data $csvData,
 * tambien los datos de la moto como referencia
 * y recibe el kilometraje introducido por el usuario.
 * 
 * La función retorna el número de la fila donde encontró la data, 
 * además de un booleano que indica que la data fue encontrada,
 * y un último booleano que indica que la data encontrada tiene un mantenimiento asignado
 */
function getCSVDataNumberRow($csvData, $referencia, $kilometer)
{
  $isFind = false;
  $hasMaintenance = false;
  $row = 0;
  foreach ($csvData->getData() as $data) {
    // validar que la data coincida con el kilometraje introducido
    if (is_moto($data, $referencia, $kilometer)) {
      $isFind = true;
      if ($data['Cambio']) {
        $hasMaintenance = true;
      }
      break;
    }
    $row++;
  }
  return [$row, $isFind, $hasMaintenance];
}

/**
 * Valida si se ha encontrado el kilometraje introducido por el usuario 
 * para la marca y referencia de la moto registrada por el usuario
 */
function is_moto($data, $referencia, $kilometer)
{
    // Convertir ambas referencias a minúsculas antes de comparar
    return (strtolower($data['REFERENCIA']) === strtolower($referencia)) && $data['KILOMETRAJE'] == $kilometer;
}

/**
 * Retorna el número de fila anterior o siguiente a la fila enviada. 
 */
function rowPrevNext($csvData, $rowNumber, $tipoPrevNext)
{
  if ($tipoPrevNext) {
    $rowNumber++;
    while (!$csvData->getRow($rowNumber)['Cambio']) {
      $rowNumber++;
    }
  } else {
    $rowNumber--;
    while (!$csvData->getRow($rowNumber)['Cambio']) {
      $rowNumber--;
    }
  }
  return $rowNumber;
}

/**
 * Retorna el html de la fila con la data de mantenimiento enviada
 */
function rowDataMaintenanceHTML($rowDataMaintenance, $isCurrent = false)
{
  $col = 0;
  $classCurrent = $isCurrent ? 'km-current' : '';
  $html = "<div class='dk__maintenance-row $classCurrent'>";
  foreach ($rowDataMaintenance as $cell) {
    // Convertir a UTF-8 si no lo está
    $cell = mb_convert_encoding($cell, 'UTF-8', 'auto');

    if ($col >= 4) {
      $html .= !empty($cell) ? "<div class='dk__maintenance-col'>" . $cell . "</div>" : null;
    }
    $col++;
  }
  $html .= "</div>";
  return $html;
}

/**
 * Función para redondear al múltiplo de 500 más cercano
 */
function roundToNearest500($value)
{
  return round($value / 500) * 500;
}
