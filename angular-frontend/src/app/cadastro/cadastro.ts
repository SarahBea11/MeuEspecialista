import { Component, OnInit } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
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
export class Cadastro implements OnInit {
  tipoUsuario: string = '';
  carregando: boolean = false;
  private apiUrl = `${environment.apiUrl}cadastro.php`;

  cidades: any[] = [];
  especialidades: any[] = [];
  convenios: any[] = [];

  constructor(
    private router: Router,
    private http: HttpClient,
    private toastService: ToastService,
  ) {}

  ngOnInit(): void {
    this.carregarListas();
  }

  carregarListas(): void {
    this.http.get<any>(`${environment.apiUrl}listar_cidades.php`).subscribe({
      next: (res) => {
        if (res.status === 'success') {
          this.cidades = res.dados;
        }
      }
    });

    this.http.get<any>(`${environment.apiUrl}listar_especialidades.php`).subscribe({
      next: (res) => {
        if (res.status === 'success') {
          this.especialidades = res.dados;
        }
      }
    });

    this.http.get<any>(`${environment.apiUrl}listar_convenios.php`).subscribe({
      next: (res) => {
        if (res.status === 'success') {
          // Mantém "Não conveniado" opcional se necessário, ou adiciona ao início se não houver
          this.convenios = res.dados;
        }
      }
    });
  }

  criarConta(form: NgForm) {
    // Verificação básica de senha
    if (!this.validarSenhaForte(form.value.senha)) {
      this.toastService.warning('Senha Fraca', 'A senha deve ter pelo menos 8 caracteres, incluir letras, números e símbolo.');
      return;
    }

    if (form.value.senha !== form.value.confirmaSenha) {
      this.toastService.warning('Senha Incorreta', 'As senhas não coincidem!');
      return;
    }

    if (this.tipoUsuario === 'paciente') {
      if (!this.validarCPF(form.value.cpf)) {
        this.toastService.warning('CPF Inválido', 'Por favor, insira um CPF válido de 11 dígitos.');
        return;
      }
    }

    if (this.tipoUsuario === 'medico') {
      if (!this.validarCRM(form.value.crm)) {
        this.toastService.warning('CRM Inválido', 'O CRM deve conter de 4 a 10 dígitos (Ex: 12345/SP ou 123456).');
        return;
      }
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
          if (res.token) {
            localStorage.setItem('token', res.token);
            localStorage.setItem('user_type', res.tipo);
            localStorage.setItem('user_name', res.nome || '');
            if (res.tipo === 'medico') {
              this.router.navigate(['/perfil']);
            } else {
              this.router.navigate(['/buscar']);
            }
          } else {
            this.router.navigate(['/login']);
          }
        },

        error: (err) => {
          this.carregando = false;
          console.error(err);
          this.toastService.errorFriendly('Erro ao cadastrar', err);
        },
      });
  }


  cancelar() {
    this.router.navigate(['/']);
  }

  private validarSenhaForte(senha: string): boolean {
    const regex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;
    return regex.test(senha);
  }

  private validarCPF(cpf: string): boolean {
    if (!cpf) return false;
    cpf = cpf.replace(/[^\d]+/g, '');
    if (cpf.length !== 11) return false;
    if (/^(\d)\1{10}$/.test(cpf)) return false;
    let soma = 0;
    let resto;
    for (let i = 1; i <= 9; i++) {
      soma = soma + parseInt(cpf.substring(i - 1, i)) * (11 - i);
    }
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.substring(9, 10))) return false;
    soma = 0;
    for (let i = 1; i <= 10; i++) {
      soma = soma + parseInt(cpf.substring(i - 1, i)) * (12 - i);
    }
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.substring(10, 11))) return false;
    return true;
  }

  private validarCRM(crm: string): boolean {
    if (!crm) return false;
    crm = crm.trim();
    const regex = /^\d{4,10}([-/ ]?[A-Za-z]{2})?$/;
    return regex.test(crm);
  }

  limpar(form: NgForm) {
    form.resetForm();
    this.tipoUsuario = '';
  }
}
