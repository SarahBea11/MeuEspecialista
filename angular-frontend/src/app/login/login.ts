import { Component } from '@angular/core';

@Component({
  selector: 'app-login',
  standalone: false,
  templateUrl: './login.html',
  styleUrls: ['./login.css']
})
export class Login {
loginData = {
  email:'',
  password:''

  }


constructor(){}

onLogin() {
  console.log(this.loginData);
  }

}