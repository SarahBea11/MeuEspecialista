import { Component } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule, NgForm } from '@angular/forms';
import { HttpClient, HttpClientModule } from '@angular/common/http';

import { environment } from '../environments';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, FormsModule, HttpClientModule, RouterModule],
  templateUrl: './home.html',
  styleUrl: './home.css',
})
export class Home {
  modalAberto: boolean = false;
  tipoUsuario: string = '';
  carregando: boolean = false;

  // Campos de endereço
  cepValue: string = '';
  cepCarregando: boolean = false;
  cepErro: string = '';
  enderecoLogradouro: string = '';
  enderecoBairro: string = '';
  enderecoNumero: string = '';
  enderecoComplemento: string = '';
  enderecoCidade: string = '';
  enderecoUF: string = '';

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
  ) {}

  abrirModal(): void {
    this.modalAberto = true;
  }

  fecharModal(): void {
    this.modalAberto = false;
    this.tipoUsuario = '';
    this.limparEndereco();
  }

  limparEndereco(): void {
    this.cepValue = '';
    this.cepErro = '';
    this.enderecoLogradouro = '';
    this.enderecoBairro = '';
    this.enderecoNumero = '';
    this.enderecoComplemento = '';
    this.enderecoCidade = '';
    this.enderecoUF = '';
  }

  buscarCep(cep: string): void {
    const cepLimpo = cep.replace(/\D/g, '');
    this.cepErro = '';

    if (cepLimpo.length !== 8) {
      return;
    }

    this.cepCarregando = true;
    this.enderecoLogradouro = '';
    this.enderecoBairro = '';
    this.enderecoCidade = '';
    this.enderecoUF = '';

    this.http.get<any>(`https://viacep.com.br/ws/${cepLimpo}/json/`).subscribe({
      next: (res) => {
        this.cepCarregando = false;
        if (res.erro) {
          this.cepErro = 'CEP não encontrado.';
          return;
        }
        this.enderecoLogradouro = res.logradouro || '';
        this.enderecoBairro = res.bairro || '';
        this.enderecoCidade = res.localidade || '';
        this.enderecoUF = res.uf || '';
      },
      error: () => {
        this.cepCarregando = false;
        this.cepErro = 'Erro ao buscar o CEP. Verifique sua conexão.';
      },
    });
  }

  get enderecoCompleto(): string {
    const partes = [
      this.enderecoLogradouro,
      this.enderecoNumero ? `nº ${this.enderecoNumero}` : '',
      this.enderecoComplemento,
      this.enderecoBairro,
      this.enderecoCidade ? `${this.enderecoCidade}/${this.enderecoUF}` : '',
    ].filter(p => p.trim() !== '');
    return partes.join(', ');
  }

  criarConta(form: NgForm) {
    if (form.value.senha !== form.value.confirmaSenha) {
      alert('As senhas não coincidem!');
      return;
    }

    const dados = {
      tipo: this.tipoUsuario,
      nome: form.value.nome,
      email: form.value.email,
      senha: form.value.senha,
      cidade: this.enderecoCidade || form.value.cidade,
      endereco: this.enderecoCompleto,
      telefone: form.value.telefone,
      especialidade: form.value.especialidade,
      crm: form.value.crm,
      cpf: form.value.cpf,
      convenio_id: form.value.convenio,
    };

    this.carregando = true;
    this.http.post(this.apiUrl, dados).subscribe({
      next: (res: any) => {
        this.carregando = false;
        alert(res.message);
        this.fecharModal();
        this.router.navigate(['/login']);
      },
      error: (err) => {
        this.carregando = false;
        console.error(err);
        alert('Erro ao cadastrar: ' + (err.error?.message || 'Verifique a conexão com o servidor.'));
      },
    });
  }

  limpar(form: NgForm) {
    form.resetForm();
    this.tipoUsuario = '';
    this.limparEndereco();
  }
}
