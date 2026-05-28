<?php
class Paciente {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function crear($nombre, $apellido, $dni, $fecha_nacimiento, $email, $telefono, $plan, $nro_afiliado) {
        $sql = "INSERT INTO paciente (nombre, apellido, dni, fecha_nacimiento, email, telefono, plan, nro_afiliado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ssssssss", $nombre, $apellido, $dni, $fecha_nacimiento, $email, $telefono, $plan, $nro_afiliado);
        return $stmt->execute();
    }

    public function listar() {
        $sql = "SELECT * FROM paciente ORDER BY id_paciente DESC";
        $resultado = $this->conexion->query($sql);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM paciente WHERE id_paciente = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function buscarPorTexto($texto) {
        $termino = "%" . $texto . "%";
        $sql = "SELECT * FROM paciente WHERE nombre LIKE ? OR apellido LIKE ? OR dni LIKE ? OR nro_afiliado LIKE ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ssss", $termino, $termino, $termino, $termino);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function editar($id, $nombre, $apellido, $dni, $fecha_nacimiento, $email, $telefono, $plan, $nro_afiliado) {
        $sql = "UPDATE paciente SET nombre = ?, apellido = ?, dni = ?, fecha_nacimiento = ?, email = ?, telefono = ?, plan = ?, nro_afiliado = ? WHERE id_paciente = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ssssssssi", $nombre, $apellido, $dni, $fecha_nacimiento, $email, $telefono, $plan, $nro_afiliado, $id);
        return $stmt->execute();
    }
    // 6. ELIMINAR PACIENTE (Dar de baja)
    public function eliminar($id) {
        $sql = "DELETE FROM paciente WHERE id_paciente = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>