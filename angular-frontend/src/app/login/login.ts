import { Router } from '@angular/router';
import { Component } from '@angular/core';
import { AuthService } from '../services/auth';

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
  ) {}

  logar() {
    this.errorMessage = '';
    this.loading = true;
    this.authService.login(this.loginData).subscribe({
      next: (res) => {
        localStorage.setItem('token', res.token);
        localStorage.setItem('user_type', res.tipo);
        localStorage.setItem('user_name', res.nome || '');

        this.router.navigate(['/buscar']);
        this.loading = false;
      },
      error: (err) => {
        this.errorMessage = err.error?.message || 'Erro ao realizar login. Tente novamente.';
        console.error('Login error:', err);
        this.loading = false;
      },
    });
  }
}
