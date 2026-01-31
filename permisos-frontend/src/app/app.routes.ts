import { Routes } from '@angular/router';
import { authGuard } from './core/guards/auth.guard';

export const routes: Routes = [

  // =====================
  // AUTH (PÚBLICO)
  // =====================
  {
    path: 'auth',
    loadChildren: () =>
      import('./auth/auth.routes')
        .then(m => m.AUTH_ROUTES)
  },

  // =====================
  // APP (PRIVADO)
  // =====================
  {
    path: '',
    canActivate: [authGuard],
    children: [

      {
        path: '',
        redirectTo: 'dashboard',
        pathMatch: 'full'
      },

      {
        path: 'dashboard',
        loadComponent: () =>
          import('./pages/dashboard/dashboard.component')
            .then(m => m.DashboardComponent)
      },

      {
        path: 'permisos',
        loadChildren: () =>
          import('./pages/permisos/permisos.routes')
            .then(m => m.PERMISOS_ROUTES)
      },

      {
        path: 'perfil',
        loadComponent: () =>
          import('./pages/perfil/perfil.component')
            .then(m => m.PerfilComponent)
      }
    ]
  },

  // =====================
  // FALLBACK
  // =====================
  { path: '**', redirectTo: 'dashboard' }
];
