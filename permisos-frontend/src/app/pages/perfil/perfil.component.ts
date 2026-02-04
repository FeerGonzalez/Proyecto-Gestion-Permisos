import { Component, OnInit } from '@angular/core';
import { AuthService } from '../../core/services/auth.service';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-perfil',
  standalone: true,
  imports: [
    CommonModule, 
    FormsModule
  ],
  templateUrl: './perfil.component.html',
  styleUrl: './perfil.component.css'
})
export class PerfilComponent implements OnInit {

  email = '';
  currentPassword = '';
  password = '';
  passwordConfirm = '';

  loading = false;
  message = '';
  error = '';

  private apiUrl = 'http://localhost:8000/api/me';

  constructor(
    private auth: AuthService,
    private http: HttpClient
  ) {}

  ngOnInit() {
    const user = this.auth.getUser();
    this.email = user.email;
  }

  actualizarEmail() {
    this.loading = true;
    this.error = '';

    this.http.put(this.apiUrl, { email: this.email }).subscribe({
      next: () => {
        this.message = 'Email actualizado';
        this.loading = false;
      },
      error: err => {
        this.loading = false;
        if (err.status === 422 && err.error?.errors) {
          this.error = this.formatLaravelErrors(err.error.errors);
        } else {
          this.error = err.error?.message || 'Error al actualizar email';
        }
      }
    });
  }

  cambiarPassword() {
    this.loading = true;
    this.error = '';

    this.http.put(`${this.apiUrl}/password`, {
      current_password: this.currentPassword,
      password: this.password,
      password_confirmation: this.passwordConfirm
    }).subscribe({
      next: () => {
        this.message = 'Contraseña actualizada';
        this.loading = false;
      },
      error: err => {
        this.loading = false;
        if (err.status === 422 && err.error?.errors) {
          this.error = this.formatLaravelErrors(err.error.errors);
        } else {
          this.error = err.error?.message || 'Error al cambiar contraseña';
        }
      }
    });
  }

  private formatLaravelErrors(errors: any): string {
    return Object.values(errors)
      .flat()
      .join(' ');
  }

}
