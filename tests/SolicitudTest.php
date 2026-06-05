<?php
use PHPUnit\Framework\TestCase;

// Ruta correcta hacia la clase original en tu carpeta MediFlow
require_once __DIR__ . '/../MediFlow/modelo/Solicitud.php';

class SolicitudTest extends TestCase
{
    private $conexionMock;
    private $solicitud;

    protected function setUp(): void
    {
        // ARRANGE: Simulamos la base de datos
        $this->conexionMock = $this->createMock(mysqli::class);
        $this->solicitud = new Solicitud($this->conexionMock);
    }

    public function test_contar_pendientes_devuelve_numero_correcto()
    {
        $resultadoMock = $this->createMock(mysqli_result::class);
        $resultadoMock->method('fetch_assoc')->willReturn(['total' => 5]);

        $this->conexionMock->method('query')
                           ->with("SELECT COUNT(*) as total FROM solicitud WHERE estado = 'pendiente'")
                           ->willReturn($resultadoMock);

        $total = $this->solicitud->contarPendientes();

        $this->assertEquals(5, $total);
    }

    public function test_obtener_estadisticas_devuelve_array_con_datos()
    {
        $datosEsperados = [
            'aprobadas' => 10,
            'rechazadas' => 2,
            'pendientes' => 5
        ];

        $resultadoMock = $this->createMock(mysqli_result::class);
        $resultadoMock->method('fetch_assoc')->willReturn($datosEsperados);
        $this->conexionMock->method('query')->willReturn($resultadoMock);

        $estadisticas = $this->solicitud->obtenerEstadisticas();

        $this->assertIsArray($estadisticas);
        $this->assertEquals(10, $estadisticas['aprobadas']);
        $this->assertEquals(2, $estadisticas['rechazadas']);
    }

    public function test_actualizar_estado_ejecuta_correctamente()
    {
        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('bind_param')->willReturn(true);
        $stmtMock->method('execute')->willReturn(true); 

        $this->conexionMock->method('prepare')->willReturn($stmtMock);

        $resultado = $this->solicitud->actualizarEstado(1, 'aprobada');

        $this->assertTrue($resultado);
    }
}