<?php
add_shortcode('mdw_lists_users_csv', 'mdw_lists_users_csv_function');

function mdw_lists_users_csv_function()
{
    $html = '';
    $htmlTBody = '';
    // Verifica si el usuario está conectado
    if (is_user_logged_in()) {        
        // Obtiene todos los usuarios
        $users = get_users();
        // Crea un archivo CSV
        $file = fopen('wp-content/themes/hub-child/inc/mdw-lists-users-csv/users.csv', 'w');
        // Encabezado del archivo CSV
        fputcsv($file, array('Nombre', 'Apellido', 'Correo electrónico', 'Marca de Moto', 'Referencia', 'Modelo')); // Encabezado del archivo CSV
        // Recorre todos los usuarios
        foreach ($users as $user) {
            // Obtiene el ID del usuario
            $id = $user->ID;
            // Obtiene el nombre del usuario
            $first_name = $user->first_name;
            // Obtiene el apellido del usuario
            $last_name = $user->last_name;
            // Obtiene el correo electrónico del usuario
            $email = $user->user_email;
            // Obtener los valores ya registrados de "motos" en el usuario
            $motosRegistradas = get_user_meta($id, 'motos', true);
            // Asegurarnos de que $motosRegistradas sea un array
            if (!is_array($motosRegistradas)) {
                // Si no es un array (puede ser una cadena serializada o vacío), lo inicializamos como array vacío
                $motosRegistradas = [];
            }
            // Recorrer las motos registradas
            foreach ($motosRegistradas as $moto) {
                // Obtiene la marca de la moto
                $marca = $moto['marca_de_moto'];
                // Obtiene la referencia de la moto
                $referencia = $moto['referencia'];
                // Obtiene el modelo de la moto
                $modelo = $moto['modelo'];
                // Escribe los datos del usuario en el archivo CSV
                fputcsv($file, array($first_name, $last_name, $email, $marca, $referencia, $modelo));
                // Agrega los datos del usuario a la tabla HTML
                $htmlTBody .= "
                    <tr>
                        <td>$first_name</td>
                        <td>$last_name</td>
                        <td>$email</td>
                        <td>$marca</td>
                        <td>$referencia</td>
                        <td>$modelo</td>
                    </tr>
                ";
            }
        }
        // Cierra el archivo CSV
        fclose($file);
        // Muestra un enlace para descargar el archivo CSV
        $html .= '<a href="' . get_stylesheet_directory_uri() . '/inc/mdw-lists-users-csv/users.csv" download class="mdw_download">Descargar lista de usuarios</a>';
        $html .= "
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Correo electrónico</th>
                        <th>Marca de Moto</th>
                        <th>Referencia</th>
                        <th>Modelo</th>
                    </tr>
                </thead>
                <tbody>
                    $htmlTBody
                </tbody>
            </table>
        ";        
    } else {
        // Si no hay usuario conectado, muestra un mensaje de error
        $html .= '<div class="msj-error">Debes iniciar sesión para acceder a esta función.</div>';
    }

    return $html;
}

?>
