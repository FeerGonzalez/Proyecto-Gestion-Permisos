import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { UserService } from '../../core/services/user.service';
import { User } from '../../core/models/user.model';

@Component({
  selector: 'app-usuarios',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './usuarios.component.html',
  styleUrl: './usuarios.component.css'
})
export class UsuariosComponent implements OnInit {

  usuarios: User[] = [];
  loading = true;
  errorMessage = '';

  constructor(
    private userService: UserService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.cargarUsuarios();
  }

  cargarUsuarios() {
    this.loading = true;
    this.userService.getAll().subscribe({
      next: users => {
        this.usuarios = users;
        this.loading = false;
      },
      error: () => {
        this.errorMessage = 'Error al cargar usuarios';
        this.loading = false;
      }
    });
  }

  nuevoUsuario() {
    this.router.navigate(['/permisos/usuarios/nuevo']);
  }

  onToggleEstado(user: User, event: Event) {
    const checked = (event.target as HTMLInputElement).checked;

    // QUIERE activar
    if (checked && user.deleted_at) {
      this.userService.activar(user.id).subscribe(() => {
        user.deleted_at = null;
      });
    }

    // QUIERE desactivar
    if (!checked && !user.deleted_at) {
      this.userService.desactivar(user.id).subscribe(() => {
        user.deleted_at = new Date().toISOString();
      });
    }
  }
}
