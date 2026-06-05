#!/usr/bin/env php
<?php
/**
 * Script para ejecutar tests y generar reporte detallado CON ERRORES
 * Uso: php test-report.php
 */

echo "\n";
echo "═══════════════════════════════════════════════════════════════════════\n";
echo "          📊 REPORTE DETALLADO DE TESTS - TP4 DEFENSIVO 📊\n";
echo "═══════════════════════════════════════════════════════════════════════\n";
echo "\n";

// Ejecutar PHPUnit SIN --testdox para obtener errores completos
$output = shell_exec('C:\xampp\php\php.exe phpunit tests/ --configuration phpunit.xml 2>&1');

// Extraer información de estadísticas
preg_match('/Tests: (\d+)/', $output, $testsMatch);
preg_match('/Assertions: (\d+)/', $output, $assertionsMatch);
preg_match('/Time: ([^,]+)/', $output, $timeMatch);
preg_match('/Memory: ([^M]+)/', $output, $memoryMatch);
preg_match('/Errors?: (\d+)/', $output, $errorsMatch);
preg_match('/Failures?: (\d+)/', $output, $failuresMatch);

$tests = $testsMatch[1] ?? '0';
$assertions = $assertionsMatch[1] ?? '0';
$time = $timeMatch[1] ?? 'N/A';
$memory = $memoryMatch[1] ?? 'N/A';
$errors = $errorsMatch[1] ?? '0';
$failures = $failuresMatch[1] ?? '0';
$failedCount = (int)$errors + (int)$failures;
$passedCount = (int)$tests - $failedCount;

// Procesar líneas para extraer tests
$lines = explode("\n", $output);
$currentClass = '';
$testsByClass = [];
$failureDetails = [];

// Extraer detalles de fallos
$failureBlock = '';
$capturingFailure = false;

foreach ($lines as $line) {
    // Detectar inicio de bloque de fallos
    if (preg_match('/^(\d+\)) /', $line)) {
        if (!empty($failureBlock)) {
            // Guardar el bloque anterior
            preg_match('/^(\d+\)) ([^\n]+)/', $failureBlock, $matches);
            if (!empty($matches[2])) {
                $failureDetails[] = [
                    'number' => $matches[1],
                    'test' => $matches[2],
                    'details' => $failureBlock
                ];
            }
        }
        $failureBlock = $line . "\n";
        $capturingFailure = true;
    } elseif ($capturingFailure && !empty($line) && !preg_match('/^(\d+\))/', $line)) {
        $failureBlock .= $line . "\n";
    }
}

// Guardar último bloque
if (!empty($failureBlock)) {
    preg_match('/^(\d+\)) ([^\n]+)/', $failureBlock, $matches);
    if (!empty($matches[2])) {
        $failureDetails[] = [
            'number' => $matches[1],
            'test' => trim($matches[2]),
            'details' => $failureBlock
        ];
    }
}

// Mostrar tests por clase (de la salida de testdox)
$testdoxOutput = shell_exec('C:\xampp\php\php.exe phpunit tests/ --configuration phpunit.xml --testdox 2>&1');
$testdoxLines = explode("\n", $testdoxOutput);

foreach ($testdoxLines as $line) {
    $trimmed = trim($line);
    
    // Detectar clase
    if (preg_match('/^(Evaluacion|Paciente|Usuario)$/', $trimmed)) {
        $currentClass = $trimmed;
        echo "\n";
        echo "🔴 " . strtoupper($currentClass) . " TESTS\n";
        echo "───────────────────────────────────────────────────────────────\n";
    }
    
    // Detectar test pasado
    if (strpos($line, '✔') !== false) {
        $testName = trim(preg_replace('/[✔]/', '', $line));
        if (!empty($testName)) {
            echo "   ✅ " . $testName . "\n";
        }
    }
    
    // Detectar test fallido - solo en testdox si tiene símbolo
    else if ((strpos($line, '✕') !== false || strpos($line, '✗') !== false) && !empty($trimmed)) {
        $testName = trim(preg_replace('/[✕✗]/', '', $line));
        if (!empty($testName)) {
            echo "   ❌ " . $testName . "\n";
        }
    }
}

// Mostrar detalles de fallos si existen
if (!empty($failureDetails)) {
    echo "\n";
    echo "═══════════════════════════════════════════════════════════════════════\n";
    echo "                   🔴 DETALLES DE TESTS FALLIDOS 🔴\n";
    echo "═══════════════════════════════════════════════════════════════════════\n";
    
    foreach ($failureDetails as $idx => $failure) {
        echo "\n";
        echo "❌ Fallo #" . ($idx + 1) . ": " . $failure['test'] . "\n";
        echo "───────────────────────────────────────────────────────────────\n";
        
        // Extraer el mensaje de error
        preg_match('/VULNERABILIDAD[^\n]*|FALLO[^\n]*/', $failure['details'], $errorMatch);
        if (!empty($errorMatch[0])) {
            echo "   💥 " . $errorMatch[0] . "\n";
        } else {
            // Si no encuentra patrón específico, mostrar primeras líneas
            $lines_detail = explode("\n", $failure['details']);
            for ($i = 1; $i < min(5, count($lines_detail)); $i++) {
                $detailLine = trim($lines_detail[$i]);
                if (!empty($detailLine) && !preg_match('/^C:\\/', $detailLine)) {
                    echo "   💥 " . $detailLine . "\n";
                }
            }
        }
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════════════\n";
echo "                          📈 ESTADÍSTICAS 📈\n";
echo "═══════════════════════════════════════════════════════════════════════\n";
echo "\n";
printf("   Total Tests:      %d\n", $tests);
printf("   Tests Pasados:    %d ✅\n", $passedCount);
printf("   Tests Fallidos:   %d ❌\n", $failedCount);
printf("   Assertions:       %d\n", $assertions);
printf("   Tiempo:           %s\n", $time);
printf("   Memoria:          %s MB\n", $memory);
echo "\n";

echo "═══════════════════════════════════════════════════════════════════════\n";
if ($failedCount > 0) {
    echo "                    🔴 RESULTADO: " . $failedCount . " TEST(S) FALLIDO(S) 🔴\n";
} else {
    echo "                    ✅ RESULTADO: OK (TODO PASÓ) ✅\n";
}
echo "═══════════════════════════════════════════════════════════════════════\n";
echo "\n";
?>
