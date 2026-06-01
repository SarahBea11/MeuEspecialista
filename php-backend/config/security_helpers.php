<?php
require_once __DIR__ . '/app_config.php';

/**
 * Valida o formato e dígito verificador do CPF
 *
 * @param string $cpf
 * @return bool
 */
function validarCPF($cpf) {
    // Extrai apenas os números
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    // Verifica se possui 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }

    // Verifica se foi informada uma sequência de dígitos repetidos (ex: 111.111.111-11)
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    // Calcula os dígitos verificadores para verificar se o CPF é válido
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }

    return true;
}

/**
 * Valida o formato do CRM (Ex: 123456, 12345/SP, 123456-SP, 123456SP)
 * Deve conter de 4 a 10 dígitos, seguido opcionalmente de hífen, barra ou espaço e a sigla do estado (2 letras).
 *
 * @param string $crm
 * @return bool
 */
function validarCRM($crm) {
    $crmClean = trim($crm);
    if (empty($crmClean)) {
        return false;
    }
    // Regex aceita dígitos seguidos opcionalmente por UF com separador opcional
    return (bool)preg_match('#^\d{4,10}([-/ ]?[A-Z]{2})?$#i', $crmClean);
}

/**
 * Criptografa dados usando AES-256-CBC
 *
 * @param string $plaintext
 * @param bool $deterministic Se true, usa um IV estático permitindo busca e constraints UNIQUE no banco.
 * @return string|null
 */
function encryptData($plaintext, $deterministic = false) {
    if ($plaintext === null || $plaintext === '') {
        return null;
    }

    $key = ENCRYPTION_KEY;
    $method = ENCRYPTION_METHOD;
    $ivLength = openssl_cipher_iv_length($method);
    
    if ($deterministic) {
        $iv = str_repeat("\0", $ivLength);
    } else {
        $iv = openssl_random_pseudo_bytes($ivLength);
    }
    
    $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
    if ($ciphertext === false) {
        return null;
    }

    if ($deterministic) {
        return base64_encode($ciphertext);
    }

    // Retorna IV concatenado com o texto cifrado, codificado em base64
    return base64_encode($iv . $ciphertext);
}

/**
 * Descriptografa dados usando AES-256-CBC
 *
 * @param string $ciphertextBase64
 * @param bool $deterministic Se true, descriptografa assumindo IV estático.
 * @return string|null
 */
function decryptData($ciphertextBase64, $deterministic = false) {
    if ($ciphertextBase64 === null || $ciphertextBase64 === '') {
        return null;
    }

    $key = ENCRYPTION_KEY;
    $method = ENCRYPTION_METHOD;
    $ivLength = openssl_cipher_iv_length($method);
    
    if ($deterministic) {
        $iv = str_repeat("\0", $ivLength);
        $ciphertext = base64_decode($ciphertextBase64);
        if ($ciphertext === false) {
            return $ciphertextBase64;
        }
        $decrypted = openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
    } else {
        $data = base64_decode($ciphertextBase64);
        if ($data === false || strlen($data) <= $ivLength) {
            return $ciphertextBase64; // Retorna original se não puder decodificar
        }
        $iv = substr($data, 0, $ivLength);
        $ciphertext = substr($data, $ivLength);
        $decrypted = openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
    }

    if ($decrypted === false) {
        return $ciphertextBase64; // Retorna original se falhar a descriptografia
    }

    return $decrypted;
}
