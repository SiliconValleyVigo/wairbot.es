<?php

class getAllData{
    static public function getAll($usuario, $print = true){

        //si existe la key usuario en el array usuario se le asigna el valor de usuario
        if(array_key_exists('usuario', $usuario)){
            $usuario = $usuario['usuario'];
        }
        $rol = $usuario['rol'];

        if($rol == 'ADMINISTRADOR'){
            $conn = $_SESSION['db_connection'];
            $sql = "SELECT n_socio, nombre_comercial FROM usuarios WHERE estado = 'INSCRITO'";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $usuarios = $stmt->get_result();

            $usuarios = $usuarios->fetch_all(MYSQLI_ASSOC);

            //eliminar usuarios que n_socio o nombre_comercial sea null o vacio
            $usuarios = array_filter($usuarios, function($usuario){
                return !empty($usuario['n_socio']) && !empty($usuario['nombre_comercial']);
            });

            //poner usuarios en formato n_socio => nombre_comercial
            $usuarios = array_column($usuarios, 'nombre_comercial', 'n_socio');

            $data = [
                'usuarios' => $usuarios,
                'usuario' => $usuario,
            ];
        }else{
            $data = [
                'usuario' => $usuario,
            ];
        }
        
        if($print){
            $usuario = $data['usuario'];
            $data['usuario'] = $usuario;
            return [
                'status' => 'success',
                'message' => 'Datos obtenidos correctamente',
                'data' => $data
            ];
        }else{
            return $data;
        }
    }
}