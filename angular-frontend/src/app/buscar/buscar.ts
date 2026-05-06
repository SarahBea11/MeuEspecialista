import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { MedicoService } from '../services/medico.service';

@Component({
  selector: 'app-buscar',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './buscar.html',
  styleUrl: './buscar.css',
})
export class Buscar {
  especialidades = ['Cardiologia', 'Pediatria', 'Psiquiatria'];
  cidades = ['Campinas', 'Indaiatuba', 'Itu'];
  convenios = ['Amil', 'Intermédica', 'Unimed'];

  cidadeSelecionada: string = '';
  convenioSelecionado: string = '';
  especialidadeSelecionada: string = '';

  resultados: any[] = [];
termoBusca: any;

  constructor(private medicoService: MedicoService) {}

  buscarMedicos() {
    this.medicoService.buscar(this.cidadeSelecionada, this.especialidadeSelecionada).subscribe({
      next: (res) => {
        this.resultados = res;
      },
      error: (err) => {
        console.error(err);
      },
    });
  }
}
