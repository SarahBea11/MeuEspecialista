import { Home } from './home/home';
import { Login } from './login/login';
import { NgModule } from '@angular/core';
import { Buscar } from './buscar/buscar';
import { Perfil } from './perfil/perfil';
import { Cadastro } from './cadastro/cadastro';
import { AuthGuard } from './guards/auth-guard';
import { RouterModule, Routes } from '@angular/router';
import { EsqueceuSenha } from './esqueceu-senha/esqueceu-senha';

export const routes: Routes = [
  { path: '', component: Home },
  { path: 'login', component: Login },
  { path: 'cadastro', component: Cadastro },
  { path: 'perfil', component: Perfil,canActivate: [AuthGuard] },
  { path: 'buscar', component: Buscar, canActivate: [AuthGuard] },
  { path: 'esqueceu-senha', component: EsqueceuSenha},
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule { }
