import { Router } from '@angular/router';
import { AuthService } from '../services/auth';
import { Component, OnInit, ChangeDetectorRef } from '@angular/core';

@Component({
  selector: 'app-perfil',
  standalone: false,
  templateUrl: './perfil.html',
  styleUrl: './perfil.css',
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
  };
  usuarioOriginal: any = {};
  editando: boolean = false;
  loading: boolean = false;

  constructor(
    private authService: AuthService,
    private router: Router,
    private cdr: ChangeDetectorRef,
  ) {}

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
        alert('Perfil atualizado com sucesso!');
        this.usuarioOriginal = { ...this.usuario };
        this.editando = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
        this.loading = false;
        alert(err.error?.message || 'Erro ao salvar perfil.');
      },
    });
  }

  sair() {
    localStorage.clear();
    this.router.navigate(['/login']);
  }
}
