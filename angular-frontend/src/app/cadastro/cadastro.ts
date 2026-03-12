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

tipoUsuario:string = '';
crm:string='';
especialidade:string='';

constructor(private router: Router){}

  criarConta(form:any){
    console.log("Conta criada");
    console.log(form.value);
  }

  cancelar() {
    this.router.navigate(['/']);
  }

  limpar(form:any){

    form.resetForm();

    this.tipoUsuario ='';
    this.crm = '';
    this.especialidade = '';

  }
}
