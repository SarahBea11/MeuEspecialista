# MeuEspecialista — Guia de Implementação: Recuperação de Senha

Este guia descreve o fluxo completo para implementar um sistema de **Recuperação e Redefinição de Senha** seguro no MeuEspecialista, abrangendo a alteração no banco de dados, endpoints no backend PHP e lógica em etapas no frontend Angular.

---

## 💻 Arquitetura do Fluxo de Recuperação

Como aplicações locais normalmente não possuem servidores SMTP configurados para envio de e-mails reais, a estratégia recomendada para fins acadêmicos e de teste é:
1. **Etapa 1 (Solicitação)**: O usuário informa o e-mail cadastrado.
2. **Backend**: O PHP valida o e-mail, gera um código numérico de 6 dígitos, salva-o no banco de dados com data de expiração e **retorna o código na resposta JSON** para permitir testes instantâneos na apresentação.
3. **Etapa 2 (Redefinição)**: O usuário insere o código exibido e digita a nova senha. O backend valida o código, criptografa a nova senha com `password_hash` e a salva.

---

## 🛠️ Passo 1: Atualização do Banco de Dados (MySQL)

Adicionamos as colunas de controle de recuperação diretamente na tabela de `usuarios`.

Execute o comando SQL abaixo no seu banco de dados:

```sql
ALTER TABLE usuarios 
ADD COLUMN codigo_recuperacao VARCHAR(6) DEFAULT NULL,
ADD COLUMN limite_recuperacao DATETIME DEFAULT NULL;
```

---

## 🔒 Passo 2: Backend PHP (Criação dos Endpoints)

### 1. Solicitar Código de Recuperação (`php-backend/api/esqueceu_senha.php`)
Este script verifica se o e-mail existe, gera um código aleatório de 6 dígitos e define um tempo limite de validade de 15 minutos.

```php
<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));
$email = isset($data->email) ? trim($data->email) : "";

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Informe um e-mail válido."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    // Verificar se o e-mail existe no banco
    $query = "SELECT id FROM usuarios WHERE email = :email LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        // Por motivos de segurança, evitamos dizer explicitamente que o e-mail não existe 
        // para prevenir a raspagem de e-mails por atacantes.
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Se o e-mail existir, você receberá o código."]);
        exit();
    }
    
    // Gerar um código numérico de 6 dígitos
    $codigo = strval(rand(100000, 999999));
    
    // Definir limite de validade para +15 minutos no fuso horário local
    $limite = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Atualizar no banco de dados
    $queryUpdate = "UPDATE usuarios SET codigo_recuperacao = :codigo, limite_recuperacao = :limite WHERE email = :email";
    $stmtUpdate = $db->prepare($queryUpdate);
    $stmtUpdate->bindParam(":codigo", $codigo);
    $stmtUpdate->bindParam(":limite", $limite);
    $stmtUpdate->bindParam(":email", $email);
    $stmtUpdate->execute();
    
    // Numa aplicação real, enviaríamos um e-mail aqui. 
    // Para a apresentação acadêmica, retornamos o código no JSON para testes rápidos:
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Código de recuperação gerado com sucesso!",
        "dev_code" => $codigo // Apenas para exibição em ambiente de desenvolvimento/teste
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro no servidor: " . $e->getMessage()]);
}
?>
```

### 2. Redefinir a Senha com o Código (`php-backend/api/redefinir_senha.php`)
Este endpoint recebe o e-mail, o código e a nova senha. Valida as chaves e atualiza a senha de forma segura.

```php
<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));
$email = isset($data->email) ? trim($data->email) : "";
$codigo = isset($data->codigo) ? trim($data->codigo) : "";
$novaSenha = isset($data->novaSenha) ? $data->novaSenha : "";

if (empty($email) || empty($codigo) || empty($novaSenha)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Preencha todos os campos obrigatórios."]);
    exit();
}

if (strlen($novaSenha) < 6) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "A nova senha deve ter pelo menos 6 caracteres."]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    // Buscar o código e limite salvos
    $query = "SELECT codigo_recuperacao, limite_recuperacao FROM usuarios WHERE email = :email LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "E-mail ou código inválidos."]);
        exit();
    }
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $codigoSalvo = $row['codigo_recuperacao'];
    $limiteSalvo = $row['limite_recuperacao'];
    
    // 1. Validar se o código bate
    if ($codigoSalvo !== $codigo) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Código de recuperação incorreto."]);
        exit();
    }
    
    // 2. Validar se o código expirou
    if (strtotime($limiteSalvo) < time()) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Este código expirou. Solicite um novo código."]);
        exit();
    }
    
    // Criptografar a nova senha de forma segura com Bcrypt
    $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
    
    // Atualizar a senha e limpar o código de recuperação (inutilizando após o uso)
    $queryUpdate = "UPDATE usuarios 
                    SET senha = :senha, codigo_recuperacao = NULL, limite_recuperacao = NULL 
                    WHERE email = :email";
                    
    $stmtUpdate = $db->prepare($queryUpdate);
    $stmtUpdate->bindParam(":senha", $senhaHash);
    $stmtUpdate->bindParam(":email", $email);
    $stmtUpdate->execute();
    
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Senha redefinida com sucesso!"]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro no servidor: " . $e->getMessage()]);
}
?>
```

---

## ⚡ Passo 3: Frontend Angular (Lógica e Roteamento)

### 1. Criar as chamadas no Serviço (`auth.ts`)
Adicione estes métodos ao seu serviço de autenticação (`angular-frontend/src/app/services/auth.ts`):

```typescript
solicitarCodigoRecuperacao(email: string): Observable<any> {
  return this.http.post<any>(`${this.apiUrl}esqueceu_senha.php`, { email });
}

redefinirSenha(dados: { email: string; codigo: string; novaSenha: string }): Observable<any> {
  return this.http.post<any>(`${this.apiUrl}redefinir_senha.php`, dados);
}
```

### 2. Atualizar o Componente TypeScript (`esqueceu-senha.ts`)
Implementamos a navegação em duas etapas (`etapa = 1` e `etapa = 2`) e as chamadas assíncronas:

```typescript
import { Component } from '@angular/core';
import { AuthService } from '../services/auth';
import { Router } from '@angular/router';

@Component({
  selector: 'app-esqueceu-senha',
  standalone: false,
  templateUrl: './esqueceu-senha.html',
  styleUrl: './esqueceu-senha.css',
})
export class EsqueceuSenha {
  email = '';
  codigo = '';
  novaSenha = '';
  confirmarSenha = '';
  
  mensagem = '';
  tipoMensagem: 'success' | 'error' | '' = '';
  
  etapa: number = 1; // Controla se pede e-mail ou insere nova senha
  carregando = false;

  constructor(private authService: AuthService, private router: Router) {}

  solicitarCodigo() {
    if (!this.email) {
      this.exibirMensagem('Digite um e-mail válido', 'error');
      return;
    }

    this.carregando = true;
    this.authService.solicitarCodigoRecuperacao(this.email).subscribe({
      next: (res) => {
        this.carregando = false;
        
        // Simulação acadêmica: exibe o código para facilitar testes
        const codigoInfo = res.dev_code ? ` (Para testes use: ${res.dev_code})` : '';
        this.exibirMensagem('Código enviado para o e-mail cadastrado!' + codigoInfo, 'success');
        
        // Avançar para a etapa de redefinição
        this.etapa = 2;
      },
      error: (err) => {
        this.carregando = false;
        this.exibirMensagem(err.error?.message || 'Erro ao processar solicitação.', 'error');
      }
    });
  }

  confirmarRedefinicao() {
    if (!this.codigo || !this.novaSenha || !this.confirmarSenha) {
      this.exibirMensagem('Preencha todos os campos obrigatórios', 'error');
      return;
    }

    if (this.novaSenha !== this.confirmarSenha) {
      this.exibirMensagem('As senhas não coincidem', 'error');
      return;
    }

    this.carregando = true;
    this.authService.redefinirSenha({
      email: this.email,
      codigo: this.codigo,
      novaSenha: this.novaSenha
    }).subscribe({
      next: (res) => {
        this.carregando = false;
        alert('Senha redefinida com sucesso! Você será redirecionado para o Login.');
        this.router.navigate(['/login']);
      },
      error: (err) => {
        this.carregando = false;
        this.exibirMensagem(err.error?.message || 'Erro ao redefinir a senha.', 'error');
      }
    });
  }

  exibirMensagem(texto: string, tipo: 'success' | 'error') {
    this.mensagem = texto;
    this.tipoMensagem = tipo;
  }
}
```

### 3. Ajuste do HTML Dinâmico em Etapas (`esqueceu-senha.html`)
Substitua o arquivo atual por este modelo que chaveia visualmente entre as etapas de forma responsiva:

```html
<div class="login-container">
  <div class="login-esquerda">
    <div class="logo-box">
      <img src="assets/logo-especialista.png" alt="Meu Especialista" />
    </div>
  </div>

  <!-- LADO DIREITO -->
  <div class="login-direita">
    <div class="form-box">
      
      <!-- ETAPA 1: SOLICITAR CÓDIGO -->
      <div *ngIf="etapa === 1">
        <h1>Recuperar Senha</h1>
        <p class="instrucoes-texto">Digite seu e-mail cadastrado. Enviaremos um código de 6 dígitos para redefinição da sua senha.</p>
        
        <label>E-mail de Cadastro</label>
        <input type="email" [(ngModel)]="email" placeholder="nome@exemplo.com" class="form-control" />
        
        <div *ngIf="mensagem" [class]="'mensagem-alert ' + tipoMensagem">
          {{ mensagem }}
        </div>
        
        <button type="button" class="btn btn-primary" (click)="solicitarCodigo()" [disabled]="carregando">
          {{ carregando ? 'Enviando...' : 'Enviar Código' }}
        </button>
      </div>

      <!-- ETAPA 2: DIGITAR CÓDIGO E DEFINIR NOVA SENHA -->
      <div *ngIf="etapa === 2">
        <h1>Redefinir Senha</h1>
        
        <div *ngIf="mensagem" [class]="'mensagem-alert ' + tipoMensagem">
          {{ mensagem }}
        </div>

        <label>Código de Recuperação</label>
        <input type="text" [(ngModel)]="codigo" placeholder="Digite os 6 dígitos" maxlength="6" class="form-control" />

        <label>Nova Senha</label>
        <input type="password" [(ngModel)]="novaSenha" class="form-control" placeholder="Mínimo de 6 caracteres" />

        <label>Confirmar Nova Senha</label>
        <input type="password" [(ngModel)]="confirmarSenha" class="form-control" placeholder="Repita a senha" />

        <button type="button" class="btn btn-primary" (click)="confirmarRedefinicao()" [disabled]="carregando">
          {{ carregando ? 'Gravando...' : 'Atualizar Senha' }}
        </button>
      </div>

      <div class="links-footer">
        <a class="back" routerLink="/login">Voltar para o Login</a>
        <a class="register-link" routerLink="/cadastro">Criar nova conta</a>
      </div>

    </div>
  </div>
</div>
```
