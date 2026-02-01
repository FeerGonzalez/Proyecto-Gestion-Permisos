import { Routes } from '@angular/router';
import { roleGuard } from '../../core/guards/role.guard';

export const PERMISOS_ROUTES: Routes = [
  {
    path: '',
    loadComponent: () =>
      import('./listar/listar.component')
        .then(m => m.ListarComponent)
  },
  {
    path: 'crear',
    loadComponent: () =>
      import('./crear/crear.component')
        .then(m => m.CrearComponent)
  },
  {
    path: 'pendientes',
    canActivate: [roleGuard(['supervisor', 'rrhh'])],
    loadComponent: () =>
      import('./pendientes/pendientes.component')
        .then(m => m.PendientesComponent)
  }
];
