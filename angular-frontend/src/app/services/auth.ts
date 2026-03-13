import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = 'http://localhost/MeuEspecialista/php-backend/api/login.php';

  constructor(private http: HttpClient) { }

  login(dados: any): Observable<any> {
    return this.http.post(this.apiUrl, dados);
  }
}
