import { Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LoginResponse, PerfilResponse } from '../models/auth.model';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private apiUrl = 'http://localhost/MeuEspecialista/php-backend/api/';

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
}
