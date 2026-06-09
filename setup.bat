@echo off
REM ============================================================
REM MeuEspecialista — Script de Setup Automático (Windows)
REM ============================================================
REM Execute este script do diretório raiz do projeto

setlocal enabledelayedexpansion

echo.
echo 🚀 MeuEspecialista — Setup (Windows)
echo ==================================
echo.

REM Verificar se está no diretório correto
if not exist "README.md" (
    echo ❌ Execute este script da raiz do projeto ^(onde está README.md^)
    pause
    exit /b 1
)

echo ✓ Diretório correto detectado
echo.

REM ──────────────────────────────────────────────────────────
REM 1. Configurar Backend PHP
REM ──────────────────────────────────────────────────────────

echo 📦 Configurando Backend...

if not exist "php-backend\config\app_config.php" (
    if exist "php-backend\config\app_config.example.php" (
        copy "php-backend\config\app_config.example.php" "php-backend\config\app_config.php"
        echo ✓ Arquivo app_config.php criado
        echo   ⚠️  IMPORTANTE: Edite php-backend\config\app_config.php com suas chaves:
        echo       - JWT_SECRET: Cole aqui um valor seguro de 32+ caracteres
        echo       - ENCRYPTION_KEY: Gere com: openssl rand -hex 32
    )
) else (
    echo ✓ app_config.php já existe
)

if not exist "php-backend\config\email_config.php" (
    if exist "php-backend\config\email_config.example.php" (
        copy "php-backend\config\email_config.example.php" "php-backend\config\email_config.php"
        echo ✓ Arquivo email_config.php criado
        echo   ⚠️  IMPORTANTE: Edite php-backend\config\email_config.php com:
        echo       - EMAIL_USERNAME: Seu email do Gmail
        echo       - EMAIL_PASSWORD: Senha de App do Google ^(16 chars^)
    )
) else (
    echo ✓ email_config.php já existe
)

if not exist "php-backend\uploads" (
    mkdir "php-backend\uploads"
    echo ✓ Pasta php-backend\uploads\ criada
) else (
    echo ✓ Pasta php-backend\uploads\ já existe
)

echo.

REM ──────────────────────────────────────────────────────────
REM 2. Configurar Frontend Angular
REM ──────────────────────────────────────────────────────────

echo 📱 Configurando Frontend...

if exist "angular-frontend" (
    cd angular-frontend
    
    if not exist "node_modules" (
        echo   Instalando dependências ^(npm install^)...
        call npm install --legacy-peer-deps
        echo ✓ Dependências instaladas
    ) else (
        echo ✓ node_modules já existe
    )
    
    cd ..
) else (
    echo ❌ Pasta angular-frontend\ não encontrada
)

echo.

REM ──────────────────────────────────────────────────────────
REM 3. Resumo e próximos passos
REM ──────────────────────────────────────────────────────────

echo ✅ Setup concluído!
echo.
echo Próximos passos:
echo ================
echo.
echo 1️⃣  Backend ^(PHP^):
echo    • Inicie Apache e MySQL no Painel de Controle do XAMPP
echo    • Edite: php-backend\config\app_config.php
echo      - JWT_SECRET ^(mínimo 32 caracteres^)
echo      - ENCRYPTION_KEY ^(gere com: openssl rand -hex 32^)
echo    • Edite: php-backend\config\email_config.php
echo      - EMAIL_USERNAME ^(seu Gmail^)
echo      - EMAIL_PASSWORD ^(Senha de App do Google^)
echo    • Importe banco_estrutura.sql no phpMyAdmin
echo.
echo 2️⃣  Frontend ^(Angular^):
echo    • cmd: cd angular-frontend
echo    • cmd: ng serve -o
echo.
echo 3️⃣  Acesse:
echo    • Frontend: http://localhost:4200
echo    • API: http://localhost/MeuEspecialista/php-backend/api/
echo    • phpMyAdmin: http://localhost/phpmyadmin
echo.
pause
