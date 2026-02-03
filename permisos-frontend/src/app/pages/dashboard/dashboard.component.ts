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

  statsGestion = {
    aprobados: 0,
    rechazados: 0
  };

  esSupervisorORRHH = false;

  loading = false;
  error = '';

  constructor(
    private permisoService: PermisoService,
    private authService: AuthService,
  ) {}

  ngOnInit(): void {
    this.esSupervisorORRHH = this.authService.hasRole(['supervisor', 'rrhh']);
    this.cargarDashboard();
  }

  cargarDashboard() {
    this.loading = true;
    this.error = '';

    interface DashboardForkResult {
      permisos: { data: Permiso[] };
      horas: { horas_disponibles: number };
      gestionados?: { data: Permiso[] };
    }

    const requests: {
      permisos: ReturnType<PermisoService['misPermisos']>;
      horas: ReturnType<PermisoService['horasDisponibles']>;
      gestionados?: ReturnType<PermisoService['gestionadosPorMi']>;
    } = {
      permisos: this.permisoService.misPermisos(),
      horas: this.permisoService.horasDisponibles()
    };

    if (this.esSupervisorORRHH) {
      requests.gestionados = this.permisoService.gestionadosPorMi();
    }

    forkJoin(requests).subscribe({
      next: (res: DashboardForkResult) => {
        this.permisos = res.permisos.data;
        this.horasDisponibles = res.horas.horas_disponibles;

        this.calcularStats();

        this.permisosPendientes = this.permisos.filter(
          p => p.estado === 'pendiente'
        );

        if (res.gestionados) {
          this.calcularStatsGestion(res.gestionados.data);
        }

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

  private calcularStatsGestion(permisos: Permiso[]) {
    this.statsGestion.aprobados = permisos.filter(
      p => p.estado === 'aprobado'
    ).length;

    this.statsGestion.rechazados = permisos.filter(
      p => p.estado === 'rechazado'
    ).length;
  }
  
}
