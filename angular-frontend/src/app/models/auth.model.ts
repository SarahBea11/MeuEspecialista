export interface LoginResponse {
  status: 'success' | 'error';
  message: string;
  token: string;
  tipo: 'medico' | 'paciente';
  nome: string;
}

export interface PerfilResponse {
  status: 'success' | 'error';
  dados: any; // Depois podemos detalhar melhor conforme o tipo
}
