import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { Permiso } from '../../core/models/permiso.model';
import { PermisoService } from '../../core/services/permiso.service';

@Component({
  selector: 'app-mis-permisos',
  imports: [CommonModule],
  templateUrl: './mis-permisos.component.html',
  styleUrl: './mis-permisos.component.css'
})
export class MisPermisosComponent {
  permisos: Permiso[] = [];
  horasDisponibles = 0;

  constructor(private permisoService: PermisoService) {}

  ngOnInit() {
    this.cargarDatos();
  }

  cargarDatos() {
    this.permisoService.misPermisos()
      .subscribe(p => this.permisos = p);

    this.permisoService.horasDisponibles()
      .subscribe(h => this.horasDisponibles = h.disponibles);
  }

  cancelar(permiso: Permiso) {
    if (!confirm('¿Cancelar este permiso?')) return;

    this.permisoService.cancelarPermiso(permiso.id)
      .subscribe(() => this.cargarDatos());
  }

  colorEstado(estado: string) {
    return {
      pendiente: 'badge yellow',
      aprobado: 'badge green',
      rechazado: 'badge red',
      cancelado: 'badge gray'
    }[estado];
  }
}
