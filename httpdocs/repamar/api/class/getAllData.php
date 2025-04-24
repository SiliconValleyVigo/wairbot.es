<?php

class getAllData{
    static public function getAll($usuario, $print = true){

        //si existe la key usuario en el array usuario se le asigna el valor de usuario
        if(array_key_exists('usuario', $usuario)){
            $usuario = $usuario['usuario'];
        }
        $rol = $usuario['rol'];

        Notificaciones::create();

        if($rol == 'ADMINISTRADOR'){
            $clientesYAgentesYTecnicos = Usuarios::getAll($usuario);

            $nombresDeMotores = Motores::getAll($usuario);
            $nombresDeAgentes = $clientesYAgentesYTecnicos['agentes'];
            $nombresDeTecnicos = $clientesYAgentesYTecnicos['tecnicos'];
            $nombresDeClientes = $clientesYAgentesYTecnicos['clientes'];

            $solicitudes = Solicitudes::getAll($usuario);
            
            $data = [
                'usuario' => $usuario,
                'nombresDeClientes' => $nombresDeClientes,
                'nombresDeMotores' => $nombresDeMotores,
                'nombresDeAgentes' => $nombresDeAgentes,
                'nombresDeTecnicos' => $nombresDeTecnicos,
                'solicitudes' => $solicitudes,
            ];
        }else if($rol == 'AGENTE'){
            $clientesYTecnicos = Usuarios::getAll($usuario);
            $nombresDeClientes = $clientesYTecnicos['clientes'];
            $nombresDeTecnicos = $clientesYTecnicos['tecnicos'];

            $nombresDeMotores = Motores::getAll($usuario);

            $data = [
                'usuario' => $usuario,
                'nombresDeClientes' => $nombresDeClientes,
                'nombresDeTecnicos' => $nombresDeTecnicos,
                'nombresDeMotores' => $nombresDeMotores
            ];
        }else if($rol == 'TECNICO'){
            $partes = Partes::getPartesByTecnicoId();
            
            $data = [
                'usuario' => $usuario,
                'partes' => $partes,
            ];
        }else if($rol == 'CLIENTE'){
            $motores = Motores::getByClienteId();
            $notificaciones = Notificaciones:: getByClienteId();
            $publicidad = Publicidad::getAll();

            $data = [
                'usuario' => $usuario,
                'motores' => $motores,
                'notificaciones' => $notificaciones,
                'publicidad' => $publicidad,
            ];
        }else{
            return json_encode([
                'status' => 'error',
                'message' => 'Rol no válido'
            ]);
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