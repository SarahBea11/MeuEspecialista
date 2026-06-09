#!/bin/bash

# ============================================================
# MeuEspecialista — Script de Setup Automático
# ============================================================
# Este script ajuda a configurar o projeto após clonagem
# Executar com: bash setup.sh (no Mac/Linux) ou .\setup.bat (no Windows)

echo "🚀 MeuEspecialista — Setup"
echo "=========================="
echo ""

# ──────────────────────────────────────────────────────────
# 1. Verificar se está no diretório correto
# ──────────────────────────────────────────────────────────

if [ ! -f "README.md" ]; then
    echo "❌ Execute este script da raiz do projeto (onde está README.md)"
    exit 1
fi

echo "✓ Diretório correto detectado"
echo ""

# ──────────────────────────────────────────────────────────
# 2. Configurar Backend PHP
# ──────────────────────────────────────────────────────────

echo "📦 Configurando Backend..."

if [ ! -f "php-backend/config/app_config.php" ]; then
    if [ -f "php-backend/config/app_config.example.php" ]; then
        cp php-backend/config/app_config.example.php php-backend/config/app_config.php
        echo "✓ Arquivo app_config.php criado (exemplo copiado)"
        echo "  ⚠️  IMPORTANTE: Edite php-backend/config/app_config.php com suas chaves:"
        echo "      - JWT_SECRET: Cole aqui um valor seguro de 32+ caracteres"
        echo "      - ENCRYPTION_KEY: Gere com: openssl rand -hex 32"
    fi
else
    echo "✓ app_config.php já existe"
fi

if [ ! -f "php-backend/config/email_config.php" ]; then
    if [ -f "php-backend/config/email_config.example.php" ]; then
        cp php-backend/config/email_config.example.php php-backend/config/email_config.php
        echo "✓ Arquivo email_config.php criado (exemplo copiado)"
        echo "  ⚠️  IMPORTANTE: Edite php-backend/config/email_config.php com:"
        echo "      - EMAIL_USERNAME: Seu email do Gmail"
        echo "      - EMAIL_PASSWORD: Senha de App do Google (16 chars)"
    fi
else
    echo "✓ email_config.php já existe"
fi

if [ ! -d "php-backend/uploads" ]; then
    mkdir -p php-backend/uploads
    chmod 755 php-backend/uploads
    echo "✓ Pasta php-backend/uploads/ criada"
else
    echo "✓ Pasta php-backend/uploads/ já existe"
fi

echo ""

# ──────────────────────────────────────────────────────────
# 3. Configurar Frontend Angular
# ──────────────────────────────────────────────────────────

echo "📱 Configurando Frontend..."

if [ -d "angular-frontend" ]; then
    cd angular-frontend
    
    if [ ! -d "node_modules" ]; then
        echo "  Instalando dependências (npm install)..."
        npm install --legacy-peer-deps
        echo "✓ Dependências instaladas"
    else
        echo "✓ node_modules já existe"
    fi
    
    cd ..
else
    echo "❌ Pasta angular-frontend/ não encontrada"
fi

echo ""

# ──────────────────────────────────────────────────────────
# 4. Resumo e próximos passos
# ──────────────────────────────────────────────────────────

echo "✅ Setup concluído!"
echo ""
echo "Próximos passos:"
echo "================="
echo ""
echo "1️⃣  Backend (PHP):"
echo "   • Inicie Apache e MySQL no Painel de Controle do XAMPP"
echo "   • Edite: php-backend/config/app_config.php"
echo "     - JWT_SECRET (mínimo 32 caracteres)"
echo "     - ENCRYPTION_KEY (gere com: openssl rand -hex 32)"
echo "   • Edite: php-backend/config/email_config.php"
echo "     - EMAIL_USERNAME (seu Gmail)"
echo "     - EMAIL_PASSWORD (Senha de App do Google)"
echo "   • Importe banco_estrutura.sql no phpMyAdmin"
echo ""
echo "2️⃣  Frontend (Angular):"
echo "   • cd angular-frontend"
echo "   • ng serve -o"
echo ""
echo "3️⃣  Acesse:"
echo "   • Frontend: http://localhost:4200"
echo "   • API: http://localhost/MeuEspecialista/php-backend/api/"
echo "   • phpMyAdmin: http://localhost/phpmyadmin"
echo ""
