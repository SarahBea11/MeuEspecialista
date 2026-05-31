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
  foto?: string;
  atualizado_em?: string;
  // usado para controle de favorito no front
  favoritado?: boolean;
  notificacoes_ativas?: boolean;
}

export interface Paciente extends Usuario {
  cpf: string;
  cidade?: string;
  telefone?: string;
  endereco?: string;
  convenio?: string;
  convenio_id?: number;
}

export interface Favorito {
  medico_usuario_id: number;
  nome: string;
  email: string;
  crm: string;
  especialidade: string;
  cidade: string;
  telefone: string;
  endereco: string;
  foto?: string;
  atualizado_em?: string;
  notificacoes_ativas: boolean;
  favoritado_em: string;
}

