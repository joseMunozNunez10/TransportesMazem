# 📤 Deploy Manual a Hostgator

## ❌ Problema con GitHub Actions

Hostgator bloquea conexiones FTP desde los servidores de GitHub Actions por seguridad. Después de múltiples intentos con diferentes métodos (FTP, FTPS, LFTP), todos fallan con errores de conexión.

## ✅ Solución: Deploy Manual con FileZilla

### Paso 1: Descargar FileZilla
- Descarga desde: https://filezilla-project.org/
- Instala la versión Cliente (no Server)

### Paso 2: Configurar Conexión FTP

En FileZilla, ingresa los datos de conexión:

```
Servidor: ftp.transportesmazem.cl
Usuario: ftp@transportesmazem.cl
Contraseña: [Tu contraseña de cPanel]
Puerto: 21
```

O usa el **Gestor de Sitios** (Archivo > Gestor de sitios):
- Protocolo: FTP
- Servidor: ftp.transportesmazem.cl
- Puerto: 21
- Modo de cifrado: Solo usar FTP simple (inseguro)
- Tipo de acceso: Normal
- Usuario: ftp@transportesmazem.cl
- Contraseña: [Tu contraseña]

### Paso 3: Subir Archivos

1. **Conecta** usando el botón "Conexión rápida"
2. En el panel derecho, navega a `/public_html/`
3. En el panel izquierdo, navega a tu carpeta local: `TransportesMazem/html/`
4. **Selecciona todo** el contenido de la carpeta `html/` (no la carpeta misma)
5. **Arrastra** todo al panel derecho sobre `/public_html/`
6. Espera que termine la transferencia (140 archivos, ~2-3 minutos)

### Paso 4: Verificar

Abre tu navegador en: **https://transportesmazem.cl**

Deberías ver tu sitio con todas las mejoras:
- ✅ Diseño responsive mobile
- ✅ Optimización de rendimiento
- ✅ SEO mejorado
- ✅ Formularios seguros
- ✅ Compresión y caché activados

## 🔄 Para Actualizaciones Futuras

Cuando hagas cambios en el código:

1. Edita los archivos localmente
2. Haz commit a GitHub:
   ```bash
   git add .
   git commit -m "Descripción de cambios"
   git push origin main
   ```
3. Abre FileZilla
4. Conecta a tu servidor
5. Sube **solo los archivos modificados**

### Tip: Ver archivos modificados
```bash
git status
```

Esto muestra qué archivos cambiaron, sube solo esos a través de FTP.

## 🌐 Alternativa: Cambiar de Hosting

Si quieres deploy automático real, considera migrar a servicios modernos:

### Netlify (Recomendado para sitios estáticos)
- ✅ Deploy automático desde GitHub
- ✅ SSL gratis
- ✅ CDN global
- ✅ 100GB bandwidth gratis/mes
- ✅ Formularios sin PHP (usa Netlify Forms)

### Vercel
- ✅ Deploy automático
- ✅ Performance excelente
- ✅ Gratis para proyectos personales

### Configuración en Netlify:
1. Conecta tu repositorio GitHub
2. Build settings:
   - Base directory: `html`
   - Publish directory: `.`
3. Configura tu dominio `transportesmazem.cl`
4. Cada push a `main` despliega automáticamente

**Nota:** Necesitarías adaptar los formularios PHP a Netlify Forms o usar un backend serverless.

## 📞 Soporte Hostgator

Si quieres mantener Hostgator, contacta su soporte para:
- Habilitar acceso SFTP/SSH (más seguro que FTP)
- Whitelist de IPs de GitHub Actions
- Configurar API de cPanel para deploy

---

**Resumen:** GitHub Actions + Hostgator FTP no es compatible por restricciones de seguridad. Usa FileZilla para deploy manual o migra a hosting moderno para automatización real.
