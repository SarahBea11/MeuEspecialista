import { App } from './app';
import { Home } from './home/home';
import { Login } from './login/login';
import { NgModule } from '@angular/core';
import { Perfil } from './perfil/perfil';
import { FormsModule } from '@angular/forms';
import { Cadastro } from './cadastro/cadastro';
import { AppRoutingModule } from './app-routing-module';
import { BrowserModule } from '@angular/platform-browser';
import { AuthInterceptor } from './services/auth.interceptor';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { EsqueceuSenha } from './esqueceu-senha/esqueceu-senha';
import { ServiceWorkerModule } from '@angular/service-worker';

@NgModule({
  declarations: [App, Login, Perfil, EsqueceuSenha],
  imports: [
    BrowserModule,
    AppRoutingModule,
    HttpClientModule,
    FormsModule,
    Cadastro,
    Home,
    ServiceWorkerModule.register('ngsw-worker.js', {
      enabled: true,
      registrationStrategy: 'registerWhenStable:30000'
    })
  ],
  providers: [
    {
      provide: HTTP_INTERCEPTORS,
      useClass: AuthInterceptor,
      multi: true,
    },
  ],
  bootstrap: [App],
})
export class AppModule {}

