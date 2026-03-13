import { Component } from '@angular/core';
import { AuthService } from '../services/auth';
import { Router } from '@angular/router';

@Component({
  selector: 'app-login',
  standalone: false,
  templateUrl: './login.html',
  styleUrls: ['./login.css']
})
export class Login {
loginData = {
  email:'',
  password:''

  }

constructor(private authService: AuthService, private router: Router) {}

  logar() {
    this.authService.login(this.loginData).subscribe({
      next: (res) => {
        console.log('Sucesso!', res);
        alert('Login realizado!');
        this.router.navigate(['/cadastro']); // Redireciona após o login
      },
      error: (err) => {
        console.error('Erro!', err);
        alert('E-mail ou senha incorretos.');
      }
    });
  }
}
