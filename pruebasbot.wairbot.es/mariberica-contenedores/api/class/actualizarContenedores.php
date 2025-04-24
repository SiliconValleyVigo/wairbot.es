<?php

class ActualizarContenedores{

    private static function getContainerStatusSummaryFromAPI($bookingCode) {
        // URL de la API con el código de booking
        $url = "https://ovhweportalapim.azure-api.net/dpo/trackandtrace/v2.2/events?carrierBookingReference=" . urlencode($bookingCode);

        // Configuración de la solicitud cURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Ocp-Apim-Subscription-Key: <tu_clave_api_aquí>', // Sustituir con tu clave de API
            'Accept: application/json'
        ]);

        // Ejecutar la solicitud
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        // Verificar el código de respuesta
        if ($httpCode !== 200) {
            return [
                'error' => "Error al obtener datos de la API. Código HTTP: $httpCode",
                'response' => $response
            ];
        }

        // Procesar el JSON
        $events = json_decode($response, true);
        if ($events === null) {
            return ['error' => 'Error al decodificar el JSON de la respuesta'];
        }

        // Inicializar los datos del resumen
        $summary = [
            'estimated' => [],
            'currentStatus' => []
        ];

        // Inicializar los datos estructurados
        $structuredData = [
            'containerId' => null,
            'carrierName' => null,
            'carrierId' => null,
            'shipmentDate' => 'pendiente',
            'summary' => ''
        ];

        // Recorrer los eventos
        foreach ($events as $event) {
            $classifierCode = $event['eventClassifierCode'] ?? '';
            $description = $event['description'] ?? '';
            $location = $event['transportCall']['unLocationCode'] ?? ($event['eventLocation']['unLocationCode'] ?? '');
            $eventDateTime = $event['eventDateTime'] ?? '';
            $equipmentEventTypeCode = $event['equipmentEventTypeCode'] ?? '';
            $transportEventTypeCode = $event['transportEventTypeCode'] ?? '';
            $equipmentReference = $event['equipmentReference'] ?? null;
            $vesselName = $event['transportCall']['vessel']['vesselName'] ?? null;
            $vesselOperatorCarrierCode = $event['transportCall']['vessel']['vesselOperatorCarrierCode'] ?? null;

            // Identificar el ID del contenedor, nombre de la naviera y código de la naviera
            if ($structuredData['containerId'] === null && $equipmentReference !== null) {
                $structuredData['containerId'] = $equipmentReference;
            }

            if ($structuredData['carrierName'] === null && $vesselName !== null) {
                $structuredData['carrierName'] = $vesselName;
            }

            if ($structuredData['carrierId'] === null && $vesselOperatorCarrierCode !== null) {
                $structuredData['carrierId'] = $vesselOperatorCarrierCode;
            }

            // Identificar la fecha de embarque si existe
            if ($equipmentEventTypeCode === 'LOAD' && $structuredData['shipmentDate'] === 'pendiente') {
                $structuredData['shipmentDate'] = $eventDateTime;
            }

            // Identificar eventos estimados (EST)
            if ($classifierCode === 'EST') {
                $summary['estimated'][] = [
                    'description' => $description,
                    'location' => $location,
                    'eventDateTime' => $eventDateTime
                ];
            }

            // Identificar estados actuales (ACT) relacionados con el proceso
            if ($classifierCode === 'ACT') {
                if ($equipmentEventTypeCode === 'LOAD') {
                    $summary['currentStatus'][] = [
                        'status' => 'Embarcado',
                        'description' => $description,
                        'location' => $location,
                        'eventDateTime' => $eventDateTime
                    ];
                } elseif ($transportEventTypeCode === 'DEPA') {
                    $summary['currentStatus'][] = [
                        'status' => 'En salida',
                        'description' => $description,
                        'location' => $location,
                        'eventDateTime' => $eventDateTime
                    ];
                } elseif ($equipmentEventTypeCode === 'DISC') {
                    $summary['currentStatus'][] = [
                        'status' => 'En destino',
                        'description' => $description,
                        'location' => $location,
                        'eventDateTime' => $eventDateTime
                    ];
                }
            }
        }

        // Generar el resumen como texto
        $output = "\n**Estado estimado (EST):**\n";
        foreach ($summary['estimated'] as $est) {
            $output .= "- {$est['description']} en {$est['location']} (Fecha estimada: {$est['eventDateTime']})\n";
        }

        $output .= "\n**Estado actual del contenedor:**\n";
        foreach ($summary['currentStatus'] as $status) {
            $output .= "- {$status['status']}: {$status['description']} en {$status['location']} (Fecha: {$status['eventDateTime']})\n";
        }

        // Agregar el resumen al array estructurado
        $structuredData['summary'] = $output;
        $structuredData['status'] = $summary;

        return $structuredData;
    }

    /*
    TABLAS
    proveedores: id, nombre, referencia, notas(textarea), timestamp

    compras: id, fecha, id_proveedor, n_compra, seguro(SI, NO, PENDIENTE), lc(textarea), etiquetas(textarea), documentos(textarea), referencia, notas(textarea), timestamp

    contenedores: id, id_compra(n_compra), booking, identificador_contenedor, naviera, identificador_naviera, fecha_embarque, fecha_llegada_estimada, estado_de_embarque, informacion, referencia, notas(textarea), timestamp
    
    productos_de_contenedor: id, id_contenedor(identificador_contenedor + naviera), id_producto(descripcion), divisa, cantidad, referencia, notas(textarea), timestamp
    
    productos: id, codigo, descripcion, presentacion, precio, referencia, notas(textarea), timestamp
    */

    //Actualizar las tabla de contenedores que no tengan el estado de embarque como "En destino"



}