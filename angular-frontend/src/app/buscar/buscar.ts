import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-buscar',
  standalone: true,
  imports: [CommonModule, FormsModule],
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
