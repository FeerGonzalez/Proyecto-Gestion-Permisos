import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PermisoService } from '../../../core/services/permiso.service';
import { Permiso } from '../../../core/models/permiso.model';
import { AuthService } from '../../../core/services/auth.service';

@Component({
  selector: 'app-pendientes',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './pendientes.component.html',
  styleUrl: './pendientes.component.css'
})
export class PendientesComponent implements OnInit {

  permisos: Permiso[] = [];
  loading = false;
  error = '';
  usuarioId!: number;
  currentPage = 1;
  lastPage = 1;

  constructor(
    private permisoService: PermisoService,
    private authService: AuthService,
  ) {}

  ngOnInit(): void {
    if (!this.puedeAcceder()) {
      this.error = 'No tenés permisos para acceder a esta sección';
      return;
    }

    const user = this.authService.getUser();
    this.usuarioId = user.id;

    this.cargarPendientes();
  }

  cargarPendientes(page: number = 1) {
    this.loading = true;
    this.error = '';

    this.permisoService.pendientes(page).subscribe({
      next: res => {
        this.permisos = res.data;
        this.currentPage = res.meta.current_page;
        this.lastPage = res.meta.last_page;
        this.loading = false;
      },
      error: () => {
        this.error = 'No se pudieron cargar los permisos pendientes';
        this.loading = false;
      }
    });
  }


  aprobar(id: number) {
    if (!this.puedeAcceder()) return;

    const ok = confirm('¿Confirmás aprobar este permiso?');
    if (!ok) return;

    this.loading = true;

    this.permisoService.aprobar(id).subscribe({
      next: () => this.cargarPendientes(),
      error: () => {
        this.loading = false;
        this.error = 'No se pudo aprobar el permiso';
      }
    });
  }

  rechazar(id: number) {
    if (!this.puedeAcceder()) return;

    const ok = confirm('¿Confirmás rechazar este permiso?');
    if (!ok) return;

    this.loading = true;

    this.permisoService.rechazar(id).subscribe({
      next: () => this.cargarPendientes(),
      error: () => {
        this.loading = false;
        this.error = 'No se pudo rechazar el permiso';
      }
    });
  }

  puedeAcceder(): boolean {
    return this.authService.hasRole(['supervisor', 'rrhh']);
  }

  puedeEvaluar(permiso: Permiso): boolean {
    return permiso.user_id !== this.usuarioId;
  }

  irAPagina(page: number) {
    if (page < 1 || page > this.lastPage) return;
    this.cargarPendientes(page);
  }

}
