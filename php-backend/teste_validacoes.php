<?php
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/config/security_helpers.php';

echo "=== INICIANDO TESTES DE SEGURANÇA E VALIDAÇÕES ===\n\n";

// 1. Testes de CPF
echo "1. Testes de CPF:\n";
$cpfs = [
    ['cpf' => '111.111.111-11', 'esperado' => false], // repetidos
    ['cpf' => '12345678909', 'esperado' => true],    // cpf matematicamente valido
    ['cpf' => '000.000.000-00', 'esperado' => false], // repetidos
    ['cpf' => '52998224725', 'esperado' => true],    // cpf matematicamente valido
    ['cpf' => '95747683050', 'esperado' => false],   // cpf invalido
    ['cpf' => '11122233396', 'esperado' => true]     // cpf matematicamente valido
];

foreach ($cpfs as $item) {
    $cpf = $item['cpf'];
    $esperado = $item['esperado'];
    $valido = validarCPF($cpf);
    $res = $valido ? "Válido" : "Inválido";
    $status = ($valido === $esperado) ? "PASSED" : "FAILED";
    echo "  CPF: $cpf -> Resultado: $res (Esperado: " . ($esperado ? "Válido" : "Inválido") . ") [$status]\n";
}

// 2. Testes de CRM
echo "\n2. Testes de CRM:\n";
$crms = [
    ['crm' => '123', 'esperado' => false],        // curto demais
    ['crm' => '12345', 'esperado' => true],
    ['crm' => '123456/SP', 'esperado' => true],
    ['crm' => '123456789012', 'esperado' => false], // longo demais
    ['crm' => '12345-RJ', 'esperado' => true],
    ['crm' => '12345MG', 'esperado' => true],
    ['crm' => 'abcde', 'esperado' => false]       // apenas letras
];

foreach ($crms as $item) {
    $crm = $item['crm'];
    $esperado = $item['esperado'];
    $valido = validarCRM($crm);
    $res = $valido ? "Válido" : "Inválido";
    $status = ($valido === $esperado) ? "PASSED" : "FAILED";
    echo "  CRM: $crm -> Resultado: $res (Esperado: " . ($esperado ? "Válido" : "Inválido") . ") [$status]\n";
}

// 3. Testes de Criptografia
echo "\n3. Testes de Criptografia:\n";

// Teste Criptografia Aleatória (Randomized IV)
$originalRandom = "Telefone: (19) 98765-4321";
$cifradoRandom1 = encryptData($originalRandom, false);
$cifradoRandom2 = encryptData($originalRandom, false);
$decifradoRandom = decryptData($cifradoRandom1, false);

echo "  [Randomized IV]\n";
echo "    Original: $originalRandom\n";
echo "    Cifrado 1: $cifradoRandom1\n";
echo "    Cifrado 2: $cifradoRandom2\n";
echo "    Diferentes ciphertexts? " . ($cifradoRandom1 !== $cifradoRandom2 ? "SIM [PASSED]" : "NÃO [FAILED]") . "\n";
echo "    Decifrado com sucesso? " . ($decifradoRandom === $originalRandom ? "SIM [PASSED]" : "NÃO [FAILED]") . "\n";

// Teste Criptografia Determinística (Static IV)
$originalDet = "CRM: 12345/SP";
$cifradoDet1 = encryptData($originalDet, true);
$cifradoDet2 = encryptData($originalDet, true);
$decifradoDet = decryptData($cifradoDet1, true);

echo "\n  [Deterministic IV]\n";
echo "    Original: $originalDet\n";
echo "    Cifrado 1: $cifradoDet1\n";
echo "    Cifrado 2: $cifradoDet2\n";
echo "    Iguais ciphertexts? " . ($cifradoDet1 === $cifradoDet2 ? "SIM [PASSED]" : "NÃO [FAILED]") . "\n";
echo "    Decifrado com sucesso? " . ($decifradoDet === $originalDet ? "SIM [PASSED]" : "NÃO [FAILED]") . "\n";

echo "\n=== FIM DOS TESTES ===\n";
