import { Router } from '@angular/router';
import { Component } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule, NgForm } from '@angular/forms';
import { HttpClient, HttpClientModule } from '@angular/common/http';

@Component({
  selector: 'app-cadastro',
  standalone: true,
  imports: [CommonModule, FormsModule, HttpClientModule, RouterModule],
  templateUrl: './cadastro.html',
  styleUrls: ['./cadastro.css'],
})
export class Cadastro {
  tipoUsuario: string = '';
  crm: string = '';
  especialidade: string = '';
  mensagemSucesso: boolean = false;

  cidades = [
    { id: 1, nome: 'Campinas' },
    { id: 2, nome: 'Indaiatuba' },
    { id: 3, nome: 'Itu' }
  ];

  especialidades = [
    { id: 1, nome: 'Cardiologia' },
    { id: 2, nome: 'Pediatria' },
    { id: 3, nome: 'Psiquiatria' }
  ];

  convenios = [
    { id: 1, nome: 'Não conveniado' },
    { id: 2, nome: 'Amil' },
    { id: 3, nome: 'Intermédica' },
    { id: 4, nome: 'Unimed' }
  ];

  constructor(
    private router: Router,
    private http: HttpClient,
  ) { }

  criarConta(form: NgForm) {
    const dados: any = {
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
      convenio_id: this.tipoUsuario === 'paciente' ? 1 : undefined,
      convenios: this.tipoUsuario === 'medico' ? [1] : undefined,
      
    };

    this.http
      .post('http://172.20.10.2/MeuEspecialista/php-backend/api/cadastro.php', dados, {
        headers: { 'Content-Type': 'application/json' },
      })
      .subscribe({
        next: (res: any) => {
          console.log(res);
          alert(res.message);
          this.mensagemSucesso = true;
          form.resetForm();
          this.tipoUsuario = '';
          this.crm = '';
          this.especialidade = '';
          this.router.navigate(['/login']);

        },
        error: (err) => {
          console.error(err);
          alert('Erro ao cadastrar: ' + (err.error?.message || err.statusText));
        },
      });
  }

  cancelar() {
    this.router.navigate(['/']);
  }

  limpar(form: any) {


    form.resetForm();

    this.tipoUsuario = '';
    this.crm = '';
    this.especialidade = '';
  }
}
