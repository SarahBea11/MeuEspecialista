import { Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { environment } from '../environments';
import { Medico, Favorito } from '../models/usuario.model';

@Injectable({
  providedIn: 'root',
})
export class MedicoService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  buscar(cidade: string, especialidade: string, termo: string = ''): Observable<Medico[]> {
    let params = new HttpParams()
      .set('cidade', cidade)
      .set('especialidade', especialidade)
      .set('termo', termo);

    return this.http.get<Medico[]>(`${this.apiUrl}buscar_medicos.php`, { params });
  }

  /** Toggle favoritar / desfavoritar um médico */
  favoritar(medicoUsuarioId: number): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}favoritar_medico.php`, { medico_usuario_id: medicoUsuarioId });
  }

  /** Altera preferência de notificação de um médico favoritado */
  alterarNotificacao(medicoUsuarioId: number, ativo: boolean): Observable<any> {
    return this.http.put<any>(`${this.apiUrl}favoritar_medico.php`, {
      medico_usuario_id: medicoUsuarioId,
      notificacoes_ativas: ativo ? 1 : 0,
    });
  }

  /** Retorna a lista de médicos favoritados pelo paciente logado */
  listarFavoritos(): Observable<Favorito[]> {
    return this.http.get<Favorito[]>(`${this.apiUrl}listar_favoritos.php`);
  }

  /** Verifica se o paciente logado favoritou um médico específico */
  verificarFavorito(medicoUsuarioId: number): Observable<{ favoritado: boolean; notificacoes_ativas: boolean }> {
    const params = new HttpParams().set('medico_usuario_id', medicoUsuarioId.toString());
    return this.http.get<{ favoritado: boolean; notificacoes_ativas: boolean }>(
      `${this.apiUrl}verificar_favorito.php`, { params }
    );
  }
}

