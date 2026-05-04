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
    tipo: '',
    cidade: '',
    endereco: '',
    telefone: '',
    convenio: '',
    cpf: '',
    crm: '',
    especialidade: '',
  };
  usuarioOriginal: any = {};
  editando: boolean = false;

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
          this.usuario = { ...res.dados };
          this.usuarioOriginal = { ...res.dados };

          this.cdr.detectChanges();

          console.log('Dados carregados com sucesso no F5');
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
    this.authService.atualizarPerfil(this.usuario).subscribe({
      next: (res: any) => {
        alert('Perfil atualizado!');
        this.usuarioOriginal = { ...this.usuario };
        this.editando = false;
        this.cdr.detectChanges();
      },
      error: (err) => {
        alert('Erro ao salvar');
      },
    });
  }

  sair() {
    localStorage.clear();
    this.router.navigate(['/login']);
  }
}
