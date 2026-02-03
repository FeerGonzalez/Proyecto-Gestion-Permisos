import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { PermisoService } from '../../core/services/permiso.service';
import { Permiso } from '../../core/models/permiso.model';
import { forkJoin } from 'rxjs';
import { AuthService } from '../../core/services/auth.service';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.css'
})
export class DashboardComponent implements OnInit {

  permisos: Permiso[] = [];
  permisosPendientes: Permiso[] = [];

  horasDisponibles = 0;

  stats = {
    total: 0,
    pendientes: 0,
    aprobados: 0,
    rechazados: 0,
    cancelados: 0
  };

  loading = false;
  error = '';

  constructor(
    private permisoService: PermisoService,
    private authService: AuthService,
  ) {}

  ngOnInit(): void {
    this.cargarDashboard();
  }

  cargarDashboard() {
    this.loading = true;
    this.error = '';

    forkJoin({
      permisos: this.permisoService.misPermisos(),
      horas: this.permisoService.horasDisponibles()
    }).subscribe({
      next: ({ permisos, horas }) => {
        this.permisos = permisos.data;
        this.horasDisponibles = horas.horas_disponibles;

        this.calcularStats();
        this.permisosPendientes = this.permisos.filter(
          p => p.estado === 'pendiente'
        );

        this.loading = false;
      },
      error: () => {
        this.error = 'No se pudo cargar la información del dashboard';
        this.loading = false;
      }
    });
  }

  private calcularStats() {
    this.stats.total = this.permisos.length;

    this.stats.pendientes = this.permisos.filter(
      p => p.estado === 'pendiente'
    ).length;

    this.stats.aprobados = this.permisos.filter(
      p => p.estado === 'aprobado'
    ).length;

    this.stats.rechazados = this.permisos.filter(
      p => p.estado === 'rechazado'
    ).length;

    this.stats.cancelados = this.permisos.filter(
      p => p.estado === 'cancelado'
    ).length;
  }


  
}
