import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { HttpClientModule } from '@angular/common/http'; 
import { FormsModule } from '@angular/forms';

import { AppRoutingModule } from './app-routing-module';
import { App } from './app';
import { Login } from './login/login';
import { Cadastro } from './cadastro/cadastro';

@NgModule({
  declarations: [
    App,
    Login,

  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    HttpClientModule,
    FormsModule,
    Cadastro

  ],
  providers: [],
  bootstrap: [App]
})
export class AppModule { }
