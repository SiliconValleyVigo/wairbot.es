<?php

/*
usuarios: rol, imagen, nombre, referencia, direccion, telefono_contacto, telefono_whatsapp, email, password, id_agente
motores: imagen, marca, modelo, codigo, observaciones, id_cliente, id_agente, fecha_instalacion, fecha_ulitma_revision, fecha_proxima_revision
partes: fecha, id_motor, tipo, notas, estado, id_tecnico
tareas: id_parte, descripcion, tipo, referencia
documentos: tipo, id_parte, url
solicitudes: id_motor, id_agente, estado
notificaciones: id_cliente, texto
publicidad: url, imagen, nombre, precio, estado
emails: email, texto, fecha
*/

class Publicidad{
    static public function create($request){
        $conn = $_SESSION['db_connection'];
        $url = $request['url'];
        $rol = $_SESSION['user_rol'];

        //si el rol no es ADMINISTRADOR, no puede crear una publicidad
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para crear una publicidad'
            ];
        }

        // Crea un nuevo objeto DOM
        $dom = new DOMDocument;

        // Desactiva los errores de libxml y permite al usuario capturar los errores de manipulación del DOM
        libxml_use_internal_errors(true);

        // Carga el HTML de la URL
        $dom->loadHTMLFile($url);

        // Crea un nuevo objeto DOMXPath
        $xpath = new DOMXPath($dom);

        // Obtiene el primer valor del atributo data-zoom-image
        $dataZoomImageNodes = $xpath->query('//*[@data-zoom-image]');
        if ($dataZoomImageNodes->length > 0) {
            $firstNode = $dataZoomImageNodes->item(0);
            if ($firstNode instanceof DOMElement) {
                $dataZoomImage = $firstNode->getAttribute('data-zoom-image');
            } else {
                echo "El primer nodo no es un DOMElement.\n";
            }
        } else {
            echo "No se encontró el atributo data-zoom-image.\n";
        }

        // Obtiene el primer elemento con la clase page-heading
        $pageHeadingNodes = $xpath->query('//*[contains(@class, "page-heading")]');
        $pageHeading = $pageHeadingNodes->item(0)->nodeValue;

        // Obtiene el primer elemento con la clase current-price-value
        $currentPriceValueNodes = $xpath->query('//*[contains(@class, "current-price-value")]');
        $currentPriceValue = $currentPriceValueNodes->item(0)->nodeValue;

        //eliminar de currentPriceValue cualquier caracter que no sea un número una coma o un punto
        $currentPriceValue = preg_replace('/[^0-9,.]/', '', $currentPriceValue);
        $currentPriceValue = $currentPriceValue . " €";

        //insertar la publicidad en la base de datos
        $sql = "INSERT INTO publicidad (url, imagen, nombre, precio, estado) VALUES ('$url', '$dataZoomImage', '$pageHeading', '$currentPriceValue', 'ACTIVO')";
        $conn->query($sql);

        return [
            'status' => 'success',
            'message' => 'Publicidad creada correctamente'
        ];
    }

    static public function update($request){
        $conn = $_SESSION['db_connection'];
        $id_publicidad = $request['id_publicidad'];
        $estado = $request['estado'];
        $rol = $_SESSION['user_rol'];

        //si el rol no es ADMINISTRADOR, no puede actualizar una publicidad
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para actualizar una publicidad'
            ];
        }

        $sql = "UPDATE publicidad SET estado = '$estado' WHERE id = $id_publicidad";
        $conn->query($sql);

        return [
            'status' => 'success',
            'message' => 'Publicidad actualizada correctamente'
        ];
    }

    static public function delete($request){
        $conn = $_SESSION['db_connection'];
        $id_publicidad = $request['id'];
        $rol = $_SESSION['user_rol'];

        //si el rol no es ADMINISTRADOR, no puede eliminar una publicidad
        if($rol !== 'ADMINISTRADOR'){
            return [
                'status' => 'error',
                'message' => 'No tienes permisos para eliminar una publicidad'
            ];
        }

        $sql = "DELETE FROM publicidad WHERE id = $id_publicidad";
        $conn->query($sql);

        return [
            'status' => 'success',
            'message' => 'Publicidad eliminada correctamente'
        ];
    }

    //obtener todas las publicidades
    static public function getAll(){
        $conn = $_SESSION['db_connection'];

        $publicidades = [];
        $sql = "SELECT * FROM publicidad";

        $result = $conn->query($sql);

        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $publicidades[] = $row;
            }
        }

        return [
            'status' => 'success',
            'message' => 'Publicidades obtenidas correctamente',
            'data' => $publicidades
        ];
    }
}