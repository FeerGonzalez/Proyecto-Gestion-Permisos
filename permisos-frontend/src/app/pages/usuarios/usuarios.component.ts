import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { UserService } from '../../core/services/user.service';
import { AuthService } from '../../core/services/auth.service';
import { User } from '../../core/models/user.model';
import { PaginationMeta } from '../../core/models/paginated-response.model';

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
  usuarioActual!: User;
  currentPage = 1;
  lastPage = 1;
  total = 0;

  constructor(
    private userService: UserService,
    private router: Router,
    private authService: AuthService,
  ) {}

  ngOnInit(): void {
    this.usuarioActual = this.authService.getUser();
    this.cargarUsuarios();
  }

  cargarUsuarios(page: number = 1) {
    this.loading = true;
    this.errorMessage = '';

    this.userService.getAll(page).subscribe({
      next: res => {
        this.usuarios = res.data;
        this.currentPage = res.meta.current_page;
        this.lastPage = res.meta.last_page;
        this.total = res.meta.total;
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
      this.userService.desactivar(user.id).subscribe({
        next: res => {
          console.log(res.message);
        }
      });
    }
  }

  esUsuarioActual(u: User): boolean {
    return this.usuarioActual?.id === u.id;
  }

  irAPagina(page: number) {
    if (page < 1 || page > this.lastPage) return;
    this.cargarUsuarios(page);
  }
}
