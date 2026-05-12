import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { MedicoService } from '../services/medico.service';
import { Medico } from '../models/usuario.model';

@Component({
  selector: 'app-buscar',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './buscar.html',
  styleUrl: './buscar.css',
})
export class Buscar implements OnInit {
  especialidades = ['Cardiologia', 'Pediatria', 'Psiquiatria'];
  cidades = ['Campinas', 'Indaiatuba', 'Itu'];
  convenios = ['Amil', 'Intermédica', 'Unimed'];

  cidadeSelecionada: string = '';
  convenioSelecionado: string = '';
  especialidadeSelecionada: string = '';

  resultados: Medico[] = [];
  termoBusca: string = '';
  userName: string = '';

  constructor(
    private medicoService: MedicoService,
    private router: Router,
    private cdr: ChangeDetectorRef,
  ) {
    this.userName = localStorage.getItem('user_name') || '';
  }

  ngOnInit(): void {
    this.buscarMedicos();
  }

  buscarMedicos() {
    this.medicoService
      .buscar(this.cidadeSelecionada, this.especialidadeSelecionada, this.termoBusca)
      .subscribe({
        next: (res) => {
          this.resultados = res;
          this.cdr.detectChanges();
        },
        error: (err) => {
          console.error('Erro na busca de médicos:', err);
        },
      });
  }

  sair() {
    localStorage.clear();
    this.router.navigate(['/login']);
  }
}
