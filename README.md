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
- ✅ **Upload de foto de perfil** (com validação de tipo e tamanho)
- ✅ Avatar exibido nos cards de busca e no modal de detalhes

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

## 🚀 Como executar o projeto

### 1. Clone o repositório

```bash
git clone https://github.com/SarahBea11/MeuEspecialista.git
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
   - Defina uma chave secreta forte para `JWT_SECRET`
4. **Configuração de envio de e-mails (Recuperação de Senha)**:
   - No diretório `php-backend/config/`, copie o arquivo `email_config.example.php` e renomeie para `email_config.php`.
   - Abra o `email_config.php` recém-criado e adicione suas credenciais do Gmail SMTP em `EMAIL_USERNAME`.
   - Gere uma **Senha de App** do Google de 16 caracteres em `myaccount.google.com/apppasswords` e insira em `EMAIL_PASSWORD` (sem espaços).
5. Importe o banco de dados:
   - Acesse `http://localhost/phpmyadmin`
   - Crie um banco chamado `meu_especialista`
   - Importe o arquivo `banco_estrutura.sql` da raiz do projeto. Ele já possui as tabelas `usuarios` e `password_reset_tokens` prontas para uso.
6. Certifique-se que a pasta `php-backend/uploads/` tem permissão de escrita.

### 3. Configuração do Frontend (Angular)

```bash
cd angular-frontend
npm install
ng serve -o
```

O frontend estará disponível em: `http://localhost:4200`  
A API estará acessível em: `http://localhost/MeuEspecialista/php-backend/api/`

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

O banco de dados possui **5 tabelas** com as seguintes relações:

- `usuarios` → tabela central de autenticação (`medico` | `paciente`)
- `medicos_perfil` → dados profissionais + foto de perfil
- `pacientes_perfil` → dados pessoais + convênio
- `convenios` → catálogo de planos de saúde
- `medico_convenio` → relacionamento N:M entre médicos e convênios

---

## 👥 Autores

Desenvolvedores:

- **Sarah Pina** — [@SarahBea11](https://github.com/SarahBea11)
- **Matheus Prazeres** — [@MathzLabs](https://github.com/MathzLabs)
