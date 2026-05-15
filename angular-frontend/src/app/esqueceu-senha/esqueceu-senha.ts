import { Component } from '@angular/core';

@Component({
  selector: 'app-esqueceu-senha',
  standalone: false,
  templateUrl: './esqueceu-senha.html',
  styleUrl: './esqueceu-senha.css',
})
export class EsqueceuSenha {

  email = '';
  mensagem = '';

  enviar() {
    if (!this.email) {
      this.mensagem = 'Digite um e-mail válido';
      return;
    }

    this.mensagem = 'Se o e-mail existir, você receberá instruções';
  }
}
