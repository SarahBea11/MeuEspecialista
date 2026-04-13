import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { Login } from './login/login';
import { Cadastro } from './cadastro/cadastro';
import { Home } from './home/home';
import { Buscar } from './buscar/buscar';

export const routes: Routes = [
  { path: 'login', component: Login },
  { path: 'cadastro', component: Cadastro },
  { path: '', component: Home },
  { path: 'buscar', component: Buscar }

];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule { }
