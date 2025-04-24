<?php

/*
$sql = "CREATE TABLE selects (
            id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255),
            tabla VARCHAR(255),
            menu JSON,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
*/

class Selects {
    static public function getForNombreSelect($request){
        $conn = $_SESSION['db_connection'];
        $nombre = $request['nombre'];

        $sql = "SELECT * FROM selects WHERE nombre = '$nombre'";
        $result = $conn->query($sql);

        if($result->num_rows == 0){
            return [
                'status' => 'error',
                'message' => 'No se ha encontrado el select'
            ];
        }

        $select = $result->fetch_assoc();

        $select['menu'] = json_decode(urldecode($select['menu']), true);

        return [
            'status' => 'success',
            'message' => 'Select obtenido correctamente',
            'data' => $select
        ];
    }

    static public function update($request){
        $conn = $_SESSION['db_connection'];
        $data = $request['data'];
        $nombre = $data['nombre'];
        $menu = json_encode($data['menu']);

        $menu = urlencode($menu);

        if($_SESSION['user_rol'] != 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para realizar esta acción'
            ];
        }

        $sql = "UPDATE selects SET menu = '$menu' WHERE nombre = '$nombre'";
        $result = $conn->query($sql);

        if($result){
            return [
                'status' => 'success',
                'message' => 'Select actualizado correctamente'
            ];
        }else{
            return [
                'status' => 'error',
                'message' => 'Error al actualizar el select'
            ];
        }
    }
}
