import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthService } from '../services/auth';
import { ToastService } from '../services/toast';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-redefinir-senha',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './redefinir-senha.html',
  styleUrl: './redefinir-senha.css',
})
export class RedefinirSenha implements OnInit {
  token = '';
  novaSenha = '';
  confirmarSenha = '';
  carregando = false;
  concluido = false;
  tokenInvalido = false;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private authService: AuthService,
    private toast: ToastService
  ) {}

  ngOnInit() {
    this.token = this.route.snapshot.queryParamMap.get('token') || '';
    if (!this.token) {
      this.tokenInvalido = true;
    }
  }

  redefinir() {
    if (!this.novaSenha || !this.confirmarSenha) {
      this.toast.show('error', 'Atenção', 'Preencha todos os campos.');
      return;
    }
    if (this.novaSenha.length < 6) {
      this.toast.show('error', 'Senha Fraca', 'A senha deve ter pelo menos 6 caracteres.');
      return;
    }
    if (this.novaSenha !== this.confirmarSenha) {
      this.toast.show('error', 'Atenção', 'As senhas não coincidem.');
      return;
    }

    this.carregando = true;
    this.authService.redefinirSenha(this.token, this.novaSenha, this.confirmarSenha).subscribe({
      next: () => {
        this.concluido = true;
        this.carregando = false;
        this.toast.show('success', 'Pronto!', 'Senha redefinida com sucesso!');
        setTimeout(() => this.router.navigate(['/login']), 3000);
      },
      error: (err) => {
        this.carregando = false;
        const msg = err?.error?.message || 'Token inválido ou expirado. Solicite um novo link.';
        this.toast.show('error', 'Erro', msg);
        if (err.status === 400) this.tokenInvalido = true;
      }
    });
  }
}
