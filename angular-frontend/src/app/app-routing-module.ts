import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { Login } from './login/login';
import { Cadastro} from './cadastro/cadastro';

export const routes: Routes = [
  {path: '', component:Login},
  {path:'cadastro', component:Cadastro}
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
