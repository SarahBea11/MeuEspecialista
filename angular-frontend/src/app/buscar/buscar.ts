import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { MedicoService } from '../services/medico.service';
import { Medico } from '../models/usuario.model';
import { environment } from '../environments';
import { HttpClient, HttpClientModule } from '@angular/common/http';

@Component({
  selector: 'app-buscar',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule, HttpClientModule],
  templateUrl: './buscar.html',
  styleUrls: ['./buscar.css'],
})
export class Buscar implements OnInit {
  especialidades: string[] = [];
  cidades: string[] = [];
  convenios: string[] = [];

  cidadeSelecionada: string = '';
  convenioSelecionado: string = '';
  especialidadeSelecionada: string = '';

  resultados: Medico[] = [];
  termoBusca: string = '';
  userName: string = '';
  userTipo: string = '';
  carregando: boolean = false;
  medicoSelecionado: Medico | null = null;

  // Controle de favorito do modal atual
  modalFavoritado: boolean = false;
  modalNotificacoesAtivas: boolean = false;
  favoritandoEmProgresso: boolean = false;

  obterFotoUrl(foto: string): string {
    if (!foto) return '';
    const uploadsBase = environment.apiUrl.replace('/api/', '/uploads/');
    return `${uploadsBase}${foto}`;
  }

  formatarData(dataStr: string): string {
    if (!dataStr) return '';
    try {
      const data = new Date(dataStr.replace(' ', 'T'));
      return data.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    } catch {
      return dataStr;
    }
  }

  constructor(
    private medicoService: MedicoService,
    private router: Router,
    private cdr: ChangeDetectorRef,
    private http: HttpClient,
  ) {
    this.userName = localStorage.getItem('user_name') || '';
    this.userTipo = localStorage.getItem('user_type') || '';
  }

  get isPaciente(): boolean {
    return this.userTipo === 'paciente';
  }

  abrirPerfil(medico: Medico): void {
    this.medicoSelecionado = medico;
    this.modalFavoritado = !!medico.favoritado;
    this.modalNotificacoesAtivas = !!medico.notificacoes_ativas;
    this.cdr.detectChanges();

    // Se for paciente, verificar se já favoritou no banco para garantir
    if (this.isPaciente && medico.id) {
      this.medicoService.verificarFavorito(medico.id).subscribe({
        next: (res) => {
          this.modalFavoritado = res.favoritado;
          this.modalNotificacoesAtivas = res.notificacoes_ativas;
          
          // Sincroniza com a lista de resultados caso tenha mudado
          const index = this.resultados.findIndex(m => m.id === medico.id);
          if (index !== -1) {
            this.resultados[index].favoritado = res.favoritado;
            this.resultados[index].notificacoes_ativas = res.notificacoes_ativas;
          }
          this.cdr.detectChanges();
        },
        error: () => { /* silencioso */ }
      });
    }
  }

  fecharPerfil(): void {
    this.medicoSelecionado = null;
    this.cdr.detectChanges();
  }

  toggleFavoritar(): void {
    if (!this.medicoSelecionado?.id || this.favoritandoEmProgresso) return;
    this.favoritandoEmProgresso = true;

    this.medicoService.favoritar(this.medicoSelecionado.id).subscribe({
      next: (res) => {
        this.modalFavoritado = res.favoritado;
        if (res.favoritado) {
          this.modalNotificacoesAtivas = true;
        }

        // Sincroniza com a lista de resultados
        const index = this.resultados.findIndex(m => m.id === this.medicoSelecionado?.id);
        if (index !== -1) {
          this.resultados[index].favoritado = res.favoritado;
          if (res.favoritado) {
            this.resultados[index].notificacoes_ativas = true;
          }
        }

        this.favoritandoEmProgresso = false;
        this.cdr.detectChanges();
      },
      error: () => {
        this.favoritandoEmProgresso = false;
      }
    });
  }

  toggleNotificacao(): void {
    if (!this.medicoSelecionado?.id) return;
    const novoEstado = !this.modalNotificacoesAtivas;

    this.medicoService.alterarNotificacao(this.medicoSelecionado.id, novoEstado).subscribe({
      next: () => {
        this.modalNotificacoesAtivas = novoEstado;

        // Sincroniza com a lista de resultados
        const index = this.resultados.findIndex(m => m.id === this.medicoSelecionado?.id);
        if (index !== -1) {
          this.resultados[index].notificacoes_ativas = novoEstado;
        }

        this.cdr.detectChanges();
      },
      error: () => { /* silencioso */ }
    });
  }

  ngOnInit(): void {
    this.carregarListas();
    this.buscarMedicos();
  }

  carregarListas() {
    this.http.get<any>(`${environment.apiUrl}listar_cidades.php`).subscribe({
      next: (res) => {
        if (res.status === 'success') {
          this.cidades = res.dados.map((c: any) => c.nome);
          this.cdr.detectChanges();
        }
      }
    });
    this.http.get<any>(`${environment.apiUrl}listar_especialidades.php`).subscribe({
      next: (res) => {
        if (res.status === 'success') {
          this.especialidades = res.dados.map((e: any) => e.nome);
          this.cdr.detectChanges();
        }
      }
    });
    this.http.get<any>(`${environment.apiUrl}listar_convenios.php`).subscribe({
      next: (res) => {
        if (res.status === 'success') {
          this.convenios = res.dados.map((c: any) => c.nome);
          this.cdr.detectChanges();
        }
      }
    });
  }

  buscarMedicos() {
    this.carregando = true;
    this.medicoService
      .buscar(this.cidadeSelecionada, this.especialidadeSelecionada, this.termoBusca)
      .subscribe({
        next: (res) => {
          this.resultados = res.map((m: any) => ({
            ...m,
            favoritado: m.favoritado == 1,
            notificacoes_ativas: m.notificacoes_ativas == 1
          }));
          this.carregando = false;
          this.cdr.detectChanges();
        },
        error: (err) => {
          console.error('Erro na busca de médicos:', err);
          this.carregando = false;
          this.cdr.detectChanges();
        },
      });
  }

  sair() {
    localStorage.clear();
    this.router.navigate(['/login']);
  }
}

