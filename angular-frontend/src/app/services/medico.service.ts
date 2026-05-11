import { Observable } from 'rxjs';
import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Medico } from '../models/usuario.model';

@Injectable({
  providedIn: 'root',
})
export class MedicoService {
  private apiUrl = 'http://localhost/MeuEspecialista/php-backend/api/buscar_medicos.php';

  constructor(private http: HttpClient) {}

  // Adicionamos o parâmetro opcional 'termo' para o nome/CRM
  buscar(cidade: string, especialidade: string, termo: string = ''): Observable<Medico[]> {
    let params = new HttpParams()
      .set('cidade', cidade)
      .set('especialidade', especialidade)
      .set('termo', termo);

    return this.http.get<Medico[]>(this.apiUrl, { params });
  }
}
