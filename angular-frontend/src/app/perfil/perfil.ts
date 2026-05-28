import { Router } from '@angular/router';
import { AuthService } from '../services/auth';
import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { ToastService } from '../services/toast';
import { environment } from '../environments';

@Component({
  selector: 'app-perfil',
  standalone: false,
  templateUrl: './perfil.html',
  styleUrls: ['./perfil.css'],
})
export class Perfil implements OnInit {
  usuario: any = {
    nome: '',
    email: '',
    tipo: 'paciente',
    cidade: '',
    endereco: '',
    telefone: '',
    convenio: '',
    cpf: '',
    crm: '',
    especialidade: '',
    senha: '',
    confirmarSenha: '',
    foto: '',
  };
  usuarioOriginal: any = {};
  editando: boolean = false;
  loading: boolean = false;
  exibirModalExclusao: boolean = false;

  constructor(
    private authService: AuthService,
    private router: Router,
    private cdr: ChangeDetectorRef,
    private toastService: ToastService,
  ) {}

  obterFotoUrl(foto: string): string {
    if (!foto) return '';
    const uploadsBase = environment.apiUrl.replace('/api/', '/uploads/');
    return `${uploadsBase}${foto}`;
  }

  aoSelecionarArquivo(event: any): void {
    const file = event.target.files[0];
    if (!file) return;

    // Valida o tamanho máximo de 5MB
    if (file.size > 5 * 1024 * 1024) {
      this.toastService.error('Tamanho excedido', 'A foto de perfil deve ter no máximo 5MB.');
      return;
    }

    this.loading = true;
    this.authService.uploadFoto(file).subscribe({
      next: (res: any) => {
        this.loading = false;
        this.usuario.foto = res.foto;
        this.toastService.success('Sucesso', 'Sua foto de perfil foi atualizada!');
        this.cdr.detectChanges();
      },
      error: (err) => {
        this.loading = false;
        console.error(err);
        this.toastService.error('Erro no Upload', err.error?.message || 'Erro ao enviar a imagem.');
        this.cdr.detectChanges();
      },
    });
  }

  ngOnInit(): void {
    this.carregarUsuario();
  }

  carregarUsuario() {
    this.authService.getPerfil().subscribe({
      next: (res: any) => {
        if (res.status === 'success') {
          this.usuario = { ...res.dados, senha: '', confirmarSenha: '' };
          this.usuarioOriginal = { ...res.dados, senha: '', confirmarSenha: '' };
          this.cdr.detectChanges();
        }
      },
      error: (err) => {
        console.error('Erro:', err);
      },
    });
  }

  habilitarEdicao() {
    this.editando = true;
  }

  cancelar() {
    this.usuario = { ...this.usuarioOriginal };
    this.editando = false;
    this.cdr.detectChanges();
  }

  salvar() {
    this.loading = true;
    this.authService.atualizarPerfil(this.usuario).subscribe({
      next: (res: any) => {
        this.loading = false;
        this.toastService.success('Sucesso', 'Perfil atualizado com sucesso!');
        this.usuarioOriginal = { ...this.usuario };
        this.editando = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
        this.loading = false;
        this.toastService.error('Erro ao salvar', err.error?.message || 'Erro ao salvar perfil.');
      },
    });
  }

  sair() {
    localStorage.clear();
    this.router.navigate(['/login']);
  }

  abrirModalExclusao() {
    this.exibirModalExclusao = true;
    this.cdr.detectChanges();
  }

  fecharModalExclusao() {
    this.exibirModalExclusao = false;
    this.cdr.detectChanges();
  }

  confirmarExclusaoModal() {
    this.excluirPerfil();
  }

  excluirPerfil() {
    this.loading = true;
    this.authService.excluirPerfil().subscribe({
      next: (res: any) => {
        this.loading = false;
        this.exibirModalExclusao = false;
        this.toastService.success('Conta Excluída', 'Sua conta foi excluída com sucesso.');
        localStorage.clear();
        this.router.navigate(['/login']);
      },
      error: (err) => {
        this.loading = false;
        this.toastService.error('Erro ao excluir', err.error?.message || 'Erro ao excluir conta.');
        this.cdr.detectChanges();
      },
    });
  }
}
