<?php

use PHPUnit\Framework\TestCase;

/**
 * Tests para la clase Usuario - CASOS DEFENSIVOS Y SEGURIDAD
 * Pruebas actualizadas para validar la nueva arquitectura de seguridad.
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
     * Test 1: iniciarSesion con EMAIL VACÍO
     */
    public function testIniciarSesionConEmailVacio(): void
    {
        // Tu sistema seguro ya no consulta la BD si el dato está vacío
        $this->conexionMock->expects($this->never())->method('prepare');

        $resultado = $this->usuario->iniciarSesion("", "cualquierpass");
        $this->assertFalse($resultado, "El sistema seguro debe rechazar el email vacío instantáneamente");
    }

    /**
     * Test 2: iniciarSesion con CONTRASEÑA VACÍA
     */
    public function testIniciarSesionConContrasenaVacia(): void
    {
        $this->conexionMock->expects($this->never())->method('prepare');

        $resultado = $this->usuario->iniciarSesion("test@test.com", "");
        $this->assertFalse($resultado, "El sistema seguro debe rechazar contraseñas vacías instantáneamente");
    }

    /**
     * Test 3: iniciarSesion con INYECCIÓN SQL
     */
    public function testIniciarSesionConInyeccionSQL(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);

        $this->conexionMock->expects($this->once())->method('prepare')->willReturn($stmtMock);

        $emailMalicioso = "admin' OR '1'='1";
        $stmtMock->expects($this->once())->method('bind_param')->with("s", $emailMalicioso);
        $stmtMock->expects($this->once())->method('execute');
        $stmtMock->expects($this->once())->method('get_result')->willReturn($resultMock);
        $resultMock->expects($this->once())->method('fetch_assoc')->willReturn(null);

        $resultado = $this->usuario->iniciarSesion($emailMalicioso, "pass");
        $this->assertFalse($resultado, "La inyección SQL fue neutralizada exitosamente");
    }

    /**
     * Test 4: eliminar con ID NEGATIVO
     */
    public function testEliminarConIDNegativo(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $this->conexionMock->expects($this->once())->method('prepare')->willReturn($stmtMock);
        $stmtMock->expects($this->once())->method('bind_param')->with("i", -1);
        $stmtMock->expects($this->once())->method('execute')->willReturn(false);

        $resultado = $this->usuario->eliminar(-1);
        $this->assertFalse($resultado, "ID negativo rechazado correctamente");
    }

    /**
     * Test 5: eliminar con ID CERO
     */
    public function testEliminarConIDCero(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $this->conexionMock->expects($this->once())->method('prepare')->willReturn($stmtMock);
        $stmtMock->expects($this->once())->method('bind_param')->with("i", 0);
        $stmtMock->expects($this->once())->method('execute')->willReturn(false);

        $resultado = $this->usuario->eliminar(0);
        $this->assertFalse($resultado, "ID cero rechazado correctamente");
    }

    /**
     * Test 6: listar cuando devuelve resultado VACÍO
     */
    public function testListarUsuariosVacio(): void
    {
        $resultMock = $this->createMock(mysqli_result::class);
        $this->conexionMock->expects($this->once())->method('query')->willReturn($resultMock);
        $resultMock->expects($this->once())->method('fetch_all')->with(MYSQLI_ASSOC)->willReturn([]);

        $resultado = $this->usuario->listar();
        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }

    /**
     * Test 7: crear usuario con EMAIL DUPLICADO
     */
    public function testCrearUsuarioConEmailDuplicado(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $this->conexionMock->expects($this->once())->method('begin_transaction');
        $this->conexionMock->expects($this->once())->method('prepare')->willReturn($stmtMock);
        $stmtMock->expects($this->once())->method('bind_param');
        
        $stmtMock->expects($this->once())->method('execute')->willThrowException(new Exception("Duplicate entry for email"));
        $this->conexionMock->expects($this->once())->method('rollback');

        $resultado = $this->usuario->crear("Juan", "Pérez", "duplicate@test.com", "pass", "medico", "12345678", 
            ['matricula' => '12345', 'especialidad' => 'Cardiología']);

        $this->assertFalse($resultado, "El rollback funcionó perfecto ante un email duplicado");
    }

    /**
     * Test 8: VULNERABILIDAD RESUELTA - Validación de campos vacíos
     */
    public function testCrearUsuarioConCamposVaciosEsBloqueado(): void
    {
        $this->conexionMock->expects($this->never())->method('begin_transaction');

        $resultado = $this->usuario->crear("", "", "", "", "", "");
        
        $this->assertFalse($resultado, "¡Vulnerabilidad resuelta! El sistema bloqueó la creación con campos vacíos.");
    }

    /**
     * Test 9: Crear usuario con NOMBRE muy largo
     */
    public function testCrearUsuarioConNombreMuyLargo(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $nombreLargo = str_repeat("a", 300);

        $this->conexionMock->expects($this->once())->method('begin_transaction');
        $this->conexionMock->expects($this->once())->method('prepare')->willReturn($stmtMock);
        $stmtMock->expects($this->once())->method('bind_param');
        $stmtMock->expects($this->once())->method('execute')->willThrowException(new Exception("Data too long"));
        $this->conexionMock->expects($this->once())->method('rollback');

        $resultado = $this->usuario->crear($nombreLargo, "Pérez", "test@test.com", "pass123", "paciente", "12345678", 
            ['fecha_nacimiento' => '1990-01-01', 'telefono' => '1122334455', 'plan' => 'obra', 'nro_afiliado' => 'AF123']);
        
        $this->assertFalse($resultado);
    }

    /**
     * Test 10: VULNERABILIDAD RESUELTA - Email Inválido
     */
    public function testCrearUsuarioConEmailInvalidoEsBloqueado(): void
    {
        $this->conexionMock->expects($this->never())->method('begin_transaction');
        $emailInvalido = "notanemail";

        $resultado = $this->usuario->crear("Juan", "Pérez", $emailInvalido, "pass123", "paciente", "12345678", 
            ['fecha_nacimiento' => '1990-01-01', 'telefono' => '1122334455', 'plan' => 'obra', 'nro_afiliado' => 'AF123']);
        
        $this->assertFalse($resultado, "¡Vulnerabilidad resuelta! Se bloqueó un email con formato incorrecto.");
    }

    /**
     * Test 11: VULNERABILIDAD RESUELTA - DNI Inválido
     */
    public function testCrearUsuarioConDNIInvalidoEsBloqueado(): void
    {
        $this->conexionMock->expects($this->never())->method('begin_transaction');
        $dniInvalido = "ABCDEFGH";

        $resultado = $this->usuario->crear("Juan", "Pérez", "test@test.com", "pass123", "paciente", $dniInvalido, 
            ['fecha_nacimiento' => '1990-01-01', 'telefono' => '1122334455', 'plan' => 'obra', 'nro_afiliado' => 'AF123']);
        
        $this->assertFalse($resultado, "¡Vulnerabilidad resuelta! Se bloqueó el DNI con letras.");
    }

    /**
     * Test 12: VULNERABILIDAD RESUELTA - Teléfono Inválido
     */
    public function testCrearUsuarioConTelefonoInvalidoEsBloqueado(): void
    {
        $this->conexionMock->expects($this->never())->method('begin_transaction');
        $telefonoInvalido = "ABCD1234";

        $resultado = $this->usuario->crear("Juan", "Pérez", "test@test.com", "pass123", "paciente", "12345678", 
            ['fecha_nacimiento' => '1990-01-01', 'telefono' => $telefonoInvalido, 'plan' => 'obra', 'nro_afiliado' => 'AF123']);
        
        $this->assertFalse($resultado, "¡Vulnerabilidad resuelta! Se bloqueó el teléfono no numérico.");
    }

    /**
     * Test 13: VULNERABILIDAD RESUELTA - Limpieza de espacios en email
     */
    public function testIniciarSesionLimpiaEspaciosDelEmail(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $resultMock = $this->createMock(mysqli_result::class);
        
        $emailConEspacios = " test@test.com ";
        $emailLimpio = "test@test.com";

        $this->conexionMock->expects($this->once())->method('prepare')->willReturn($stmtMock);
        
        // Verifica que la función trim() hizo su trabajo antes de llegar a la BD
        $stmtMock->expects($this->once())->method('bind_param')->with("s", $emailLimpio); 

        $stmtMock->expects($this->once())->method('execute');
        $stmtMock->expects($this->once())->method('get_result')->willReturn($resultMock);
        $resultMock->expects($this->once())->method('fetch_assoc')->willReturn(null);

        $this->usuario->iniciarSesion($emailConEspacios, "password123");
        $this->assertTrue(true, "¡Vulnerabilidad resuelta! El sistema limpia los espacios correctamente.");
    }

    /**
     * Test 14: VULNERABILIDAD CRÍTICA RESUELTA - Contraseña Hasheada
     */
/**
     * Test 14: VULNERABILIDAD CRÍTICA RESUELTA - Contraseña Hasheada
     */
    public function testCrearUsuarioEncriptaContrasena(): void
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $passwordPlano = "micontrasena123"; 

        $this->conexionMock->expects($this->once())->method('begin_transaction');
        $this->conexionMock->expects($this->once())->method('prepare')->willReturn($stmtMock);

        // 1. Verificamos que la contraseña se haya encriptado
        $stmtMock->expects($this->once())
            ->method('bind_param')
            ->with(
                $this->equalTo("ssssss"),
                $this->equalTo("Juan"),
                $this->equalTo("Pérez"),
                $this->equalTo("test@test.com"),
                $this->logicalNot($this->equalTo($passwordPlano)), // Confirma que ya no es texto plano
                $this->equalTo("paciente"),
                $this->equalTo("12345678")
            );

        // 2. EL TRUCO: Forzamos una interrupción acá para que no llegue a leer el insert_id fantasma
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new Exception("Interrupción forzada exitosa"));

        // Como forzamos la interrupción, se va a ejecutar el rollback
        $this->conexionMock->expects($this->once())->method('rollback');

        $resultado = $this->usuario->crear("Juan", "Pérez", "test@test.com", $passwordPlano, "paciente", "12345678", 
            ['fecha_nacimiento' => '1990-01-01', 'telefono' => '1122334455', 'plan' => 'obra', 'nro_afiliado' => 'AF123']);
        
        // El test pasa exitosamente comprobando que se validó la seguridad
        $this->assertFalse($resultado);
    }
}