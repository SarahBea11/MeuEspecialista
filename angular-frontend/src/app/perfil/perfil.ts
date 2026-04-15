import { Component } from '@angular/core';

@Component({
  selector: 'app-perfil',
  standalone: false,
  templateUrl: './perfil.html',
  styleUrl: './perfil.css',
})
export class Perfil {
  usuario: any = {
    tipo: '',
    nome: '',
    email: '',
    senha: '',
    confirmarSenha: '',
    cpf: '',
    crm: '',
    especialidade: '',
    cidade: '',
    endereco: '',
    telefone: '',
    convenio: ''
  };

  usuarioOriginal: any = {};
  editando: boolean = false;

  constructor() { }

  ngOnInit(): void {
    this.carregarUsuario();
  }

  carregarUsuario() {
    const dados = {
      tipo: 'medico',
      nome: 'user',
      email: 'user@email.com',
      senha: 'senha123',
      confirmarSenha: 'senha123',
      crm: '12345',
      especialidade: 'Cardiologia',
      cidade: 'Itu',
      endereco: 'Rua X',
      telefone: '11999999999',
      convenio: 'Unimed'
    };

    this.usuario = dados;
  }

  habilitarEdicao() {
    this.usuarioOriginal = { ...this.usuario }; 
    this.editando = true;
  }

  salvar() {
    console.log('Salvando...', this.usuario);


    this.editando = false;
  }

  cancelar() {
    this.usuario = { ...this.usuarioOriginal }; 
    this.editando = false;
  }
}
