import { Component } from '@angular/core';
import { AuthService } from '../../core/services/auth.service';
import { Router, RouterLink, RouterLinkActive } from '@angular/router';
import { CommonModule } from '@angular/common';


@Component({
  selector: 'app-navbar',
  imports: [
    CommonModule,
    RouterLink,
    RouterLinkActive
  ],
  templateUrl: './navbar.component.html',
  styleUrl: './navbar.component.css'
})
export class NavbarComponent {
  constructor(
    public auth: AuthService,
    private router: Router
  ) {}

  get user() {
    return this.auth.getUser();
  }

  logout() {
    this.auth.logout();
    this.router.navigate(['/auth/login']);
  }
}
