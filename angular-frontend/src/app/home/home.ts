import { Component } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule, NgForm } from '@angular/forms';
import { HttpClient, HttpClientModule } from '@angular/common/http';

import { environment } from '../environments';
import { ToastService } from '../services/toast';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, FormsModule, HttpClientModule, RouterModule],
  templateUrl: './home.html',
  styleUrls: ['./home.css'],
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

  // Campos de CPF
  cpfFormatado: string = '';
  cpfErro: string = '';

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

  abrirModal(): void {
    this.modalAberto = true;
  }

  fecharModal(): void {
    this.modalAberto = false;
    this.tipoUsuario = '';
    this.cpfFormatado = '';
    this.cpfErro = '';
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

  // ---------- CPF ----------
  onCpfInput(event: Event): void {
    const el = event.target as HTMLInputElement;
    const raw = el.value.replace(/\D/g, '').slice(0, 11);

    // Aplica máscara 000.000.000-00
    let masked = raw;
    if (raw.length > 9) {
      masked = `${raw.slice(0,3)}.${raw.slice(3,6)}.${raw.slice(6,9)}-${raw.slice(9)}`;
    } else if (raw.length > 6) {
      masked = `${raw.slice(0,3)}.${raw.slice(3,6)}.${raw.slice(6)}`;
    } else if (raw.length > 3) {
      masked = `${raw.slice(0,3)}.${raw.slice(3)}`;
    }

    this.cpfFormatado = masked;
    el.value = masked;

    if (raw.length === 11) {
      this.cpfErro = this.validarCpf(raw) ? '' : 'CPF inválido. Verifique os números digitados.';
    } else {
      this.cpfErro = '';
    }
  }

  private validarCpf(cpf: string): boolean {
    // Rejeita sequências repetidas (ex: 111.111.111-11)
    if (/^(\d)\1{10}$/.test(cpf)) return false;

    const calcDigit = (slice: string, factor: number): number => {
      const sum = slice.split('').reduce((acc, d, i) => acc + parseInt(d) * (factor - i), 0);
      const rem = (sum * 10) % 11;
      return rem >= 10 ? 0 : rem;
    };

    const d1 = calcDigit(cpf.slice(0, 9), 10);
    const d2 = calcDigit(cpf.slice(0, 10), 11);
    return d1 === parseInt(cpf[9]) && d2 === parseInt(cpf[10]);
  }

  private validarSenhaForte(senha: string): boolean {
    const regex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;
    return regex.test(senha);
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
          this.toastService.warning('CEP não encontrado', 'Verifique o número digitado.');
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
        this.toastService.error('Erro de CEP', 'Erro ao buscar o CEP. Verifique sua conexão.');
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
    if (!this.validarSenhaForte(form.value.senha)) {
      this.toastService.warning('Senha Fraca', 'A senha deve ter pelo menos 8 caracteres, incluir letras, números e símbolo.');
      return;
    }

    if (form.value.senha !== form.value.confirmaSenha) {
      this.toastService.warning('Senha Incorreta', 'As senhas não coincidem!');
      return;
    }

    // Bloqueia CPF inválido
    if (this.tipoUsuario === 'paciente') {
      const rawCpf = this.cpfFormatado.replace(/\D/g, '');
      if (!this.validarCpf(rawCpf)) {
        this.cpfErro = 'CPF inválido. Verifique os números digitados.';
        this.toastService.warning('CPF Inválido', 'Informe um CPF válido antes de continuar.');
        return;
      }
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
      cpf: this.cpfFormatado,
      convenio_id: form.value.convenio,
    };

    this.carregando = true;
    this.http.post(this.apiUrl, dados).subscribe({
      next: (res: any) => {
        this.carregando = false;
        this.toastService.success('Sucesso!', res.message || 'Cadastro realizado com sucesso!');
        this.fecharModal();
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

  limpar(form: NgForm) {
    form.resetForm();
    this.tipoUsuario = '';
    this.cpfFormatado = '';
    this.cpfErro = '';
    this.limparEndereco();
  }
}
