<?php
// Configuración de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Iniciar sesión para protección CSRF (opcional)
session_start();

// Función de sanitización mejorada
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Función de validación de nombre
function validate_name($name) {
    return preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/u", $name);
}

// Función de validación de email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && 
           preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email);
}

// Función simple anti-spam (honeypot)
function check_honeypot() {
    return empty($_POST['website']); // Campo honeypot
}

// Función de rate limiting simple
function check_rate_limit() {
    if (!isset($_SESSION['last_submission'])) {
        $_SESSION['last_submission'] = time();
        return true;
    }
    
    $time_diff = time() - $_SESSION['last_submission'];
    if ($time_diff < 30) { // 30 segundos entre envíos
        return false;
    }
    
    $_SESSION['last_submission'] = time();
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Verificar honeypot
    if (!check_honeypot()) {
        http_response_code(403);
        echo "Solicitud no válida.";
        exit;
    }
    
    // Verificar rate limiting
    if (!check_rate_limit()) {
        http_response_code(429);
        echo "Por favor, espera un momento antes de enviar otra solicitud.";
        exit;
    }
    
    // Sanitizar y validar datos
    $fullname = sanitize_input($_POST["full-name"] ?? '');
    $email = sanitize_input($_POST["email"] ?? '');
    $packageType = sanitize_input($_POST["package-type"] ?? '');
    $passengers = filter_var($_POST["passengers"] ?? '', FILTER_VALIDATE_INT);
    $startDest = sanitize_input($_POST["start-dest"] ?? '');
    $endDest = sanitize_input($_POST["end-dest"] ?? '');
    $rideDate = sanitize_input($_POST["ride-date"] ?? '');
    $rideTime = sanitize_input($_POST["ride-time"] ?? '');
    
    // Array de errores
    $errors = [];
    
    // Validaciones específicas
    if (empty($fullname) || !validate_name($fullname)) {
        $errors[] = "Nombre inválido. Use solo letras y espacios (2-50 caracteres).";
    }
    
    if (empty($email) || !validate_email($email)) {
        $errors[] = "Email inválido.";
    }
    
    if (empty($packageType) || !in_array($packageType, ['standard', 'business', 'economy'])) {
        $errors[] = "Tipo de servicio inválido.";
    }
    
    if ($passengers === false || $passengers < 1 || $passengers > 5) {
        $errors[] = "Número de pasajeros inválido (1-5).";
    }
    
    if (empty($startDest) || strlen($startDest) < 3) {
        $errors[] = "Lugar de origen inválido.";
    }
    
    if (empty($endDest) || strlen($endDest) < 3) {
        $errors[] = "Lugar de destino inválido.";
    }
    
    // Si hay errores, retornarlos
    if (!empty($errors)) {
        http_response_code(400);
        echo implode(" ", $errors);
        exit;
    }

    // Datos del destinatario
    $recipient = "contacto@transportesmazem.cl";
    $subject = "Nueva Cotización de Servicio - " . date('d/m/Y H:i');
    
    // Mapear tipos de servicio a nombres legibles
    $serviceTypes = [
        'standard' => 'Domicilio Nocturno',
        'business' => 'Acercamientos a Metros',
        'economy' => 'Traslados Diurnos'
    ];
    $serviceTypeName = $serviceTypes[$packageType] ?? $packageType;

    // Contenido del email con mejor formato
    $email_content = "=== NUEVA COTIZACIÓN DE SERVICIO ===\n\n";
    $email_content .= "INFORMACIÓN DEL CLIENTE:\n";
    $email_content .= "------------------------\n";
    $email_content .= "Nombre: $fullname\n";
    $email_content .= "Email: $email\n\n";
    
    $email_content .= "DETALLES DEL SERVICIO:\n";
    $email_content .= "------------------------\n";
    $email_content .= "Tipo de Servicio: $serviceTypeName\n";
    $email_content .= "Cantidad de Pasajeros: $passengers\n";
    $email_content .= "Origen: $startDest\n";
    $email_content .= "Destino: $endDest\n";
    
    if (!empty($rideDate)) {
        $email_content .= "Fecha: $rideDate\n";
    }
    if (!empty($rideTime)) {
        $email_content .= "Hora: $rideTime\n";
    }
    
    $email_content .= "\nFecha de solicitud: " . date('d/m/Y H:i:s') . "\n";
    $email_content .= "IP del cliente: " . ($_SERVER['REMOTE_ADDR'] ?? 'Desconocida') . "\n";

    // Headers del email mejorados
    $email_headers = "MIME-Version: 1.0\r\n";
    $email_headers .= "Content-type: text/plain; charset=UTF-8\r\n";
    $email_headers .= "From: Transportes Mazem <contacto@transportesmazem.cl>\r\n";
    $email_headers .= "Reply-To: $email\r\n";
    $email_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $email_headers .= "X-Priority: 1\r\n";

    // Intentar enviar el email
    try {
        if (mail($recipient, $subject, $email_content, $email_headers)) {
            http_response_code(200);
            echo "¡Gracias! Tu cotización ha sido enviada. Nos pondremos en contacto contigo pronto.";
            
            // Log opcional (descomentar si se necesita)
            // error_log("Cotización enviada desde: $email", 0);
        } else {
            throw new Exception("Error al enviar el email");
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo "¡Ups! No pudimos procesar tu solicitud. Por favor, intenta nuevamente o contáctanos directamente.";
        
        // Log del error
        error_log("Error en book-ride.php: " . $e->getMessage());
    }

} else {
    // Método HTTP no permitido
    http_response_code(405);
    header('Allow: POST');
    echo "Error: Método no permitido. Solo se aceptan solicitudes POST.";
}
?>
