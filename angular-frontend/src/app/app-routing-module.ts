import { Home } from './home/home';
import { Login } from './login/login';
import { NgModule } from '@angular/core';
import { Buscar } from './buscar/buscar';
import { Perfil } from './perfil/perfil';
import { Admin } from './admin/admin';
import { Cadastro } from './cadastro/cadastro';
import { AuthGuard } from './guards/auth-guard';
import { AdminGuard } from './guards/admin-guard';
import { MedicoGuard } from './guards/medico-guard';
import { RouterModule, Routes } from '@angular/router';
import { EsqueceuSenha } from './esqueceu-senha/esqueceu-senha';
import { RedefinirSenha } from './redefinir-senha/redefinir-senha';
import { Favoritos } from './favoritos/favoritos';

export const routes: Routes = [
  { path: '', component: Home },
  { path: 'login', component: Login },
  { path: 'cadastro', component: Cadastro },
  { path: 'perfil', component: Perfil, canActivate: [AuthGuard] },
  { path: 'buscar', component: Buscar, canActivate: [AuthGuard, MedicoGuard] },
  { path: 'favoritos', component: Favoritos, canActivate: [AuthGuard] },
  { path: 'admin', component: Admin, canActivate: [AdminGuard] },
  { path: 'esqueceu-senha', component: EsqueceuSenha },
  { path: 'redefinir-senha', component: RedefinirSenha },
];


@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule { }

