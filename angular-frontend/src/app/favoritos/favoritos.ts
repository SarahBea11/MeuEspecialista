import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { MedicoService } from '../services/medico.service';
import { Favorito } from '../models/usuario.model';
import { environment } from '../environments';

@Component({
  selector: 'app-favoritos',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './favoritos.html',
  styleUrls: ['./favoritos.css'],
})
export class Favoritos implements OnInit {
  favoritos: Favorito[] = [];
  carregando: boolean = true;
  userName: string = '';
  removendoId: number | null = null;

  // Controle de modal
  medicoSelecionado: any = null;
  modalFavoritado: boolean = false;
  modalNotificacoesAtivas: boolean = false;
  favoritandoEmProgresso: boolean = false;

  constructor(
    private medicoService: MedicoService,
    private router: Router,
    private cdr: ChangeDetectorRef
  ) {
    this.userName = localStorage.getItem('user_name') || '';
  }

  ngOnInit(): void {
    this.carregarFavoritos();
  }

  carregarFavoritos(): void {
    this.carregando = true;
    this.medicoService.listarFavoritos().subscribe({
      next: (res) => {
        this.favoritos = res;
        this.carregando = false;
        this.cdr.detectChanges();
      },
      error: () => {
        this.carregando = false;
        this.cdr.detectChanges();
      }
    });
  }

  toggleNotificacao(fav: Favorito): void {
    const novoEstado = !fav.notificacoes_ativas;
    this.medicoService.alterarNotificacao(fav.medico_usuario_id, novoEstado).subscribe({
      next: () => {
        fav.notificacoes_ativas = novoEstado;
        this.cdr.detectChanges();
      }
    });
  }

  removerFavorito(fav: Favorito): void {
    if (this.removendoId === fav.medico_usuario_id) return;
    this.removendoId = fav.medico_usuario_id;

    this.medicoService.favoritar(fav.medico_usuario_id).subscribe({
      next: (res) => {
        if (!res.favoritado) {
          this.favoritos = this.favoritos.filter(f => f.medico_usuario_id !== fav.medico_usuario_id);
        }
        this.removendoId = null;
        this.cdr.detectChanges();
      },
      error: () => {
        this.removendoId = null;
      }
    });
  }

  abrirPerfil(fav: Favorito): void {
    this.medicoSelecionado = {
      id: fav.medico_usuario_id,
      nome: fav.nome,
      email: fav.email,
      crm: fav.crm,
      especialidade: fav.especialidade,
      cidade: fav.cidade,
      telefone: fav.telefone,
      endereco: fav.endereco,
      foto: fav.foto,
      atualizado_em: fav.atualizado_em
    };
    this.modalFavoritado = true;
    this.modalNotificacoesAtivas = fav.notificacoes_ativas;
    this.cdr.detectChanges();
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

        // Sincroniza com a lista de favoritos
        if (!res.favoritado) {
          this.favoritos = this.favoritos.filter(f => f.medico_usuario_id !== this.medicoSelecionado?.id);
          this.fecharPerfil();
        } else {
          const index = this.favoritos.findIndex(f => f.medico_usuario_id === this.medicoSelecionado?.id);
          if (index !== -1) {
            this.favoritos[index].notificacoes_ativas = true;
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

  toggleNotificacaoModal(): void {
    if (!this.medicoSelecionado?.id) return;
    const novoEstado = !this.modalNotificacoesAtivas;

    this.medicoService.alterarNotificacao(this.medicoSelecionado.id, novoEstado).subscribe({
      next: () => {
        this.modalNotificacoesAtivas = novoEstado;

        // Sincroniza com a lista de favoritos
        const index = this.favoritos.findIndex(f => f.medico_usuario_id === this.medicoSelecionado?.id);
        if (index !== -1) {
          this.favoritos[index].notificacoes_ativas = novoEstado;
        }

        this.cdr.detectChanges();
      },
      error: () => { /* silencioso */ }
    });
  }

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

  sair(): void {
    localStorage.clear();
    this.router.navigate(['/login']);
  }
}
