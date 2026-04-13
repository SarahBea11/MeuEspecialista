import { Component } from '@angular/core';

@Component({
  selector: 'app-buscar',
  standalone: false,
  templateUrl: './buscar.html',
  styleUrl: './buscar.css',
})
export class Buscar {

  especialidades = ['Cardiologia', 'Pediatria', 'Psiquiatria'];
  cidades = ['Campinas', 'Indaiatuba', 'São Paulo'];
  convenios = ['Amil', 'Intermédica', 'Unimed'];

  cidadeSelecionada: string = '';
  convenioSelecionado: string = '';
  especialidadeSelecionada: string = '';
}
