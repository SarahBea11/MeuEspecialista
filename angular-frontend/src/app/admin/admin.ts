import { Router } from '@angular/router';
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { RouterModule } from '@angular/router';

import { environment } from '../environments';
import { ToastService } from '../services/toast';
import { AuthService } from '../services/auth';

@Component({
  selector: 'app-admin',
  standalone: true,
  imports: [CommonModule, FormsModule, HttpClientModule, RouterModule],
  templateUrl: './admin.html',
  styleUrls: ['./admin.css'],
})
export class Admin implements OnInit {
  abaAtiva: 'cidades' | 'especialidades' | 'convenios' | 'perfil' | 'administradores' = 'cidades';

  cidades: any[] = [];
  especialidades: any[] = [];
  convenios: any[] = [];
  administradores: any[] = [];

  // Modelos para inputs
  novoNome: string = '';
  editId: number = 0;

  novoAdmin: any = { nome: '', email: '', senha: '' };

  usuario: any = {
    nome: '',
    email: '',
    senha: '',
    confirmarSenha: ''
  };

  carregando: boolean = false;

  confirmacaoExclusao: any = {
    visivel: false,
    item: null,
    tipo: null
  };

  private crudUrl = `${environment.apiUrl}admin_crud.php`;

  constructor(
    private http: HttpClient,
    private toastService: ToastService,
    private authService: AuthService,
    private router: Router
  ) {}

  ngOnInit() {
    // Verificar se é admin
    const userType = localStorage.getItem('user_type');
    if (userType !== 'admin') {
      this.toastService.error('Acesso negado', 'Área exclusiva para administradores.');
      this.router.navigate(['/']);
      return;
    }
    this.carregarDados();
    this.carregarPerfil();
    this.carregarAdministradores();
  }

  alterarAba(aba: 'cidades' | 'especialidades' | 'convenios' | 'perfil' | 'administradores') {
    this.abaAtiva = aba;
    this.novoNome = '';
    this.editId = 0;
  }

  carregarDados() {
    this.carregando = true;

    // Cidades
    this.http.get<any>(`${environment.apiUrl}listar_cidades.php`).subscribe({
      next: (res) => {
        this.cidades = res.dados || [];
      },
      error: (err) => this.toastService.errorFriendly('Erro', err, 'Erro ao carregar cidades.')
    });

    // Especialidades
    this.http.get<any>(`${environment.apiUrl}listar_especialidades.php`).subscribe({
      next: (res) => {
        this.especialidades = res.dados || [];
      },
      error: (err) => this.toastService.errorFriendly('Erro', err, 'Erro ao carregar especialidades.')
    });

    // Convênios
    this.http.get<any>(`${environment.apiUrl}listar_convenios.php`).subscribe({
      next: (res) => {
        this.convenios = res.dados || [];
        this.carregando = false;
      },
      error: (err) => {
        this.toastService.errorFriendly('Erro', err, 'Erro ao carregar convênios.');
        this.carregando = false;
      }
    });
  }

  carregarAdministradores() {
    // Busca simples dos usuários com tipo=admin
    this.http.get<any>(`${environment.apiUrl}listar_administradores.php`).subscribe({
      next: (res) => {
        this.administradores = res.dados || [];
      },
      error: (err) => {
        this.toastService.errorFriendly('Erro', err, 'Erro ao carregar administradores.');
      }
    });
  }

  carregarPerfil() {
    this.authService.getPerfil().subscribe({
      next: (res: any) => {
        if (res.status === 'success') {
          this.usuario = { ...res.dados, senha: '', confirmarSenha: '' };
        }
      },
      error: (err) => this.toastService.errorFriendly('Erro', err, 'Erro ao carregar perfil do administrador.')
    });
  }

  iniciarEdicao(item: any) {
    this.editId = item.id;
    this.novoNome = item.nome;
  }

  cancelarEdicao() {
    this.editId = 0;
    this.novoNome = '';
  }

  salvarItem() {
    if (!this.novoNome.trim()) {
      this.toastService.warning('Aviso', 'O nome não pode estar vazio.');
      return;
    }

    let action = '';
    if (this.abaAtiva === 'cidades') action = 'save_cidade';
    if (this.abaAtiva === 'especialidades') action = 'save_especialidade';
    if (this.abaAtiva === 'convenios') action = 'save_convenio';

    const payload = {
      id: this.editId,
      nome: this.novoNome
    };

    this.http.post<any>(`${this.crudUrl}?action=${action}`, payload).subscribe({
      next: (res) => {
        this.toastService.success('Sucesso', res.message || 'Operação realizada com sucesso!');
        this.novoNome = '';
        this.editId = 0;
        this.carregarDados();
          this.carregarAdministradores();
      },
      error: (err) => this.toastService.errorFriendly('Erro ao salvar', err)
    });
  }

  criarAdmin() {
    if (!this.novoAdmin.nome || !this.novoAdmin.email) {
      this.toastService.warning('Aviso', 'Nome e e-mail são obrigatórios.');
      return;
    }
    if (this.novoAdmin.senha && this.novoAdmin.senha.length < 6) {
      this.toastService.warning('Aviso', 'Senha muito curta. Use ao menos 6 caracteres.');
      return;
    }

    this.carregando = true;
    this.http.post<any>(`${this.crudUrl}?action=create_admin`, this.novoAdmin).subscribe({
      next: (res) => {
        this.carregando = false;
        this.toastService.success('Administrador criado', res.message || 'Administrador criado com sucesso!');
        // Exibe senha temporária se retornada
        if (res.senha_temp) {
          this.toastService.info('Senha temporária', `Senha: ${res.senha_temp}`);
        }
        this.novoAdmin = { nome: '', email: '', senha: '' };
        this.carregarAdministradores();
      },
      error: (err) => {
        this.carregando = false;
        this.toastService.errorFriendly('Erro ao criar', err);
      }
    });
  }

  removerItem(item: any) {
    let tipo: 'cidade' | 'especialidade' | 'convenio' | null = null;
    if (this.abaAtiva === 'cidades') tipo = 'cidade';
    if (this.abaAtiva === 'especialidades') tipo = 'especialidade';
    if (this.abaAtiva === 'convenios') tipo = 'convenio';

    this.confirmacaoExclusao = {
      visivel: true,
      item: item,
      tipo: tipo
    };
  }

  fecharConfirmacao() {
    this.confirmacaoExclusao = {
      visivel: false,
      item: null,
      tipo: null
    };
  }

  confirmarExclusao() {
    const item = this.confirmacaoExclusao.item;
    const tipo = this.confirmacaoExclusao.tipo;
    if (!item || !tipo) return;

    let action = '';
    if (tipo === 'cidade') action = 'delete_cidade';
    if (tipo === 'especialidade') action = 'delete_especialidade';
    if (tipo === 'convenio') action = 'delete_convenio';

    this.http.post<any>(`${this.crudUrl}?action=${action}`, { id: item.id }).subscribe({
      next: (res) => {
        this.toastService.success('Sucesso', res.message || 'Removido com sucesso!');
        this.carregarDados();
        this.fecharConfirmacao();
      },
      error: (err) => {
        this.toastService.errorFriendly('Erro ao remover', err);
        this.fecharConfirmacao();
      }
    });
  }

  salvarPerfil() {
    if (!this.usuario.nome || !this.usuario.email) {
      this.toastService.warning('Aviso', 'Nome e E-mail são obrigatórios.');
      return;
    }

    this.carregando = true;
    this.authService.atualizarPerfil(this.usuario).subscribe({
      next: (res: any) => {
        this.carregando = false;
        this.toastService.success('Sucesso', 'Perfil atualizado com sucesso!');
        localStorage.setItem('user_name', this.usuario.nome);
        this.carregarPerfil();
      },
      error: (err) => {
        this.carregando = false;
        this.toastService.errorFriendly('Erro ao salvar', err);
      }
    });
  }

  sair() {
    localStorage.clear();
    this.router.navigate(['/login']);
  }
}
