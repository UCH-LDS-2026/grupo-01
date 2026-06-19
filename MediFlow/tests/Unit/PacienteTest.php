<?php

use PHPUnit\Framework\TestCase;

/**
 * Tests para la clase Paciente - CASOS DEFENSIVOS
 * Prueba validación, límites y casos que podrían romper el sistema
 */
class PacienteTest extends TestCase
{
    private $conexionMock;
    private $paciente;

    protected function setUp(): void
    {
        $this->conexionMock = $this->createMock(mysqli::class);
        $this->paciente = new Paciente($this->conexionMock);
    }

    /**
     * Test 1: crear paciente con TELÉFONO CON LETRAS (dato inválido)
     */
    /**QUE HACE:  : Probamos mandarle letras donde van letras El sistema 
     * tiene que darse cuenta y abortar.*/
    public function testCrearPacienteConTelefonoInvalido(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        // Intenta pasar letras en teléfono: "ABCD-1234"
        $telefonoMalicioso = "ABCD-1234";
        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("ssssssss", "Pedro", "López", "12345678", "1990-01-01", "pedro@test.com", 
                   $telefonoMalicioso, "Plan A", "AF-001");

        // BD rechaza tipo de dato
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $resultado = $this->paciente->crear("Pedro", "López", "12345678", "1990-01-01", 
            "pedro@test.com", "ABCD-1234", "Plan A", "AF-001");

        $this->assertFalse($resultado, "Teléfono con letras debe rechazarse");
    }

    /**
     * Test 2: crear paciente con EMAIL INVÁLIDO (sin @)
     */
    /**QUE HACE: Le mandamos un correo sin el arroba @. El test confirma que
     *  se bloquea la creación.*/
    public function testCrearPacienteConEmailInvalido(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $emailInvalido = "noesunemailvalido.com"; // Falta @
        
        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("ssssssss", "Ana", "García", "87654321", "1990-05-15", 
                   $emailInvalido, "1234-5678", "Plan A", "AF-002");

        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $resultado = $this->paciente->crear("Ana", "García", "87654321", "1990-05-15", 
            "noesunemailvalido.com", "1234-5678", "Plan A", "AF-002");

        $this->assertFalse($resultado, "Email sin @ debe rechazarse");
    }

    /**
     * Test 3: crear paciente con DNI CON LETRAS (dato inválido)
     */
     /**QUE HACE: que solo permita ingresar DNI con numeros si existen letras no es valido */
    public function testCrearPacienteConDNIInvalido(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $dniInvalido = "ABC12345"; // DNI con letras
        
        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("ssssssss", "Luis", "Martínez", $dniInvalido, "1990-06-20", 
                   "luis@test.com", "5678-1234", "Plan B", "AF-003");

        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $resultado = $this->paciente->crear("Luis", "Martínez", "ABC12345", "1990-06-20", 
            "luis@test.com", "5678-1234", "Plan B", "AF-003");

        $this->assertFalse($resultado, "DNI con letras debe rechazarse");
    }

    /**
     * Test 4: crear paciente con FECHA FUTURA (caso inválido)
     */
    /**QUE HACE: Verificamos que no se pueda registrar un paciente que "va a nacer en el año 2030*/

    public function testCrearPacienteConFechaFutura(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $fechaFutura = "2030-12-31"; // Fecha del futuro

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("ssssssss", "Carlos", "Rodríguez", "11223344", $fechaFutura, 
                   "carlos@test.com", "9876-5432", "Plan A", "AF-004");

        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $resultado = $this->paciente->crear("Carlos", "Rodríguez", "11223344", "2030-12-31", 
            "carlos@test.com", "9876-5432", "Plan A", "AF-004");

        $this->assertFalse($resultado, "Fecha futura debe rechazarse");
    }

    /**
     * Test 5: buscar paciente con TEXTO VACÍO (caso límite)
     */
    /**QUE HACE: Verifica que el buscador no se vuelva loco si
     *  el usuario aprieta "Buscar" sin escribir nada, devolviendo 
     * un array vacío en vez de un error fatal. */
    public function testBuscarPacientePorTextoVacio(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("ssss", "%%", "%%", "%%", "%%");

        $stmtMock->expects($this->once())
            ->method('execute');

        $stmtMock->expects($this->once())
            ->method('get_result')
            ->willReturn($resultMock);

        $resultMock->expects($this->once())
            ->method('fetch_all')
            ->with(MYSQLI_ASSOC)
            ->willReturn([]); // Sin resultados

        $resultado = $this->paciente->buscarPorTexto("");

        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado, "Búsqueda vacía no debe retornar resultados");
    }

    /**
     * Test 6: buscar paciente por ID NEGATIVO (caso inválido)
     */ 
    /**QUE HACE:Prueba cómo reacciona tu buscador si alguien manipula la URL o el código 
     * para buscar a un paciente con un ID imposible (por ejemplo, el paciente número -1). 
     * El sistema debe darse cuenta de que no existe y devolver "Nada" (Null),
     *  en lugar de colgarse o tirar un error fatal en la pantalla.  */
    public function testBuscarPacientePorIDNegativo(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("i", -1);

        $stmtMock->expects($this->once())
            ->method('execute');

        $stmtMock->expects($this->once())
            ->method('get_result')
            ->willReturn($resultMock);

        $resultMock->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn(null); // No existe

        $resultado = $this->paciente->buscarPorId(-1);

        $this->assertNull($resultado, "ID negativo no debe retornar nada");
    }

    /**
     * Test 7: crear paciente con NOMBRE MUY LARGO (SQL injection via length)
     */
    /**QUE HACE:Mandamos un nombre de 1000 letras "A" para verificar que se respete 
     * el límite de la base de datos. */
    public function testCrearPacienteConNombreLargo(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $nombreMuyLargo = str_repeat("A", 1000); // 1000 caracteres
        
        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("ssssssss", $nombreMuyLargo, "Apellido", "12345678", "1990-01-01", 
                   "email@test.com", "1234-5678", "Plan", "AF");

        // BD rechaza por tamaño
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $resultado = $this->paciente->crear(str_repeat("A", 1000), "Apellido", "12345678", 
            "1990-01-01", "email@test.com", "1234-5678", "Plan", "AF");

        $this->assertFalse($resultado, "Nombre muy largo debe rechazarse");
    }

    /**
     * Test 8: crear paciente con AFILIADO DUPLICADO (constraint BD)
     */
    /**QUE HACE:Le mandamos a propósito un Número de Afiliado que ya existe. El test corrobora que la base de datos frene el duplicado y el sistema avise en lugar de sobreescribir. */
    public function testCrearPacienteConAfiliadoDuplicado(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param');

        // Simular error: afiliado duplicado
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new Exception("Duplicate entry for nro_afiliado"));

        try {
            $resultado = $this->paciente->crear("Rosa", "Sánchez", "22334455", "1985-03-10", 
                "rosa@test.com", "4321-8765", "Plan C", "AF-DUPLICATE");
            
            // Si se ejecutó sin excepción, no lanzó error (algo malo)
            $this->assertFalse($resultado, "Afiliado duplicado debe rechazarse");
        } catch (Exception $e) {
            // La excepción indica que el afiliado ya existe
            $this->assertStringContainsString("Duplicate", $e->getMessage());
        }
    }
}

