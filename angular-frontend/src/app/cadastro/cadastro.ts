import { Component } from '@angular/core';
import { FormsModule, NgForm } from '@angular/forms';
import { Router } from '@angular/router';
import { HttpClient, HttpClientModule } from '@angular/common/http';

@Component({
  selector: 'app-cadastro',
  standalone: true,
  imports: [FormsModule, HttpClientModule],
  templateUrl: './cadastro.html',
  styleUrls: ['./cadastro.css']
})
export class Cadastro {
  tipoUsuario: string = '';
  crm: string = '';
  especialidade: string = '';
  mensagemSucesso: boolean = false;

  constructor(private router: Router, private http: HttpClient) {}

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
      convenios: this.tipoUsuario === 'medico' ? [1] : undefined
    };

    this.http.post('http://localhost/MeuEspecialista/php-backend/api/cadastro.php', dados, {
      headers: { 'Content-Type': 'application/json' }
    }).subscribe({
      next: (res: any) => {
        console.log(res);
        alert(res.message);
        this.mensagemSucesso = true;
        form.resetForm();
        this.tipoUsuario = '';
        this.crm = '';
        this.especialidade = '';
      },
      error: (err) => {
        console.error(err);
        alert('Erro ao cadastrar: ' + (err.error?.message || err.statusText));
      }
    });
  }

  cancelar() {
    this.router.navigate(['/']);
  }

  limpar(form:any){

    form.resetForm();

    this.tipoUsuario ='';
    this.crm = '';
    this.especialidade = '';

  }
}
