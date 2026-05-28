import { Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../environments';
import { LoginResponse, PerfilResponse } from '../models/auth.model';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  login(dados: any): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.apiUrl}login.php`, dados);
  }

  getPerfil(): Observable<PerfilResponse> {
    return this.http.get<PerfilResponse>(`${this.apiUrl}perfil.php`);
  }

  atualizarPerfil(dados: any): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}atualizar_perfil.php`, dados);
  }

  excluirPerfil(): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}excluir_perfil.php`);
  }

  uploadFoto(file: File): Observable<any> {
    const formData = new FormData();
    formData.append('foto', file);
    return this.http.post<any>(`${this.apiUrl}upload_foto.php`, formData);
  }

  solicitarResetSenha(email: string): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}solicitar_reset.php`, { email });
  }

  redefinirSenha(token: string, nova_senha: string, confirmar_senha: string): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}redefinir_senha.php`, { token, nova_senha, confirmar_senha });
  }
}

