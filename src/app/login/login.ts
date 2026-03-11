import { Component } from '@angular/core';

@Component({
  selector: 'app-login',
  standalone: false,
  templateUrl: './login.html',
  styleUrl: './login.css',
})
export class Login {
loginData = {
  email:'',
  password:''

  }


construtor(){}

onLogin() {
  console.log(this.loginData);
  }

}