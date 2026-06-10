import { Injectable } from '@angular/core';
import { CanActivate, Router } from '@angular/router';

@Injectable({
  providedIn: 'root',
})
export class MedicoGuard implements CanActivate {
  constructor(private router: Router) {}

  canActivate(): boolean {
    const token = localStorage.getItem('token');
    const userType = localStorage.getItem('user_type');

    if (!token) {
      this.router.navigate(['/login']);
      return false;
    }

    // Médicos não têm acesso à tela de busca — redirecionados ao perfil
    if (userType === 'medico') {
      this.router.navigate(['/perfil']);
      return false;
    }

    return true;
  }
}
