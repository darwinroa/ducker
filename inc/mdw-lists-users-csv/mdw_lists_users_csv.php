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
        fputcsv($file, array('Nombre', 'Apellido', 'Genero', 'Fecha de nacimiento', 'Correo electronico', 'Telefono', 'Marca de Moto', 'Referencia', 'Modelo', 'Placa')); // Encabezado del archivo CSV
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
            // Obtiene la fecha de nacimiento del usuario
            $birth_day = get_user_meta($id, 'fecha_nacimiento', true);
            // Obtiene el género del usuario
            $gender = get_user_meta($id, 'genero', true);
            // Obtiene el número de celular del usuario
            $phone = get_user_meta($id, 'numero_telefono', true);
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
                // Obtiene el número de placa de la moto
                $placa = $moto['placa'];
                // Escribe los datos del usuario en el archivo CSV
                fputcsv($file, array($first_name, $last_name, $gender, $birth_day, $email, $phone, $marca, $referencia, $modelo, $placa));
                // Agrega los datos del usuario a la tabla HTML
                $htmlTBody .= "
                    <tr>
                        <td>$first_name</td>
                        <td>$last_name</td>
                        <td>$gender</td>
                        <td>$birth_day</td>
                        <td>$email</td>
                        <td>$phone</td>
                        <td>$marca</td>
                        <td>$referencia</td>
                        <td>$modelo</td>
                        <td>$placa</td>
                    </tr>
                ";
            }
        }
        // Cierra el archivo CSV
        fclose($file);
        // Muestra un enlace para descargar el archivo CSV
        $html .= '<a href="' . get_stylesheet_directory_uri() . '/inc/mdw-lists-users-csv/users.csv" download class="mdw_download">Descargar lista de usuarios</a>';
        $html .= "
            <div class='mdw__table_list-motos'>
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Género</th>
                            <th>Fecha Nacimiento</th>
                            <th>Correo electrónico</th>
                            <th>Teléfono</th>
                            <th>Marca de Moto</th>
                            <th>Referencia</th>
                            <th>Modelo</th>
                            <th>Placa</th>
                        </tr>
                    </thead>
                    <tbody>
                        $htmlTBody
                    </tbody>
                </table>
            </div>
        ";        
    } else {
        // Si no hay usuario conectado, muestra un mensaje de error
        $html .= '<div class="msj-error">Debes iniciar sesión para acceder a esta función.</div>';
    }

    return $html;
}

?>
