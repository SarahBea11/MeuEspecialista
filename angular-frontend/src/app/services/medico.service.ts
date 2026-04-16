import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class MedicoService {

  private apiUrl = 'http://localhost/MeuEspecialista/php-backend/api/buscar_medicos.php';

  constructor(private http: HttpClient) {}

  buscar(cidade: string, especialidade: string): Observable<any> {
    return this.http.get(`${this.apiUrl}?cidade=${cidade}&especialidade=${especialidade}`);
  }
}
