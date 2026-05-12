export interface Usuario {
  id?: number;
  nome: string;
  email: string;
  tipo: 'medico' | 'paciente';
  senha?: string;
  confirmarSenha?: string;
}

export interface Medico extends Usuario {
  crm: string;
  especialidade: string;
  cidade: string;
  telefone: string;
  endereco: string;
}

export interface Paciente extends Usuario {
  cpf: string;
  cidade?: string;
  telefone?: string;
  endereco?: string;
  convenio?: string;
  convenio_id?: number;
}
