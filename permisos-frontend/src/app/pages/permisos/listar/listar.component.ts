import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { PermisoService } from '../../../core/services/permiso.service';
import { Permiso } from '../../../core/models/permiso.model';
import { AuthService } from '../../../core/services/auth.service';
import { ApiResponse } from '../../../core/models/api-response.model';

@Component({
  selector: 'app-listar',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './listar.component.html',
  styleUrl: './listar.component.css'
})
export class ListarComponent implements OnInit {

  permisos: Permiso[] = [];
  loading = false;
  error = '';
  userId!: number;
  currentPage = 1;
  lastPage = 1;
  total = 0;

  constructor(
    private permisoService: PermisoService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    const user = this.authService.getUser();

    if (user) {
      this.userId = user.id;
    }
    
    this.cargarPermisos();
  }

  cargarPermisos(page: number = 1) {
    this.loading = true;
    this.error = '';

    this.permisoService.misPermisos(page).subscribe({
      next: (res) => {
        this.permisos = res.data;
        this.currentPage = res.meta.current_page;
        this.lastPage = res.meta.last_page;
        this.total = res.meta.total;
        this.loading = false;
      },
      error: () => {
        this.error = 'No se pudieron cargar los permisos';
        this.loading = false;
      }
    });
  }

  cancelar(id: number) {
    const confirmar = confirm('¿Estás seguro de cancelar este permiso?');
    if (!confirmar) return;

    this.loading = true;
    this.error = '';

    this.permisoService.cancelar(id).subscribe({
      next: () => {
        this.loading = false;
        this.cargarPermisos(this.currentPage);
      },
      error: () => {
        this.loading = false;
        this.error = 'No se pudo cancelar el permiso';
      }
    });
  }

  irAPagina(page: number) {
    if (page < 1 || page > this.lastPage) return;
    this.cargarPermisos(page);
  }
}
