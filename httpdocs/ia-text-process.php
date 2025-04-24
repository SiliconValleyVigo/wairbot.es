<?php

class TextProcess{

    public static function procesarTexto($texto, $card){
        $prompt = self::crearPrompt($texto, $card);

        $resultado = self::consultarIA($prompt);

        $respuesta = $resultado['respuesta'];

        $respuesta = self::procesarResultado($respuesta, $texto);

        $respuesta = [
            'respuesta' => $respuesta,
            'tokensUsados' => $resultado['tokensUsados']
        ];

        return $respuesta;
    }

    public static function consultaGeneral($texto, $card, $idioma){
        //En el contexto de la tarjeta de Trello llamada [nombre] con la descripción [descripción], responde esta consulta con una respuesta concisa de menos de 20 palabras:"
        $titulo = $card['titulo'];
        $descripcion = $card['descripcion'];

        //si el texto tiene más de 20 palabras, obtener las 20 últimas
        $palabras = explode(" ", $texto);
        $palabras = array_slice($palabras, -20);
        $texto = implode(" ", $palabras);

        $prompt = "Trello card: $titulo, $descripcion. Respond in <20 words: $texto, Language: $idioma. Avoid '$titulo'. Avoid 'Trello card'";
        
        $resultado = self::consultarIA($prompt);

        $respuesta = self::procesarResultado($resultado, $texto);

        return [
            'respuesta' => $respuesta,
            'tokensUsados' => $resultado['tokensUsados']
        ];
    }

    public static function generarTituloTrello($titulo, $descripcion, $idioma){
        $prompt = "Write the Title for a Trello card about $titulo, $descripcion in the language $idioma. Avoid expression 'this card', 'this Trello card', 'Title:', 'Description:' and similars";
        
        $resultado = self::consultarIA($prompt);

        return $resultado;
    }

    public static function generarDescripcionTrello($titulo, $descripcion, $idioma){
        $prompt = "Write the description for a Trello card about $titulo, $descripcion in the language $idioma. Avoid '$titulo'. Avoid expression 'this card', 'this Trello card', 'Title:', 'Description:' and similars";
        
        $resultado = self::consultarIA($prompt);

        return $resultado;
    }

    private static function crearPrompt($texto, $card){
        //"Auto-complete Trello card about $titulo, $descripcion: $texto, Language: [$idioma]. Avoid '$texto' statement. Max 2 words."

        $title = $card['titulo'];
        $description = $card['descripcion'];
        $language = $card['idioma'];

        //si el texto tiene más de 20 palabras, obtener las 20 últimas
        $palabras = explode(" ", $texto);
        $palabras = array_slice($palabras, -20);
        $text = implode(" ", $palabras);

        $prompt = "Complete the following sentence: '$title $description $text...'

        Response: /Complete with the expected continuation, taking into account the provided context/
        
        Language: $language.
        Important: DO NOT USE THE WORDS: '$title $description $text'.
        Maximum 3 words.
        Complete partial words.";

        return $prompt;
    }

    private static function consultarIA($prompt) {
        $apiKey = getenv('OPEN_AI_SECRET_KEY'); // Reemplaza 'TU_API_KEY' con tu clave API de OpenAI
        $model = 'gpt-3.5-turbo';
    
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ];
    
        // Mensajes de conversación
        $messages = [
            ['role' => 'user', 'content' => $prompt],
        ];
    
        $data = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 1, // Ajusta la temperatura
            'max_tokens' => 100, // Ajusta el número máximo de tokens
        ];
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
        $response = curl_exec($ch);
    
        if ($response === false) {
            echo 'Error en la solicitud cURL: ' . curl_error($ch);
        } else {
            $responseData = json_decode($response, true);

            //comprobar si existe choices, si no existe imprimir respuesta
            if(!array_key_exists('choices', $responseData)){
               var_dump($responseData);
            }
            $respuesta = $responseData['choices'][0]['message']['content'];

            $tokensUsados = $responseData['usage']['total_tokens'];
    
            return [
                'respuesta' => $respuesta,
                'tokensUsados' => $tokensUsados
            ];
        }
    
        curl_close($ch);
    }

    private static function procesarResultado($resultado, $texto){
        if ($resultado == null) {
            return "";
        }

        //si resultado es un array, obtener el valor de la key respuesta
        if(is_array($resultado)){
            $resultado = $resultado['respuesta'];
        }

        //eliminar los espacios en blanco del principio del resultado
        $resultado = ltrim($resultado);

        //eliminar comillas si están al principio o al final
        $resultado = trim($resultado, '"');

        //si el resultado comienza por ... eliminar los 3 puntos
        $resultado = trim($resultado, " .");

        //si la primera letra es ' eliminarla
        $resultado = trim($resultado, "'");

        //si la ulima letra es ' eliminarla
        $resultado = rtrim($resultado, "'");

        //si el primer caracter no es una letra o un número, eliminarlo
        $resultado = preg_replace('/^[^a-z0-9]+/i', '', $resultado);

        //si el último caracter no es una letra o un número, eliminarlo
        $resultado = preg_replace('/[^a-z0-9]+$/i', '', $resultado);


        //si texto está dentro del resultado, eliminar texto de resultado y todos los caracteres anteriores a texto
        $pos = strpos($resultado, $texto);
        if($pos !== false){
            $resultado = substr($resultado, $pos + strlen($texto));
        }

        //eliminar texto de resultado
        $resultado = str_replace($texto, "", $resultado);

        //obtener la primera palabra del resultado y la ultima palabra del texto
        $primeraPalabraResultado = explode(" ", $resultado)[0];
        $ultimaPalabraTexto = explode(" ", $texto)[count(explode(" ", $texto)) - 1];

        //si primeraPalabraResultado contiene ultimaPalabraTexto, eliminar ultimaPalabraTexto de resultado
        if(strpos($primeraPalabraResultado, $ultimaPalabraTexto) !== false){
            //modificar solo la primera palabra del resultado
            $resultado = substr($resultado, strlen($ultimaPalabraTexto));
        }else{
            //si el último caracter del texto no es un espacio, añadir un espacio al principio del resultado
            if(substr($texto, -1) != " "){
                $resultado = " " . $resultado;
            }
        }

        return $resultado;
    }
    
}

