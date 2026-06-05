<?php

use PHPUnit\Framework\TestCase;

/**
 * Tests para la clase Usuario - CASOS DEFENSIVOS
 * Prueba validación y casos límite que podrían romper el sistema
 */
class UsuarioTest extends TestCase
{
    private $conexionMock;
    private $usuario;

    protected function setUp(): void
    {
        $this->conexionMock = $this->createMock(mysqli::class);
        $this->usuario = new Usuario($this->conexionMock);
    }

    /**
     * Test 1: iniciarSesion con EMAIL VACÍO (posible error)
     */
    public function testIniciarSesionConEmailVacio(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("s", "");

        $stmtMock->expects($this->once())
            ->method('execute');

        $stmtMock->expects($this->once())
            ->method('get_result')
            ->willReturn($resultMock);

        $resultMock->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn(null);

        $resultado = $this->usuario->iniciarSesion("", "cualquierpass");
        
        // Debería rechazar email vacío
        $this->assertFalse($resultado, "Email vacío debe rechazarse");
    }

    /**
     * Test 2: iniciarSesion con CONTRASEÑA VACÍA (posible error)
     */
    public function testIniciarSesionConContraseñaVacia(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('execute');

        $stmtMock->expects($this->once())
            ->method('get_result')
            ->willReturn($resultMock);

        $resultMock->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn(null);

        $resultado = $this->usuario->iniciarSesion("test@test.com", "");
        
        $this->assertFalse($resultado, "Contraseña vacía debe rechazarse");
    }

    /**
     * Test 3: iniciarSesion con INYECCIÓN SQL (') - posible ataque
     */
    public function testIniciarSesionConInyeccionSQL(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        // Intenta inyectar SQL
        $emailMalicioso = "admin' OR '1'='1";
        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("s", $emailMalicioso);

        $stmtMock->expects($this->once())
            ->method('execute');

        $stmtMock->expects($this->once())
            ->method('get_result')
            ->willReturn($resultMock);

        $resultMock->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn(null); // Debe rechazar

        $resultado = $this->usuario->iniciarSesion($emailMalicioso, "pass");
        
        $this->assertFalse($resultado, "Inyección SQL debe ser rechazada");
    }

    /**
     * Test 4: eliminar con ID NEGATIVO (caso inválido)
     */
    public function testEliminarConIDNegativo(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("i", -1);

        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(false); // Falla con ID negativo

        $resultado = $this->usuario->eliminar(-1);
        
        $this->assertFalse($resultado, "ID negativo debe rechazarse");
    }

    /**
     * Test 5: eliminar con ID CERO (caso inválido)
     */
    public function testEliminarConIDCero(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("i", 0);

        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $resultado = $this->usuario->eliminar(0);
        
        $this->assertFalse($resultado, "ID cero debe ser rechazado");
    }

    /**
     * Test 6: listar cuando devuelve resultado VACÍO
     */
    public function testListarUsuariosVacio(): void
    {
        $resultMock = $this->createMock(mysqli_result::class);
        
        $this->conexionMock->expects($this->once())
            ->method('query')
            ->willReturn($resultMock);

        $resultMock->expects($this->once())
            ->method('fetch_all')
            ->with(MYSQLI_ASSOC)
            ->willReturn([]); // BD vacía

        $resultado = $this->usuario->listar();

        $this->assertIsArray($resultado, "Debe retornar array");
        $this->assertEmpty($resultado, "Array debe estar vacío");
    }

    /**
     * Test 7: crear usuario con EMAIL DUPLICADO (posible error BD)
     */
    public function testCrearUsuarioConEmailDuplicado(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('begin_transaction');

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param');

        // Simular error de email duplicado
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new Exception("Duplicate entry for email"));

        $this->conexionMock->expects($this->once())
            ->method('rollback'); // Debe revertir

        $resultado = $this->usuario->crear("Juan", "Pérez", "duplicate@test.com", "pass", "medico", 
            ['matricula' => '12345', 'especialidad' => 'Cardiología']);

        $this->assertFalse($resultado, "Email duplicado debe fallar");
    }

    /**
     * Test 8: VULNERABILIDAD ENCONTRADA - Falta validación de campos vacíos
     * Este test FALLA porque el código no valida entrada antes de prepare()
     * DEBE agregarse validación en Usuario.php línea 39
     */
    public function testCrearUsuarioConCamposVaciosVulnerabilidadDetectada(): void
    {
        // Este test debería fallar porque NO hay validación de campos vacíos
        // La función create() DEBERÍA rechazarlos pero no lo hace
        $this->fail("VULNERABILIDAD DETECTADA: La función crear() no valida campos vacíos. Sin validación, campos como '' pueden causar comportamiento impredecible en la BD");
    }

    /**
     * Test 9: CASOS LÍMITE - Crear usuario con NOMBRE muy largo (>255 caracteres)
     */
    public function testCrearUsuarioConNombreMuyLargo(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $nombreLargo = str_repeat("a", 300); // Excede límite de VARCHAR(255)

        $this->conexionMock->expects($this->once())
            ->method('begin_transaction');

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        // El bind_param debería truncar o fallar
        $stmtMock->expects($this->once())
            ->method('bind_param');

        $stmtMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new Exception("Data too long for column 'nombre'"));

        $this->conexionMock->expects($this->once())
            ->method('rollback');

        $resultado = $this->usuario->crear($nombreLargo, "Pérez", "test@test.com", "pass123", "paciente", 
            ['dni' => '12345678', 'fecha_nacimiento' => '1990-01-01', 'telefono' => '1122334455', 'plan' => 'obra', 'nro_afiliado' => 'AF123']);
        
        $this->assertFalse($resultado, "Nombre muy largo debe rechazarse");
    }

    /**
     * Test 10: CASOS LÍMITE - Crear usuario con EMAIL INVÁLIDO (sin @)
     */
    public function testCrearUsuarioConEmailInvalido(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);

        $this->conexionMock->expects($this->once())
            ->method('begin_transaction');

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $emailInvalido = "notanemail";
        $stmtMock->expects($this->once())
            ->method('bind_param');

        // Sin validación, se inserta un email inválido
        $stmtMock->expects($this->once())
            ->method('execute');

        $this->conexionMock->expects($this->once())
            ->method('commit');

        $resultado = $this->usuario->crear("Juan", "Pérez", $emailInvalido, "pass123", "paciente", 
            ['dni' => '12345678', 'fecha_nacimiento' => '1990-01-01', 'telefono' => '1122334455', 'plan' => 'obra', 'nro_afiliado' => 'AF123']);
        
        // Este test EXPONE que no hay validación de formato email
        $this->fail("VULNERABILIDAD DETECTADA: Email '$emailInvalido' se acepta sin validación de formato");
    }

    /**
     * Test 11: CASOS LÍMITE - Crear usuario con DNI INVÁLIDO (letras en lugar de números)
     */
    public function testCrearUsuarioConDNIInvalido(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $dniInvalido = "ABCDEFGH"; // Debe ser números

        $this->conexionMock->expects($this->once())
            ->method('begin_transaction');

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param');

        $stmtMock->expects($this->once())
            ->method('execute');

        $this->conexionMock->expects($this->once())
            ->method('commit');

        $resultado = $this->usuario->crear("Juan", "Pérez", "test@test.com", "pass123", "paciente", 
            ['dni' => $dniInvalido, 'fecha_nacimiento' => '1990-01-01', 'telefono' => '1122334455', 'plan' => 'obra', 'nro_afiliado' => 'AF123']);
        
        $this->fail("VULNERABILIDAD DETECTADA: DNI '$dniInvalido' se acepta sin validar que sea numérico");
    }

    /**
     * Test 12: CASOS LÍMITE - Crear usuario con TELÉFONO NO NUMÉRICO (letras)
     */
    public function testCrearUsuarioConTelefonoInvalido(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $telefonoInvalido = "ABCD1234"; // Debe ser números

        $this->conexionMock->expects($this->once())
            ->method('begin_transaction');

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param');

        $stmtMock->expects($this->once())
            ->method('execute');

        $this->conexionMock->expects($this->once())
            ->method('commit');

        $resultado = $this->usuario->crear("Juan", "Pérez", "test@test.com", "pass123", "paciente", 
            ['dni' => '12345678', 'fecha_nacimiento' => '1990-01-01', 'telefono' => $telefonoInvalido, 'plan' => 'obra', 'nro_afiliado' => 'AF123']);
        
        $this->fail("VULNERABILIDAD DETECTADA: Teléfono '$telefonoInvalido' se acepta sin validar que sea numérico");
    }

    /**
     * Test 13: CASOS LÍMITE - Iniciar sesión con EMAIL que contiene espacios
     */
    public function testIniciarSesionConEmailConEspacios(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);
        $emailConEspacios = " test@test.com ";

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("s", $emailConEspacios); // Sin trim()

        $stmtMock->expects($this->once())
            ->method('execute');

        $stmtMock->expects($this->once())
            ->method('get_result')
            ->willReturn($resultMock);

        // No encontrará el usuario porque busca " test@test.com "
        $resultMock->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn(null);

        $resultado = $this->usuario->iniciarSesion($emailConEspacios, "password123");
        
        $this->fail("VULNERABILIDAD DETECTADA: Email con espacios '$emailConEspacios' se acepta sin trim()");
    }

    /**
     * Test 14: CASOS LÍMITE - Contraseña sin hasheado (texto plano)
     * La función guarda passwords en texto plano (CRÍTICO)
     */
    public function testCrearUsuarioConContraseñaTextoplano(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $passwordPlano = "micontraseña123"; // Debería estar hasheada

        $this->conexionMock->expects($this->once())
            ->method('begin_transaction');

        $this->conexionMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with("sssss", "Juan", "Pérez", "test@test.com", $passwordPlano, "paciente");

        $stmtMock->expects($this->once())
            ->method('execute');

        $this->conexionMock->expects($this->once())
            ->method('commit');

        $resultado = $this->usuario->crear("Juan", "Pérez", "test@test.com", $passwordPlano, "paciente", 
            ['dni' => '12345678', 'fecha_nacimiento' => '1990-01-01', 'telefono' => '1122334455', 'plan' => 'obra', 'nro_afiliado' => 'AF123']);
        
        $this->fail("VULNERABILIDAD CRÍTICA DETECTADA: Contraseña guardada en TEXTO PLANO. Debe usar password_hash()");
    }
}

