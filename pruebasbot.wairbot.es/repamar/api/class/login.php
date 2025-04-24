<?php

require_once 'vendor/autoload.php';
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class Login{
    static public function initLogin($requests){
        $user = $requests['user'];
        $password = $requests['password'];

        //obtener usuario de la tabla usuarios
        $conn = $_SESSION['db_connection'];
        $sql = "SELECT * FROM usuarios WHERE email = '" . $user . "'";
        $result = $conn->query($sql);
        $usuario = $result->fetch_assoc();

        if(!$usuario || count($usuario) == 0){
            return json_encode([
                'status' => 'error',
                'message' => 'El usuario no existe'
            ]);
        }

        $password = $requests['password'];

        $contrasenaValida = false;

        if($usuario['password'] == $password){
            $contrasenaValida = true;
        }

        if($contrasenaValida){
            $config = Configuration::forSymmetricSigner(
                new Sha256(),
                \Lcobucci\JWT\Signer\Key\InMemory::plainText('3BsdWdpbl9QcnVlYmFjb2RpZ29cIixcI')
            );

            $token = $config->builder()
                            ->withClaim('nombre', $usuario['nombre'])
                            ->withClaim('email', $usuario['email'])
                            ->withClaim('rol', $usuario['rol'])
                            ->getToken($config->signer(), $config->signingKey()); // Retrieves the generated token

            $jwt = $token->toString();

            $allData = getAllData::getAll($usuario, false);

            return json_encode([
                'status' => 'success',
                'message' => 'Usuario y contraseña válidos',
                'token' => $jwt,
                'data' => $allData
            ], JSON_UNESCAPED_UNICODE);
            
        }else{
            return json_encode([
                'status' => 'error',
                'message' => 'Usuario o contraseña inválidos'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    static public function verifyToken($token, $usuario){
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            \Lcobucci\JWT\Signer\Key\InMemory::plainText('3BsdWdpbl9QcnVlYmjb2RpZ29ixcI')
        );
    
        try {
            $token = $config->parser()->parse((string) $token); // Parses from a string
        } catch (\Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Token no válido'
            ]);
            return false;
        }
    
        if ($token instanceof \Lcobucci\JWT\Token\Plain) {
            if ($token->claims()->get('nombre') == $usuario['nombre'] && 
                $token->claims()->get('email') == $usuario['email'] &&
                $token->claims()->get('rol') == $usuario['rol']
            ) {
                return json_encode([
                    'status' => 'success',
                    'message' => 'Token válido'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Token no válido'
                ]);
                return false;
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Token no válido'
            ]);
            return false;
        }
    }
}