import { Component } from '@angular/core';
import { AuthService } from '../services/auth';
import { ToastService } from '../services/toast';

@Component({
  selector: 'app-esqueceu-senha',
  standalone: false,
  templateUrl: './esqueceu-senha.html',
  styleUrl: './esqueceu-senha.css',
})
export class EsqueceuSenha {
  email = '';
  carregando = false;
  enviado = false;

  constructor(private authService: AuthService, private toast: ToastService) {}

  enviar() {
    if (!this.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)) {
      this.toast.show('error', 'Atenção', 'Digite um e-mail válido.');
      return;
    }

    this.carregando = true;
    this.authService.solicitarResetSenha(this.email).subscribe({
      next: () => {
        this.enviado = true;
        this.carregando = false;
        this.toast.show('success', 'E-mail Enviado', 'Verifique sua caixa de entrada.');
      },
      error: () => {
        this.carregando = false;
        this.toast.show('error', 'Erro', 'Erro ao enviar. Tente novamente.');
      }
    });
  }
}
