# 🚀 Configuración de Deploy Automático a Hostgator

Este documento explica cómo configurar el despliegue automático desde GitHub a tu servidor Hostgator en transportesmazem.cl

---

## 📋 Requisitos Previos

1. Cuenta de Hostgator activa
2. Dominio transportesmazem.cl configurado
3. Acceso FTP a tu cuenta de Hostgator
4. Repositorio en GitHub: https://github.com/joseMunozNunez10/TransportesMazem

---

## 🔧 Paso 1: Obtener Credenciales FTP de Hostgator

### A través de cPanel:

1. **Accede a cPanel de Hostgator**
   - URL: https://hostgator.com/cpanel o desde tu panel de cliente

2. **Busca la sección "FTP Accounts" o "Cuentas FTP"**

3. **Crear o usar cuenta FTP:**
   - Si ya tienes una cuenta FTP principal, puedes usarla
   - O crear una nueva específica para GitHub Actions:
     ```
     Usuario: github-deploy@transportesmazem.cl
     Contraseña: [Crea una contraseña segura]
     Directorio: /public_html
     ```

4. **Anota estos datos:**
   ```
   FTP Server: ftp.transportesmazem.cl (o la IP proporcionada)
   FTP Username: tu-usuario@transportesmazem.cl
   FTP Password: tu-contraseña-segura
   Puerto: 21 (por defecto)
   ```

---

## 🔐 Paso 2: Configurar Secrets en GitHub

1. **Ve a tu repositorio en GitHub:**
   ```
   https://github.com/joseMunozNunez10/TransportesMazem
   ```

2. **Navega a Settings → Secrets and variables → Actions**

3. **Clic en "New repository secret"**

4. **Agrega los siguientes 3 secrets:**

   ### Secret 1: FTP_SERVER
   ```
   Name: FTP_SERVER
   Value: ftp.transportesmazem.cl
   ```
   (O usa la IP si te la proporcionaron: ej. 192.168.1.1)

   ### Secret 2: FTP_USERNAME
   ```
   Name: FTP_USERNAME
   Value: tu-usuario@transportesmazem.cl
   ```

   ### Secret 3: FTP_PASSWORD
   ```
   Name: FTP_PASSWORD
   Value: tu-contraseña-segura
   ```

5. **Guarda cada secret**

---

## 📁 Paso 3: Verificar Estructura en Hostgator

Asegúrate de que tu estructura en el servidor sea:

```
/home/tu-usuario/
├── public_html/              ← Carpeta raíz del sitio
│   ├── index.html
│   ├── about-company.html
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   ├── img/
│   │   └── fonts/
│   ├── book-ride.php
│   ├── contact.php
│   └── .htaccess
```

**Importante:** El workflow subirá el contenido de `./html/` a `./public_html/`

---

## 🚀 Paso 4: Activar GitHub Actions

1. **El archivo `.github/workflows/deploy.yml` ya está creado**

2. **Verifica que esté en la rama `main`:**
   ```bash
   git status
   ```

3. **Si todo está correcto, commitea y sube:**
   ```bash
   git add .github/workflows/deploy.yml
   git add html/.htaccess
   git commit -m "Add: GitHub Actions deploy workflow y .htaccess"
   git push origin main
   ```

4. **El deploy se ejecutará automáticamente**

---

## ✅ Paso 5: Verificar el Deploy

1. **Ve a tu repositorio en GitHub**

2. **Clic en la pestaña "Actions"**

3. **Verás el workflow "🚀 Deploy to Hostgator" ejecutándose**

4. **Estados posibles:**
   - 🟡 Amarillo (En progreso)
   - ✅ Verde (Exitoso)
   - ❌ Rojo (Error - revisa los logs)

5. **Si hay error:**
   - Haz clic en el workflow fallido
   - Revisa los logs para ver el error
   - Errores comunes:
     - Credenciales FTP incorrectas
     - Servidor FTP incorrecto
     - Problemas de permisos

---

## 🔄 Cómo Funciona el Deploy Automático

### Cada vez que hagas push a la rama `main`:

```bash
git add .
git commit -m "Tu mensaje de commit"
git push origin main
```

**GitHub Actions automáticamente:**
1. ✅ Descarga el código del repositorio
2. ✅ Se conecta a tu servidor Hostgator vía FTP
3. ✅ Sube solo los archivos modificados a `public_html/`
4. ✅ Mantiene los archivos existentes (no borra todo)
5. ✅ Tu sitio se actualiza en vivo en transportesmazem.cl

---

## 🛠 Configuración Adicional de Hostgator

### Configurar SSL (HTTPS)

1. **En cPanel, busca "SSL/TLS Status"**

2. **Habilita AutoSSL para transportesmazem.cl**

3. **O instala certificado Let's Encrypt gratuito**

4. **Verifica que HTTPS funcione:**
   ```
   https://transportesmazem.cl
   ```

### Configurar PHP (si es necesario)

1. **En cPanel, busca "Select PHP Version"**

2. **Selecciona PHP 7.4 o superior**

3. **Habilita extensiones necesarias:**
   - mbstring
   - curl
   - openssl

---

## 📧 Configurar Email para Formularios

### Para que los formularios PHP funcionen:

1. **En cPanel, ve a "Email Accounts"**

2. **Crea el email:**
   ```
   Email: contacto@transportesmazem.cl
   Contraseña: [segura]
   ```

3. **En los archivos PHP (`book-ride.php`, `contact.php`):**
   - El email `$recipient = "contacto@transportesmazem.cl"` ya está configurado
   - Debería funcionar automáticamente con la función `mail()` de PHP

4. **Si no funcionan los emails, configura SMTP:**
   - Instala PHPMailer
   - Usa las credenciales SMTP de Hostgator

---

## 🧪 Testing

### Después del primer deploy:

1. **Visita tu sitio:**
   ```
   https://transportesmazem.cl
   ```

2. **Verifica que cargue correctamente**

3. **Prueba los formularios:**
   - Formulario de cotización
   - Formulario de contacto

4. **Verifica en dispositivos móviles**

5. **Prueba la navegación completa**

---

## 🔍 Troubleshooting

### Error: "Failed to connect to FTP server"

**Solución:**
- Verifica que el FTP_SERVER sea correcto
- Prueba con: `ftp.transportesmazem.cl` o la IP del servidor
- Asegúrate de que Hostgator permita conexiones FTP
- Verifica que no haya firewall bloqueando

### Error: "Login authentication failed"

**Solución:**
- Revisa el FTP_USERNAME (debe incluir @transportesmazem.cl)
- Verifica el FTP_PASSWORD
- Prueba las credenciales con FileZilla primero

### Error: "Permission denied"

**Solución:**
- Verifica los permisos de la carpeta public_html
- Debe ser 755 o 775
- Contacta soporte de Hostgator si persiste

### Los archivos no se actualizan

**Solución:**
- Limpia caché del navegador (Ctrl + F5)
- Verifica que el workflow se completó exitosamente
- Revisa que el `server-dir` sea correcto: `./public_html/`

---

## 📊 Monitoreo

### Ver logs de deploy:

1. **GitHub Actions → Tu workflow**
2. **Clic en el job "Deploy"**
3. **Expande "Sync files to Hostgator via FTP"**
4. **Verás la lista de archivos subidos**

---

## 🔒 Seguridad

### Buenas Prácticas:

✅ **Nunca compartas los Secrets de GitHub**
✅ **Usa contraseñas fuertes para FTP**
✅ **Cambia las contraseñas regularmente**
✅ **Habilita autenticación de dos factores en GitHub**
✅ **Mantén el .htaccess actualizado**
✅ **Haz backups regulares en Hostgator**

---

## 📞 Soporte

### Si necesitas ayuda:

**Hostgator Support:**
- 24/7 Chat en vivo
- Teléfono: Disponible en tu panel
- Tickets de soporte

**GitHub Support:**
- https://docs.github.com/actions

**Desarrollador:**
- Email: contacto@transportesmazem.cl

---

## 🎉 ¡Listo!

Una vez configurado, cada push a `main` desplegará automáticamente tu sitio a transportesmazem.cl

```bash
# Workflow típico:
git add .
git commit -m "Update: mejoras en el formulario"
git push origin main

# GitHub Actions hace el resto automáticamente! 🚀
```

---

## 📝 Checklist de Configuración

- [ ] Obtener credenciales FTP de Hostgator
- [ ] Configurar 3 secrets en GitHub (SERVER, USERNAME, PASSWORD)
- [ ] Verificar estructura de carpetas en Hostgator
- [ ] Subir archivos .github/workflows/deploy.yml y .htaccess
- [ ] Hacer push y verificar primer deploy
- [ ] Configurar SSL/HTTPS
- [ ] Configurar email para formularios
- [ ] Probar sitio en producción
- [ ] Verificar formularios funcionando
- [ ] Probar en móviles y tablets

---

**¡Todo listo para deploy automático! 🎊**
