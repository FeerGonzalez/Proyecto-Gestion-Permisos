import { CanActivateFn, ActivatedRouteSnapshot, Router } from '@angular/router';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service';

export const roleGuard: CanActivateFn = (route: ActivatedRouteSnapshot) => {
  const auth = inject(AuthService);
  const router = inject(Router);

  const roles = route.data['roles'] as string[];

  if (!auth.hasRole(...roles)) {
    router.navigate(['/']);
    return false;
  }
  return true;
};