import { Router } from '@angular/router';
import { Component } from '@angular/core';
import { AuthService } from '../services/auth';
import { ToastService } from '../services/toast';

@Component({
  selector: 'app-login',
  standalone: false,
  templateUrl: './login.html',
  styleUrls: ['./login.css'],
})
export class Login {
  loginData = {
    email: '',
    senha: '',
  };
  errorMessage: string = '';
  loading: boolean = false;

  constructor(
    private authService: AuthService,
    private router: Router,
    private toastService: ToastService,
  ) {}

  logar() {
    this.errorMessage = '';
    this.loading = true;
    this.authService.login(this.loginData).subscribe({
      next: (res) => {
        this.loading = false;
        localStorage.setItem('token', res.token);
        localStorage.setItem('user_type', res.tipo);
        localStorage.setItem('user_name', res.nome || '');

        this.toastService.success('Bem-vindo(a)!', 'Login realizado com sucesso.');
        if (res.tipo === 'medico') {
          this.router.navigate(['/perfil']);
        } else if (res.tipo === 'admin') {
          this.router.navigate(['/admin']);
        } else {
          this.router.navigate(['/buscar']);
        }
      },
      error: (err) => {
        this.loading = false;
        let erroFriendly = 'Erro ao realizar login. Tente novamente.';
        if (err) {
          if (err.error) {
            if (typeof err.error === 'string') {
              if (err.error.includes('<br') || err.error.includes('<b>') || err.error.trim().startsWith('<')) {
                erroFriendly = 'Erro interno no servidor (formato inválido).';
              } else {
                erroFriendly = err.error;
              }
            } else if (err.error.message) {
              erroFriendly = err.error.message;
            }
          } else if (err.message) {
            erroFriendly = err.message;
          }
        }
        this.errorMessage = erroFriendly;
        this.toastService.error('Erro de Login', this.errorMessage);
        console.error('Login error:', err);
      },

    });
  }
}
