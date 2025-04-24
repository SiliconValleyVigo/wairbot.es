<?php

class Utilidades {
    public static function obtenerElemenosDeUnaColumnaSinRepetir($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::obtenerTabla($request['tabla']);
        $columna = $request['columna'];
        
        $sql = "SELECT DISTINCT $columna FROM $tabla";
        $result = $conn->query($sql);
        $data = [];
        while($row = $result->fetch_assoc()){
            $data[] = $row[$columna];
        }
        return $data;
    }

    /*
    Actualizar un registro de una tabla
    tabla: tabla,
        columna: columna,
        id: idRegistro,
        value: value
    */
    public static function actualizarRegistro($request){
        $conn = $_SESSION['db_connection'];
        $tabla = self::obtenerTabla($request['tabla']);
        $columna = $request['columna'];
        $id = $request['id'];
        $value = $request['value'];

        $sql = "UPDATE $tabla SET $columna = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $value, $id);
        $stmt->execute();

        return [
            'status' => 'success',
            'message' => 'Registro actualizado correctamente'
        ];
    }

    private static function obtenerTabla($tablaPropiedades){
        //$tablas = ['acciones', 'acciones_periodicas', 'beneficiarios', 'beneficiarios_baja', 'coordinadores', 'voluntarios', 'voluntarios_baja'];
        if($tablaPropiedades == 'acciones' || $tablaPropiedades == 'acciones_periodicas'){
            return 'acciones';
        }

        if($tablaPropiedades == 'beneficiarios' || $tablaPropiedades == 'beneficiarios_baja'){
            return 'beneficiarios';
        }

        if($tablaPropiedades == 'coordinadores' || $tablaPropiedades == 'voluntarios' || $tablaPropiedades == 'voluntarios_baja'){
            return 'usuarios';
        }
    }
}
