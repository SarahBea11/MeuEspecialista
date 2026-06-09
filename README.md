<h1 align="center">🩺 MeuEspecialista</h1>

<p align="center">
  Plataforma web para busca e conexão com especialistas de saúde, desenvolvida com Angular e PHP.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/status-em%20desenvolvimento-yellow"/>
  <img src="https://img.shields.io/badge/version-2.0-blue"/>
  <img src="https://img.shields.io/badge/PWA-ready-brightgreen?logo=pwa"/>
</p>

---

## 📋 Sobre o projeto

O **MeuEspecialista** é um sistema web fullstack desenvolvido como projeto acadêmico, com o objetivo de conectar pacientes a profissionais da área da saúde. A plataforma permite que médicos cadastrem seus perfis com foto e convênios aceitos, e que pacientes os encontrem facilmente através de filtros de busca inteligentes.

---

## ✨ Funcionalidades

### 👤 Autenticação

- ✅ Cadastro de usuários (médicos e pacientes) com modal integrado na tela inicial
- ✅ Login com autenticação via **JWT (JSON Web Token)**
- ✅ Recuperação de senha completa com geração de token seguro e **envio real de e-mail via PHPMailer + Gmail SMTP**
- ✅ Interceptor HTTP automático para envio do token nas requisições
- ✅ Proteção de rotas com Guards no frontend Angular

### 🔍 Busca de Especialistas

- ✅ Busca por nome, especialidade e cidade
- ✅ Filtro por convênio aceito
- ✅ Cards modernos com foto de perfil do médico
- ✅ Modal detalhado com informações completas do especialista

### 🩺 Perfil do Médico

- ✅ Cadastro de CRM, especialidade, telefone, endereço e convênios aceitos
- ✅ **Upload de foto de perfil** (com validação de tipo MIME e tamanho máximo de 5MB)
- ✅ Avatar exibido nos cards de busca e no modal de detalhes
- ✅ Edição de perfil com visualização de dados descriptografados

### � Segurança

- ✅ **Criptografia AES-256-CBC** para dados sensíveis (CPF, CRM, telefone)
- ✅ **Senhas com hash bcrypt** (PASSWORD_DEFAULT do PHP)
- ✅ **JWT (JSON Web Token)** para autenticação stateless
- ✅ **CORS configurado** para apenas domínios autorizados
- ✅ **Validação de e-mail** e **formato de CPF/CRM** no servidor
- ✅ **Proteção CSRF** com validação de origem
### 🔔 UX & Feedback Visual
- ✅ Sistema de **Toast Notifications** (sucesso, erro, aviso)
- ✅ Spinners de carregamento em todas as ações assíncronas
- ✅ Layout totalmente **responsivo** (desktop, tablet e mobile) com telas de login e redefinição altamente modernas

### 📲 PWA (Progressive Web App)

- ✅ **Instalável** como app no Android, iOS e Desktop
- ✅ Service Worker configurado para cache offline
- ✅ Ícones nativos para todas as resoluções
- ✅ Splash screen e tema verde personalizado

---

## 🛠️ Tecnologias utilizadas

![Angular](https://img.shields.io/badge/angular-%23DD0031.svg?style=for-the-badge&logo=angular&logoColor=white)
![TypeScript](https://img.shields.io/badge/typescript-%23007ACC.svg?style=for-the-badge&logo=typescript&logoColor=white)
![HTML5](https://img.shields.io/badge/html5-%23E34F26.svg?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/css3-%231572B6.svg?style=for-the-badge&logo=css3&logoColor=white)
![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-%234479A1.svg?style=for-the-badge&logo=mysql&logoColor=white)
![JWT](https://img.shields.io/badge/JWT-black?style=for-the-badge&logo=JSON%20web%20tokens)
![XAMPP](https://img.shields.io/badge/XAMPP-%23FB7A24.svg?style=for-the-badge&logo=xampp&logoColor=white)
![PWA](https://img.shields.io/badge/PWA-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)
![Git](https://img.shields.io/badge/git-%23F05033.svg?style=for-the-badge&logo=git&logoColor=white)
![GitHub](https://img.shields.io/badge/github-%23121011.svg?style=for-the-badge&logo=github&logoColor=white)

---

## 💻 Pré-requisitos

| Tecnologia  | Versão Mínima | Comando para verificar   |
| :---------- | :------------ | :----------------------- |
| **Angular** | 21.x          | `ng version`             |
| **Node.js** | 18.x          | `node -v`                |
| **PHP**     | 8.2.x         | `php -v`                 |
| **MySQL**   | 8.0.x         | `mysql --version`        |
| **XAMPP**   | 3.3.x         | (Ver painel de controle) |

---



### Configuração Passo a Passo

### 1. Clone o repositório

```bash
git clone https://github.com/SarahBea11/MeuEspecialista.git
cd MeuEspecialista
```

### 2. Configuração do Backend (PHP)

1. Mova a pasta do projeto para o diretório `htdocs` do XAMPP:
   ```
   C:/xampp/htdocs/MeuEspecialista/
   ```
2. Inicie o **Apache** e o **MySQL** no Painel de Controle do XAMPP.
3. Configure os arquivos de ambiente:
   - Navegue até `php-backend/config/`
   - Copie `app_config.example.php` e renomeie para `app_config.php`
   - Defina uma chave secreta forte para `JWT_SECRET` (mínimo 32 caracteres aleatórios)
   - Gere uma chave de 32 bytes (hexadecimal) para `ENCRYPTION_KEY` com: `openssl rand -hex 32`
   - Configure `ENCRYPTION_METHOD` como `AES-256-CBC` (não altere)
   - **IMPORTANTE:** O arquivo `app_config.php` está no `.gitignore` por segurança e **nunca** deve ser commitado
4. **Configuração de envio de e-mails (Recuperação de Senha)**:
   - No diretório `php-backend/config/`, copie o arquivo `email_config.example.php` e renomeie para `email_config.php`.
   - Abra o `email_config.php` recém-criado e adicione suas credenciais do Gmail SMTP em `EMAIL_USERNAME`.
   - Gere uma **Senha de App** do Google de 16 caracteres em `myaccount.google.com/apppasswords` e insira em `EMAIL_PASSWORD` (sem espaços).
5. Importe o banco de dados:
   - Acesse `http://localhost/phpmyadmin`
   - Crie um banco chamado `meu_especialista`
   - Importe o arquivo `banco_estrutura.sql` da raiz do projeto. Ele já possui todas as tabelas prontas para uso.
6. Certifique-se que a pasta `php-backend/uploads/` tem permissão de escrita.

### 3. Configuração do Frontend (Angular)

```bash
cd angular-frontend
npm install
ng serve -o
```

O frontend estará disponível em: `http://localhost:4200`  
A API estará acessível em: `http://localhost/MeuEspecialista/php-backend/api/`

## 🚀Configuração rapida🚀

### Início Rápido (Setup Automático)

**Para facilitar a configuração, execute o script de setup:**

```bash
# Mac/Linux:
bash setup.sh

# Windows (PowerShell ou CMD):
setup.bat
```

Este script vai:
- ✅ Copiar `app_config.example.php` → `app_config.php`
- ✅ Copiar `email_config.example.php` → `email_config.php`
- ✅ Criar pasta `uploads/` com permissões corretas
- ✅ Instalar dependências do Angular (`npm install`)

**Depois, você precisará editar manualmente:**
- `php-backend/config/app_config.php` → Adicionar `JWT_SECRET` e `ENCRYPTION_KEY`
- `php-backend/config/email_config.php` → Adicionar credenciais do Gmail

---

### 📲 Como testar/instalar o PWA (Progressive Web App)

Por padrão, o Angular Service Worker **não roda em modo de desenvolvimento (`ng serve`)**. Para testar o suporte offline e instalar o aplicativo como PWA no celular ou desktop:

1. **Gere a build de produção** do frontend:
   ```bash
   npm run build --prod
   ```
2. **Sirva a build** usando um servidor HTTP estático (ex: `http-server` ou movendo os arquivos de `dist/` para a pasta pública do Apache no XAMPP).
3. **Requisito HTTPS/Localhost:** O recurso PWA só é ativado em conexões seguras (`https://`) ou em `localhost`. 
4. **Instalação:**
   - **No Google Chrome/Edge (Desktop):** Clique no ícone de computador com uma seta para baixo 🖥️ na barra de endereço (lado direito) e clique em **Instalar**.
   - **No Celular (Android/iOS):** Abra o link no navegador, clique nos três pontinhos (ou no botão de Compartilhar no iOS) e selecione **"Adicionar à tela de início"** ou **"Instalar aplicativo"**.


---

## 📁 Estrutura do projeto

```
MeuEspecialista/
├── angular-frontend/          # Frontend Angular (PWA)
│   ├── src/
│   │   ├── app/
│   │   │   ├── buscar/        # Tela de busca de médicos
│   │   │   ├── cadastro/      # Modal de cadastro
│   │   │   ├── home/          # Tela inicial
│   │   │   ├── login/         # Tela de login
│   │   │   ├── perfil/        # Perfil do médico (com upload de foto)
│   │   │   ├── services/      # AuthService, ToastService
│   │   │   └── models/        # Interfaces TypeScript
│   │   ├── assets/icons/      # Ícones PWA (72px a 512px)
│   │   └── manifest.webmanifest
│   └── ngsw-config.json       # Configuração do Service Worker
│
├── php-backend/               # Backend PHP (REST API)
│   ├── api/                   # Endpoints da API
│   │   ├── login.php
│   │   ├── cadastro.php
│   │   ├── perfil.php
│   │   ├── atualizar_perfil.php
│   │   ├── buscar_medicos.php
│   │   └── upload_foto.php    # Upload de foto de perfil
│   ├── config/                # Configurações e JWT
│   └── uploads/               # Fotos de perfil dos médicos
│
└── banco_estrutura.sql        # Estrutura do banco de dados
```

---

## 🗄️ Banco de Dados

O banco de dados possui **6 tabelas** com as seguintes relações:

- `usuarios` → tabela central de autenticação (`medico` | `paciente`)
- `medicos_perfil` → dados profissionais + foto de perfil
- `pacientes_perfil` → dados pessoais + convênio
- `convenios` → catálogo de planos de saúde
- `medico_convenio` → relacionamento N:M entre médicos e convênios
- `password_reset_tokens` → gerenciamento de tokens de recuperação de senha

---

## 🔐 Criptografia de Dados Sensíveis

Dados pessoais como **CPF**, **CRM** e **Telefone** são armazenados criptografados no banco usando **AES-256-CBC**:

- **CRM e Telefone (médicos):** Criptografia determinística (busca possível)
- **CPF e Telefone (pacientes):** Criptografia não-determinística (máxima segurança)

### Fluxo de descriptografia:

- **Médicos visualizando seu perfil** (`/api/perfil.php`): Recebem todos os dados **descriptografados** para edição
- **Pacientes buscando médicos** (`/api/buscar_medicos.php`): Recebem CRM e telefone do médico **descriptografados** para contato
- **Pacientes visualizando seu perfil** (`/api/perfil.php`): Recebem todos os dados **descriptografados** para edição

A chave de criptografia (`ENCRYPTION_KEY`) é definida em `php-backend/config/app_config.php` e é crítica para descriptografar dados existentes. Sem a chave correta, os dados permanecerão ilegíveis.

### Tamanho das colunas:

As colunas de dados criptografados foram redimensionadas para suportar a saída base64 (aproximadamente 1.3x o tamanho da entrada):

| Tabela            | Coluna    | Tamanho  |
| :---------------- | :-------- | :------- |
| medicos_perfil    | crm       | 255      |
| medicos_perfil    | telefone  | 255      |
| pacientes_perfil  | cpf       | 255      |
| pacientes_perfil  | telefone  | 255      |

---

## 👥 Autores

Desenvolvedores:

- **Sarah Pina** — [@SarahBea11](https://github.com/SarahBea11)
- **Matheus Prazeres** — [@MathzLabs](https://github.com/MathzLabs)

---

## ⚠️ Notas de Segurança

- **Backup de Chaves:** Faça backup seguro de `ENCRYPTION_KEY` em `php-backend/config/app_config.php`. Sem ela, dados já criptografados não poderão ser recuperados.
- **Produção:** Nunca commite `app_config.php` ou `email_config.php` no repositório. Use variáveis de ambiente ou um sistema de secrets.
- **HTTPS:** Em produção, implante sobre HTTPS para proteger tokens JWT em transit.
- **Permissões:** Certifique-se que a pasta `uploads/` tem permissão de escrita e leitura para o servidor web.
