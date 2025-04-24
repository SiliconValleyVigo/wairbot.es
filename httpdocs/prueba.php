<?php

$url = "http://127.0.0.1:11434/api/generate";

$headers = [
    'Content-Type: application/json',
];

$conversation_history = [];

function generate_response($prompt) {
    global $url, $headers, $conversation_history;

    $conversation_history[] = $prompt;

    $full_prompt = implode("\n", $conversation_history);

    $data = [
        "model" => "openchat",
        "stream" => false,
        "prompt" => $full_prompt,
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if ($response !== false) {
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code == 200) {
            // Procesar la respuesta normalmente
            $data = json_decode($response, true);
            $actual_response = $data["response"];
            $conversation_history[] = $actual_response;
            return $actual_response;
        } else {
            echo "Error: HTTP " . $http_code;
            // Manejar otros códigos de respuesta según sea necesario
        }
    } else {
        echo "Error: " . curl_error($ch);
        return null;
    }

    curl_close($ch);
}

// Ejemplo de uso
echo generate_response("Completa esta frase con menos de 6 palabras incluyendo la ultima palabra si está incompleta en el contexto de hacer una card para trello: Enviar los inf"); // Reemplaza con tu prompt deseado
