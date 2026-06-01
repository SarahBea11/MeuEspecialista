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
    if (this.usuario.tipo === 'paciente') {
      if (!this.validarCPF(this.usuario.cpf)) {
        this.toastService.warning('CPF Inválido', 'Por favor, insira um CPF válido de 11 dígitos.');
        return;
      }
    }

    if (this.usuario.tipo === 'medico') {
      if (!this.validarCRM(this.usuario.crm)) {
        this.toastService.warning('CRM Inválido', 'O CRM deve conter de 4 a 10 dígitos (Ex: 12345/SP ou 123456).');
        return;
      }
    }

    this.loading = true;
    this.authService.atualizarPerfil(this.usuario).subscribe({
      next: (res: any) => {
        this.loading = false;
        this.toastService.success('Sucesso', 'Perfil updated successfully!');
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

  private validarCPF(cpf: string): boolean {
    if (!cpf) return false;
    cpf = cpf.replace(/[^\d]+/g, '');
    if (cpf.length !== 11) return false;
    if (/^(\d)\1{10}$/.test(cpf)) return false;
    let soma = 0;
    let resto;
    for (let i = 1; i <= 9; i++) {
      soma = soma + parseInt(cpf.substring(i - 1, i)) * (11 - i);
    }
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.substring(9, 10))) return false;
    soma = 0;
    for (let i = 1; i <= 10; i++) {
      soma = soma + parseInt(cpf.substring(i - 1, i)) * (12 - i);
    }
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.substring(10, 11))) return false;
    return true;
  }

  private validarCRM(crm: string): boolean {
    if (!crm) return false;
    crm = crm.trim();
    const regex = /^\d{4,10}([-/ ]?[A-Za-z]{2})?$/;
    return regex.test(crm);
  }
}
