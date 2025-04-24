<?php

class ToolsWairbot{
    static public function deleteBot($adminPhone){
        $conn = $_SESSION['db_connection'];
        //tablas que su nombre terminan en _$adminPhone
        //obtener todos los nombres de las tablas
        $sql = "SHOW TABLES";
        $result = $conn->query($sql);
        $tablas = $result->fetch_all(MYSQLI_ASSOC);

        //eliminar todas las tablas que su nombre termina en _$adminPhone
        foreach($tablas as $tabla){
            $nombreTabla = $tabla['Tables_in_wairbot_datos'];
            $nombreTabla = explode('_', $nombreTabla);
            $nombreTabla = $nombreTabla[count($nombreTabla) - 1];
            if($nombreTabla == $adminPhone){
                $sql = "DROP TABLE " . $tabla['Tables_in_wairbot_datos'];
                $conn->query($sql);
            }
        }
        
        //admin_bot -> AD_numero_de_telefono_del_bot__tel
        $sql = "DELETE FROM admin_bot WHERE AD_numero_de_telefono_del_bot__tel = '$adminPhone'";
        $conn->query($sql);

        //forms -> admin_phone
        $sql = "DELETE FROM forms WHERE admin_phone = '$adminPhone'";
        $conn->query($sql);

        //sesion_bot -> SE_admin_telefono
        $sql = "DELETE FROM sesion_bot WHERE SE_admin_telefono = '$adminPhone'";
        $conn->query($sql);

        //users -> admin_phone
        $sql = "DELETE FROM users WHERE admin_phone = '$adminPhone'";
        $conn->query($sql);

        //user_bot -> US_conversacion_activa
        $sql = "DELETE FROM user_bot WHERE US_conversacion_activa = '$adminPhone'";
        $conn->query($sql);
    }

    static public function createbot($data){
        $datos = [
            "key" => "",
            "text" => "user-botComunidades",
            "user" => $data["user"],
            "password" => $data["password"],
            "telefonoAdministrador" => $data["telefonoAdministrador"],
            "codigoBot" => $data["codigoBot"],
            "prefix" => $data["prefix"],
            "lang" => $data["lang"]
        ];
    
        $options = [
            CURLOPT_URL => $_SESSION['ulr_api'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($datos),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ]
        ];
    
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
    }
}