import { Observable } from 'rxjs';
import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    const token = localStorage.getItem('token');

    // Nao envia o token de Authorization para APIs externas (como a busca de CEP) para evitar erros de CORS/preflight
    if (token && !req.url.includes('viacep.com.br')) {
      const cloned = req.clone({
        setHeaders: {
          Authorization: `Bearer ${token}`,
        },
      });
      return next.handle(cloned);
    }

    return next.handle(req);
  }
}
