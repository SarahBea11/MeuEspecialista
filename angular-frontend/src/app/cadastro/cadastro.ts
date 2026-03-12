import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
@Component({
  selector: 'app-cadastro',
  standalone:true,
  imports:[FormsModule],
  templateUrl: './cadastro.html',
  styleUrls: ['./cadastro.css']
})
export class Cadastro {

constructor(private router: Router){}

  criarConta(form:any){
    console.log("Conta criada");
    console.log(form.value);
  }

  cancelar() {
    this.router.navigate(['/']);
  }
}
