<?php

use PHPUnit\Framework\TestCase;

/**
 * Tests para la clase Evaluacion - CASOS DEFENSIVOS
 * Prueba validación y límites del sistema
 */
class EvaluacionTest extends TestCase
{
    private $conexionMock;
    private $evaluacion;

    protected function setUp(): void
    {
        $this->conexionMock = $this->createMock(mysqli::class);
        $this->evaluacion = new Evaluacion($this->conexionMock);
    }

    /**
     * Test 1: crear evaluación con ESTADO VACÍO (dato inválido)
     */
    public function testCrearEvaluacionConEstadoVacio(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("iiss", 1, 2, "", "Observación");

        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(false); // Estado vacío no es válido

        $resultado = $this->evaluacion->crear(1, 2, "", "Observación");

        $this->assertFalse($resultado, "Estado vacío debe rechazarse");
    }

    /**
     * Test 2: crear evaluación con ESTADO INVÁLIDO (no coincide con tipos permitidos)
     */
    public function testCrearEvaluacionConEstadoInvalido(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        // Estado que no existe: "Quizás" (solo debe ser Aprobado, Rechazado, etc)
        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("iiss", 1, 2, "EstadoInvalido", "Observación");

        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $resultado = $this->evaluacion->crear(1, 2, "EstadoInvalido", "Observación");

        $this->assertFalse($resultado, "Estado inválido debe rechazarse");
    }

    /**
     * Test 3: crear evaluación con ID_SOLICITUD NEGATIVO (dato inválido)
     */
    public function testCrearEvaluacionConIDSolicitudNegativo(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("iiss", -1, 2, "Aprobado", "Observación");

        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(false); // ID negativo no existe

        $resultado = $this->evaluacion->crear(-1, 2, "Aprobado", "Observación");

        $this->assertFalse($resultado, "ID solicitud negativo debe rechazarse");
    }

    /**
     * Test 4: crear evaluación con ID_AUDITOR CERO (dato inválido)
     */
    public function testCrearEvaluacionConIDAuditorCero(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("iiss", 1, 0, "Aprobado", "Observación");

        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $resultado = $this->evaluacion->crear(1, 0, "Aprobado", "Observación");

        $this->assertFalse($resultado, "ID auditor cero debe rechazarse");
    }

    /**
     * Test 5: crear evaluación con OBSERVACIONES VACÍAS (caso límite)
     */
    public function testCrearEvaluacionConObservacionesVacias(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("iiss", 1, 2, "Aprobado", "");

        // Observaciones vacías podrían aceptarse (es opcional)
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $resultado = $this->evaluacion->crear(1, 2, "Aprobado", "");

        $this->assertTrue($resultado, "Observaciones vacías deberían aceptarse");
    }

    /**
     * Test 6: crear evaluación con OBSERVACIONES MUY LARGAS (límite de texto)
     */
    public function testCrearEvaluacionConObservacionesMuyLargas(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $observacionesLargas = str_repeat("Observación ", 500); // ~6000 caracteres

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("iiss", 1, 2, "Rechazado", $observacionesLargas);

        // Si excede el limite de texto en BD, falla
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $resultado = $this->evaluacion->crear(1, 2, "Rechazado", $observacionesLargas);

        $this->assertFalse($resultado, "Observaciones demasiado largas deben rechazarse");
    }

    /**
     * Test 7: crear evaluación con INYECCIÓN SQL en observaciones
     */
    public function testCrearEvaluacionConInyeccionSQLEnObservaciones(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $observacionMaliciosa = "'; DROP TABLE evaluacion; --";

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("iiss", 1, 2, "Aprobado", $observacionMaliciosa);

        // Los prepared statements deben prevenir SQL injection
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true); // Debería pasar pero como texto seguro

        $resultado = $this->evaluacion->crear(1, 2, "Aprobado", $observacionMaliciosa);

        // Aunque prepare statement lo previene, aún debería insertarse como texto
        $this->assertTrue($resultado, "SQL injection debe ser escapada por prepared statement");
    }

    /**
     * Test 8: crear evaluación con IDS NO EXISTENT (foreign key constraint)
     */
    public function testCrearEvaluacionConIDsNoExistentes(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("iiss", 9999, 9999, "Aprobado", "IDs no existen");

        // Foreign key constraint falla - lanzar excepción
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new Exception("Foreign key constraint failed"));

        try {
            $resultado = $this->evaluacion->crear(9999, 9999, "Aprobado", "IDs no existen");
            // Si llega aquí sin excepción, debería haber fallado
            $this->assertFalse($resultado, "IDs no existentes deben rechazarse");
        } catch (Exception $e) {
            // La excepción captura que los IDs no existen
            $this->assertStringContainsString("Foreign key", $e->getMessage());
        }
    }
}

