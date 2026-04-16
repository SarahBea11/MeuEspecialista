import { Component } from '@angular/core';
import { AuthService } from '../services/auth';
import { Router } from '@angular/router';


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

  constructor(
    private authService: AuthService,
    private router: Router,
  ) {}

  logar() {
    this.authService.login(this.loginData).subscribe({
      next: (res) => {
        console.log('Sucesso!', res);
        alert('Login realizado!');
         localStorage.setItem('usuarioLogado', 'true');
        this.router.navigate(['/buscar']);
      },
      error: (err) => {
        console.error('Erro!', err);
        alert('E-mail ou senha incorretos.');
      },
    });
  }
}
