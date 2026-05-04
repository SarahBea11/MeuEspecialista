import { Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private apiUrl = 'http://localhost/MeuEspecialista/php-backend/api/login.php';

  constructor(private http: HttpClient) {}

  login(dados: any): Observable<any> {
    return this.http.post(this.apiUrl, dados);
  }

  getPerfil() {
    return this.http.get('http://localhost/MeuEspecialista/php-backend/api/perfil.php');
  }
  atualizarPerfil(dados: any) {
    return this.http.post(
      'http://localhost/MeuEspecialista/php-backend/api/atualizar_perfil.php',
      dados,
    );
  }
}
