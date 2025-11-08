<?php

/**
 * Faz uma requisição HTTP segura usando cURL.
 * @param string 
 * @return string|false A resposta da API ou false em caso de erro.
 */
function http_request($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout de 5 segundos
    curl_setopt($ch, CURLOPT_USERAGENT, 'AjudaJaaApp/1.0 (contato@seusite.com)');

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode == 200) {
        return $response;
    } else {
        error_log("Falha na requisição HTTP para {$url}. Código: {$httpcode}");
        return false;
    }
}

/**
 * Obtém dados de geolocalização (Cidade, Estado, Lat, Lon) a partir de um CEP.
 * Usa ViaCEP para obter o endereço e Nominatim para obter as coordenadas.
 *
 * @param string $cep CEP com 8 dígitos (apenas números).
 * @return array|null Um array com [cidade, estado, latitude, longitude] ou null se falhar.
 */
function getGeoDataFromCEP($cep_limpo) {
    if (strlen($cep_limpo) !== 8) {
        return null;
    }

    // --- ETAPA A: Chamar ViaCEP para obter Cidade/Estado/Rua ---
    $url_viacep = "https://viacep.com.br/ws/{$cep_limpo}/json/";
    $response_viacep = http_request($url_viacep);

    if (!$response_viacep) {
        error_log("Falha ao conectar com ViaCEP para CEP: {$cep_limpo}");
        return null;
    }

    $data_viacep = json_decode($response_viacep, true);

    if (isset($data_viacep['erro'])) {
        error_log("ViaCEP retornou erro (CEP não encontrado): {$cep_limpo}");
        return null; // CEP não existe
    }

    $cidade = $data_viacep['localidade'] ?? null;
    $estado = $data_viacep['uf'] ?? null;
    $logradouro = $data_viacep['logradouro'] ?? null;
    
    if (empty($cidade) || empty($estado)) {
         error_log("ViaCEP não retornou cidade/estado para: {$cep_limpo}");
         return null; // Dados incompletos
    }

    // --- ETAPA B: Chamar Nominatim (OpenStreetMap) para obter Lat/Lon ---
    // Montamos uma query estruturada para o Nominatim ser mais preciso
    $query_params = http_build_query([
        'format' => 'jsonv2', // Formato JSON mais recente
        'limit' => 1,          // Queremos apenas o melhor resultado
        'street' => $logradouro,
        'city' => $cidade,
        'state' => $estado,
        'postalcode' => $cep_limpo,
        'country' => 'Brazil'
    ]);
    
    $url_nominatim = "https://nominatim.openstreetmap.org/search?" . $query_params;
    
    $response_nominatim = http_request($url_nominatim);

    if (!$response_nominatim) {
        error_log("Falha ao conectar com Nominatim para CEP: {$cep_limpo}");
        // Retorna pelo menos cidade/estado se Nominatim falhar
        return ['cidade' => $cidade, 'estado' => $estado, 'latitude' => null, 'longitude' => null];
    }

    $data_nominatim = json_decode($response_nominatim, true);

    if (empty($data_nominatim[0]['lat']) || empty($data_nominatim[0]['lon'])) {
        error_log("Nominatim não retornou lat/lon para: {$cep_limpo}");
        // Retorna pelo menos cidade/estado se Nominatim não encontrar coords
        return ['cidade' => $cidade, 'estado' => $estado, 'latitude' => null, 'longitude' => null];
    }
    
    $latitude = $data_nominatim[0]['lat'];
    $longitude = $data_nominatim[0]['lon'];

    // --- SUCESSO ---
    return [
        'cidade' => $cidade,
        'estado' => $estado,
        'latitude' => $latitude,
        'longitude' => $longitude
    ];
}
?>