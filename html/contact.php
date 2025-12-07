<?php
// Configuración de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Iniciar sesión para protección CSRF y rate limiting
session_start();

// Funciones de utilidad
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validate_name($name) {
    return preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/u", $name);
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && 
           preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email);
}

function validate_phone($phone) {
    // Acepta formato chileno: +56912345678 o 912345678
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return preg_match("/^(\+?56)?[2-9]\d{8}$/", $phone);
}

function check_honeypot() {
    return empty($_POST['website']); // Campo honeypot
}

function check_rate_limit() {
    if (!isset($_SESSION['last_contact'])) {
        $_SESSION['last_contact'] = time();
        $_SESSION['contact_count'] = 1;
        return true;
    }
    
    $time_diff = time() - $_SESSION['last_contact'];
    
    // Permitir 3 envíos por hora
    if ($time_diff < 1200 && $_SESSION['contact_count'] >= 3) {
        return false;
    }
    
    // Resetear contador si pasó 1 hora
    if ($time_diff >= 3600) {
        $_SESSION['contact_count'] = 1;
    } else {
        $_SESSION['contact_count']++;
    }
    
    $_SESSION['last_contact'] = time();
    return true;
}

// Solo procesar si es una solicitud POST
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
        echo "Has enviado demasiados mensajes. Por favor, espera un momento antes de intentar nuevamente.";
        exit;
    }
    
    // Obtener y sanitizar los campos del formulario
    $firstname = sanitize_input($_POST["firstname"] ?? '');
    $lastname = sanitize_input($_POST["lastname"] ?? '');
    $email = sanitize_input($_POST["email"] ?? '');
    $phone = sanitize_input($_POST["phone"] ?? '');
    $message = sanitize_input($_POST["message"] ?? '');
    
    // Array de errores
    $errors = [];

    // Validaciones específicas
    if (empty($firstname) || !validate_name($firstname)) {
        $errors[] = "Nombre inválido.";
    }
    
    if (!empty($lastname) && !validate_name($lastname)) {
        $errors[] = "Apellido inválido.";
    }
    
    if (empty($email) || !validate_email($email)) {
        $errors[] = "Email inválido.";
    }
    
    if (empty($phone) || !validate_phone($phone)) {
        $errors[] = "Teléfono inválido. Use formato chileno (ej: +56912345678).";
    }
    
    if (empty($message) || strlen($message) < 10) {
        $errors[] = "El mensaje debe tener al menos 10 caracteres.";
    }
    
    if (strlen($message) > 2000) {
        $errors[] = "El mensaje es demasiado largo (máximo 2000 caracteres).";
    }
    
    // Si hay errores, retornarlos
    if (!empty($errors)) {
        http_response_code(400);
        echo implode(" ", $errors);
        exit;
    }

    // Datos del destinatario
    $recipient = "contacto@transportesmazem.cl";
    $subject = "Mensaje desde el formulario de contacto de $firstname $lastname";

    // Contenido del correo
    $email_content = "Nombre: $firstname $lastname\n";
    $email_content .= "Correo: $email\n";
    $email_content .= "Teléfono: $phone\n";
    $email_content .= "Mensaje:\n$message\n";

    // Encabezados del correo mejorados
    $email_headers = "MIME-Version: 1.0\r\n";
    $email_headers .= "Content-type: text/plain; charset=UTF-8\r\n";
    $email_headers .= "From: Transportes Mazem <contacto@transportesmazem.cl>\r\n";
    $email_headers .= "Reply-To: $fullName <$email>\r\n";
    $email_headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $email_headers .= "X-Priority: 3\r\n";

    // Intentar enviar el correo
    try {
        if (mail($recipient, $subject, $email_content, $email_headers)) {
            http_response_code(200);
            echo "¡Gracias! Tu mensaje ha sido enviado exitosamente. Nos pondremos en contacto contigo pronto.";
            
            // Log opcional
            // error_log("Mensaje de contacto recibido de: $email", 0);
        } else {
            throw new Exception("Error al enviar el email");
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo "¡Ups! Algo salió mal y no pudimos enviar tu mensaje. Por favor, inténtalo más tarde o contáctanos directamente al +56 9 3574 4798.";
        
        // Log del error
        error_log("Error en contact.php: " . $e->getMessage());
    }

} else {
    // Método HTTP no permitido
    http_response_code(405);
    header('Allow: POST');
    echo "Error: Método no permitido. Solo se aceptan solicitudes POST.";
}
?>
