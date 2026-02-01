import { CanActivateFn, Router } from '@angular/router';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service';

export const roleGuard = (allowedRoles: string[]): CanActivateFn => {
  return () => {
    const auth = inject(AuthService);
    const router = inject(Router);

    // Si no está autenticado → login
    if (!auth.isAuthenticated()) {
      return router.createUrlTree(['/login']);
    }

    // Si no tiene el rol permitido → acceso denegado
    if (!auth.hasRole(allowedRoles as any)) {
      return router.createUrlTree(['/permisos']);
    }

    return true;
  };
};
