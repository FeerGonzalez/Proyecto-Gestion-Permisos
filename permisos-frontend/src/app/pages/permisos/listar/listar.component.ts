import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { PermisoService } from '../../../core/services/permiso.service';
import { Permiso } from '../../../core/models/permiso.model';
import { AuthService } from '../../../core/services/auth.service';

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

  cargarPermisos() {
    this.loading = true;
    this.error = '';

    this.permisoService.misPermisos().subscribe({
      next: res => {
        this.permisos = res;
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
        this.cargarPermisos();
      },
      error: () => {
        this.loading = false;
        this.error = 'No se pudo cancelar el permiso';
      }
    });
  }
}
