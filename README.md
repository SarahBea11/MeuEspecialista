<h1 align="center">💼 MeuEspecialista</h1>

<p align="center">

Sistema web para busca e gerenciamento de médicos, desenvolvido com Angular (frontend) e PHP (backend).

</p>
 
<p align="center">
  <img src="https://img.shields.io/badge/status-em%20desenvolvimento-yellow"/>
  <img src="https://img.shields.io/badge/version-1.0-blue"/>
</p>

---

## 📌 Sobre o projeto

O **MeuEspecialista** é um sistema web desenvolvido com o objetivo de conectar usuários a profissionais da área da saúde, facilitando a busca por serviços de forma rápida e eficiente.

Este projeto foi desenvolvido como prática acadêmica, focando em desenvolvimento web e organização de sistemas.

---

## 🎯 Funcionalidades

- ✅ Cadastro de usuários
- ✅ Login com autenticação
- ✅ Listagem de especialistas
- 🚧 Busca por serviços
- 🚧 Sistema de agendamento (Atualizações futuras)

---

## 🛠️ Tecnologias utilizadas

![Angular](https://img.shields.io/badge/angular-%23DD0031.svg?style=for-the-badge&logo=angular&logoColor=white)
![TypeScript](https://img.shields.io/badge/typescript-%23007ACC.svg?style=for-the-badge&logo=typescript&logoColor=white)
![HTML5](https://img.shields.io/badge/html5-%23E34F26.svg?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/css3-%231572B6.svg?style=for-the-badge&logo=css3&logoColor=white)
![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-%234479A1.svg?style=for-the-badge&logo=mysql&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-%23FB7A24.svg?style=for-the-badge&logo=xampp&logoColor=white)
![Git](https://img.shields.io/badge/git-%23F05033.svg?style=for-the-badge&logo=git&logoColor=white)
![GitHub](https://img.shields.io/badge/github-%23121011.svg?style=for-the-badge&logo=github&logoColor=white)

---

## 📸 Demonstração

<!-- COLOQUE PRINTS DO SEU SISTEMA AQUI -->

![preview](link-da-imagem)

---

## ⚠️ Pré-requisitos

Antes de começar, verifique se você tem as seguintes versões instaladas:

| Tecnologia  | Versão Mínima | Comando para verificar   |
| :---------- | :------------ | :----------------------- |
| **Angular** | 21.2.4        | `ng version`             |
| **Node.js** | 24.14.1       | `node -v`                |
| **PHP**     | 8.2.0         | `php -v`                 |
| **MySQL**   | 8.0.0         | `mysql --version`        |
| **XAMPP**   | 3.3.0         | (Ver painel de controle) |

---

## ⚙️ Como executar o projeto

### 1. Clone o repositório

```bash
git clone https://github.com/SarahBea11/MeuEspecialista.git
```

### 2. Configuração do Backend (PHP)

1. Mova a pasta do projeto para o diretório `htdocs` do seu XAMPP.
2. Certifique-se de que o **Apache** e o **MySQL** estão ativos no Painel de Controle do XAMPP.
3. Configure as variáveis de ambiente:
   - Navegue até `php-backend/config/`.
   - Copie o arquivo `app_config.example.php` e renomeie para `app_config.php`.
   - Abra o `app_config.php` e defina uma chave segura para `JWT_SECRET`.
4. Importe o banco de dados (se houver um arquivo `.sql`, utilize o phpMyAdmin).


### 3. Configuração do Frontend (Angular)

1. Abra o terminal na pasta do projeto.
2. Navegue até a pasta do frontend:
   ```bash
   cd angular-frontend
   ```
3. Instale as dependências:
   ```bash
   npm install
   ```
4. Inicie o servidor de desenvolvimento:
   ```bash
   ng serve -o
   ```

O frontend estará disponível em: `http://localhost:4200`
A API estará acessível em: `http://localhost/MeuEspecialista/php-backend/api/`


## Estrutura do projeto

```
### 📁 Estrutura

MeuEspecialista/
├── angular-frontend/
└── php-backend/
```

## 👨‍💻 Autor

Desenvolvedores:

- Sarah Pina
- Matheus Prazeres
