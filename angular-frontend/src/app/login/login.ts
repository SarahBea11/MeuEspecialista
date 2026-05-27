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
        localStorage.setItem('token', res.token);
        localStorage.setItem('user_type', res.tipo);
        localStorage.setItem('user_name', res.nome || '');

        this.toastService.success('Bem-vindo(a)!', 'Login realizado com sucesso.');
        this.router.navigate(['/buscar']);
        this.loading = false;
      },
      error: (err) => {
        this.errorMessage = err.error?.message || 'Erro ao realizar login. Tente novamente.';
        this.toastService.error('Erro de Login', this.errorMessage);
        console.error('Login error:', err);
        this.loading = false;
      },
    });
  }
}
