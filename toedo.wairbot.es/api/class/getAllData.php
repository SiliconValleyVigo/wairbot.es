<?php

class getAllData{
    static public function getAll($usuario, $print = true){

        //si existe la key usuario en el array usuario se le asigna el valor de usuario
        if(array_key_exists('usuario', $usuario)){
            $usuario = $usuario['usuario'];
        }
        $rol = $usuario['rol'];

        if($rol == 'ADMINISTRADOR'){
            $clientesYAgentesYTecnicos = Usuarios::getAll($usuario);

            $nombresDeMaquinas = Maquinas::getAll($usuario);
            $nombresDeAgentes = $clientesYAgentesYTecnicos['agentes'];
            $nombresDeTecnicos = $clientesYAgentesYTecnicos['tecnicos'];
            $nombresDeClientes = $clientesYAgentesYTecnicos['clientes'];

            $tiposDePartes = Partes::getTipos();
            
            $data = [
                'usuario' => $usuario,
                'nombresDeClientes' => $nombresDeClientes,
                'nombresDeMaquinas' => $nombresDeMaquinas,
                'nombresDeAgentes' => $nombresDeAgentes,
                'nombresDeTecnicos' => $nombresDeTecnicos,
                'tiposDePartes' => $tiposDePartes
            ];
        }else if($rol == 'AGENTE'){
            $clientesYTecnicos = Usuarios::getAll($usuario);
            $nombresDeClientes = $clientesYTecnicos['clientes'];
            $nombresDeTecnicos = $clientesYTecnicos['tecnicos'];

            $nombresDeMaquinas = Maquinas::getAll($usuario);

            $data = [
                'usuario' => $usuario,
                'nombresDeClientes' => $nombresDeClientes,
                'nombresDeTecnicos' => $nombresDeTecnicos,
                'nombresDeMaquinas' => $nombresDeMaquinas
            ];
        }else if($rol == 'TECNICO'){
            $partes = Partes::getPartesByTecnicoId();
            
            $data = [
                'usuario' => $usuario,
                'partes' => $partes,
            ];
        }else if($rol == 'CLIENTE'){
            $maquinas = Maquinas::getByClienteId();

            $data = [
                'usuario' => $usuario,
                'maquinas' => $maquinas,
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