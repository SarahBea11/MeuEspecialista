# MeuEspecialista — Guia de Implementação: Upload de Foto de Perfil

Este guia detalha a implementação segura e robusta para upload e exibição de fotos de perfil de médicos. A arquitetura proposta separa responsabilidades e garante a segurança contra uploads maliciosos (Remote Code Execution) no backend PHP.

---

## 🛠️ Passo 1: Atualização do Banco de Dados (MySQL)

Primeiro, adicionamos uma coluna para armazenar o caminho da imagem na tabela de perfis de médicos.

Execute o comando SQL abaixo no seu painel do PHPMyAdmin ou console MySQL:

```sql
ALTER TABLE medicos_perfil 
ADD COLUMN foto_url VARCHAR(255) DEFAULT NULL;
```

---

## 🔒 Passo 2: Backend PHP (Segurança em Primeiro Lugar)

Criamos um novo endpoint em `php-backend/api/upload_foto.php`. Ele exige autenticação JWT e valida rigorosamente o arquivo enviado antes de salvá-lo física e logicamente.

### `php-backend/api/upload_foto.php`
```php
<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth_middleware.php';

// 1. Exigir autenticação JWT (Apenas o próprio médico autenticado pode atualizar sua foto)
$usuarioLogado = verificarAutenticacao();
$usuario_id = $usuarioLogado->id;
$tipo_usuario = $usuarioLogado->tipo;

if ($tipo_usuario !== 'medico') {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Apenas médicos podem realizar upload de foto."]);
    exit();
}

// 2. Verificar se o arquivo foi enviado por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método não permitido."]);
    exit();
}

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Nenhum arquivo enviado ou erro no upload."]);
    exit();
}

$file = $_FILES['foto'];

// 3. Validação A: Tamanho máximo do arquivo (2MB)
$max_size = 2 * 1024 * 1024; // 2 MegaBytes
if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "O arquivo não pode exceder 2MB."]);
    exit();
}

// 4. Validação B: Extensão de Imagem Permitida
$allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
$file_info = pathinfo($file['name']);
$extension = strtolower($file_info['extension']);

if (!in_array($extension, $allowed_extensions)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Apenas extensões JPG, JPEG, PNG e WEBP são permitidas."]);
    exit();
}

// 5. Validação C: Tipo de Mídia Real (MIME-Type) contra disfarces de arquivo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($mime_type, $allowed_mimes)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Arquivo inválido. O conteúdo precisa ser uma imagem real."]);
    exit();
}

// 6. Preparar diretório de uploads físico no servidor
$upload_dir = __DIR__ . '/../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// 7. Gerar nome de arquivo criptografado e único para evitar sobrescrita ou descoberta de nomes
$new_filename = "perfil_" . $usuario_id . "_" . md5(uniqid()) . "." . $extension;
$destination = $upload_dir . $new_filename;

// 8. Salvar o arquivo fisicamente na pasta uploads
if (move_uploaded_file($file['tmp_name'], $destination)) {
    
    // Conectar ao Banco e atualizar foto_url do médico
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Buscar se já existe uma foto cadastrada anteriormente para apagar a antiga física (limpeza de servidor)
        $querySelect = "SELECT foto_url FROM medicos_perfil WHERE usuario_id = :id";
        $stmtSelect = $db->prepare($querySelect);
        $stmtSelect->bindParam(":id", $usuario_id);
        $stmtSelect->execute();
        $old_pic = $stmtSelect->fetchColumn();
        
        if ($old_pic) {
            $old_path = __DIR__ . '/../' . $old_pic;
            if (file_exists($old_path)) {
                unlink($old_path); // Apaga a imagem anterior física
            }
        }
        
        // Salvar a nova URL (caminho relativo) no banco
        $relative_url = "uploads/" . $new_filename;
        $queryUpdate = "UPDATE medicos_perfil SET foto_url = :url WHERE usuario_id = :id";
        $stmtUpdate = $db->prepare($queryUpdate);
        $stmtUpdate->bindParam(":url", $relative_url);
        $stmtUpdate->bindParam(":id", $usuario_id);
        $stmtUpdate->execute();
        
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Upload realizado com sucesso!",
            "foto_url" => $relative_url
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Erro de Banco: " . $e->getMessage()]);
    }
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Erro ao mover arquivo de upload."]);
}
?>
```

---

## ⚡ Passo 3: Frontend Angular (Chamada de API)

### 1. Atualizar o Serviço (`angular-frontend/src/app/services/auth.ts`)
Adicione o método para postar o arquivo usando o objeto `FormData`:

```typescript
uploadFoto(arquivo: File): Observable<any> {
  const formData = new FormData();
  formData.append('foto', arquivo);

  return this.http.post<any>(`${this.apiUrl}upload_foto.php`, formData);
}
```

### 2. Implementar Seleção na Tela de Perfil (`perfil.ts`)
Lógica no componente de perfil do médico para ler o arquivo do input e enviá-lo ao serviço:

```typescript
fotoSelecionada: File | null = null;
fotoPreviewUrl: string | null = null;

onFileSelected(event: any): void {
  const file: File = event.target.files[0];
  if (file) {
    this.fotoSelecionada = file;

    // Criar um preview visual rápido para o médico ver antes de salvar
    const reader = new FileReader();
    reader.onload = () => {
      this.fotoPreviewUrl = reader.result as string;
    };
    reader.readAsDataURL(file);
    
    // Dispara o upload imediatamente ao selecionar ou cria um botão de "Salvar"
    this.salvarFoto();
  }
}

salvarFoto(): void {
  if (this.fotoSelecionada) {
    this.authService.uploadFoto(this.fotoSelecionada).subscribe({
      next: (res) => {
        alert('Foto atualizada com sucesso!');
        // Atualizar foto no perfil local
      },
      error: (err) => {
        console.error(err);
        alert(err.error?.message || 'Erro ao enviar foto.');
      }
    });
  }
}
```

### 3. Ajuste no HTML do Perfil (`perfil.html`)
O botão elegante de upload que substitui o input feio por uma interface premium:

```html
<div class="perfil-avatar-wrapper">
  <!-- Exibe a foto do médico ou o ícone padrão se não tiver foto -->
  <img [src]="fotoPreviewUrl || 'assets/default-avatar.png'" alt="Foto de Perfil" class="perfil-foto" />
  
  <label for="upload-foto" class="btn-upload-label">
    <span>Alterar Foto 📷</span>
  </label>
  <input type="file" id="upload-foto" (change)="onFileSelected($event)" accept="image/*" style="display: none;" />
</div>
```

---

## 🔍 Passo 4: Exibir a Foto na Tela de Busca

### 1. Atualizar API de Busca (`buscar_medicos.php`)
Modifique o select no backend para retornar também a coluna `m.foto_url`:

```sql
SELECT u.nome, u.email, m.especialidade, m.cidade, m.telefone, m.crm, m.endereco, m.foto_url
```

### 2. Exibição Dinâmica no Modal (`buscar.html`)
Substitua o emoji genérico de estetoscópio pela imagem real do médico cadastrada no banco:

```html
<div class="medico-avatar">
  <!-- Se o médico tiver foto, mostra ela. Senão, mostra o ícone de estetoscópio padrão -->
  <img *ngIf="medicoSelecionado.foto_url" 
       [src]="'http://localhost/MeuEspecialista/php-backend/' + medicoSelecionado.foto_url" 
       alt="Foto de Perfil" 
       class="medico-foto-circular" />
       
  <span *ngIf="!medicoSelecionado.foto_url" class="icon-avatar">🩺</span>
</div>
```

### 3. Estilização Premium (`buscar.css`)
```css
.medico-foto-circular {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover; /* Mantém a proporção da imagem sem distorcer */
}
```
