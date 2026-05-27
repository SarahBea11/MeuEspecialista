import { Router } from '@angular/router';
import { Component } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule, NgForm } from '@angular/forms';
import { HttpClient, HttpClientModule } from '@angular/common/http';

import { environment } from '../environments';
import { ToastService } from '../services/toast';

@Component({
  selector: 'app-cadastro',
  standalone: true,
  imports: [CommonModule, FormsModule, HttpClientModule, RouterModule],
  templateUrl: './cadastro.html',
  styleUrls: ['./cadastro.css'],
})
export class Cadastro {
  tipoUsuario: string = '';
  carregando: boolean = false;
  private apiUrl = `${environment.apiUrl}cadastro.php`;

  cidades = [
    { id: 1, nome: 'Campinas' },
    { id: 2, nome: 'Indaiatuba' },
    { id: 3, nome: 'Itu' },
  ];

  especialidades = [
    { id: 1, nome: 'Cardiologia' },
    { id: 2, nome: 'Pediatria' },
    { id: 3, nome: 'Psiquiatria' },
  ];

  convenios = [
    { id: 1, nome: 'Não conveniado' },
    { id: 2, nome: 'Amil' },
    { id: 3, nome: 'Intermédica' },
    { id: 4, nome: 'Unimed' },
  ];

  constructor(
    private router: Router,
    private http: HttpClient,
    private toastService: ToastService,
  ) {}

  criarConta(form: NgForm) {
    // Verificação básica de senha
    if (form.value.senha !== form.value.confirmaSenha) {
      this.toastService.warning('Senha Incorreta', 'As senhas não coincidem!');
      return;
    }

    const dados = {
      tipo: this.tipoUsuario,
      nome: form.value.nome,
      email: form.value.email,
      senha: form.value.senha,
      cidade: form.value.cidade,
      endereco: form.value.endereco,
      telefone: form.value.telefone,
      especialidade: form.value.especialidade,
      crm: form.value.crm,
      cpf: form.value.cpf,
      convenio_id: form.value.convenio, // Pega o ID selecionado no select
    };

    this.carregando = true;
    this.http
      .post(this.apiUrl, dados)
      .subscribe({
        next: (res: any) => {
          this.carregando = false;
          this.toastService.success('Sucesso!', res.message || 'Cadastro realizado com sucesso!');
          this.router.navigate(['/login']);
        },
        error: (err) => {
          this.carregando = false;
          console.error(err);
          const erroMsg = err.error?.message || 'Verifique a conexão com o servidor.';
          this.toastService.error('Erro ao cadastrar', erroMsg);
        },
      });
  }

  cancelar() {
    this.router.navigate(['/']);
  }

  limpar(form: NgForm) {
    form.resetForm();
    this.tipoUsuario = '';
  }
}
