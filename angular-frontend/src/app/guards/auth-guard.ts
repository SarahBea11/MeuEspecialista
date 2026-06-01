import { Injectable } from '@angular/core';
import { CanActivate, Router } from '@angular/router';

@Injectable({
  providedIn: 'root',
})
export class AuthGuard implements CanActivate {

  constructor(private router: Router) {}

  canActivate(): boolean {
    const token = localStorage.getItem('token');

    if (token && this.isTokenValid(token)) {
      return true;
    } else {
      localStorage.removeItem('token');
      this.router.navigate(['/login']);
      return false;
    }
  }

  private isTokenValid(token: string): boolean {
    try {
      const parts = token.split('.');
      if (parts.length !== 2) {
        return false;
      }
      const payloadBase64 = parts[0];
      // atob decodifica base64. Usamos decodeURIComponent para lidar com acentuação/Unicode se houver
      const jsonPayload = decodeURIComponent(
        atob(payloadBase64)
          .split('')
          .map((c) => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
          .join('')
      );
      const decoded = JSON.parse(jsonPayload);
      if (!decoded || typeof decoded !== 'object') {
        return false;
      }
      if (decoded.exp && decoded.exp < Math.floor(Date.now() / 1000)) {
        return false; // Expirado
      }
      return true;
    } catch (e) {
      return false;
    }
  }
}
