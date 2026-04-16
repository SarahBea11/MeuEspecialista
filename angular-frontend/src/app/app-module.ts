import { App } from './app';
import { Home } from './home/home';
import { Login } from './login/login';
import { NgModule } from '@angular/core';
import { Perfil } from './perfil/perfil';
import { FormsModule } from '@angular/forms';
import { Cadastro } from './cadastro/cadastro';
import { AppRoutingModule } from './app-routing-module';
import { HttpClientModule } from '@angular/common/http';
import { BrowserModule } from '@angular/platform-browser';
@NgModule({
  declarations: [App, Login, Home, Perfil],
  imports: [BrowserModule, AppRoutingModule, HttpClientModule, FormsModule, Cadastro],
  providers: [],
  bootstrap: [App],
})
export class AppModule {}
