<?php

class getAllData{
    static public function getAll($usuario, $print = true){

        //si existe la key usuario en el array usuario se le asigna el valor de usuario
        if(array_key_exists('usuario', $usuario)){
            $usuario = $usuario['usuario'];
        }
        $rol = $usuario['rol'];

        if($rol == 'ADMINISTRADOR'){
            $data = [
                'usuario' => $usuario,
            ];
            
        }else if($rol == 'AGENTE'){
            $comunidades = Comunidades::getArrayCodigosComunidades();
            $data = [
                'usuario' => $usuario,
                'comunidades' => $comunidades
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
