import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Permiso } from '../models/permiso.model';

@Injectable({ providedIn: 'root' })
export class PermisoService {
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  misPermisos() {
    return this.http.get<Permiso[]>(
      `${this.apiUrl}/permisos/mios`,
      { withCredentials: true }
    );
  }

  crearPermiso(data: any) {
    return this.http.post(
      `${this.apiUrl}/permisos`,
      data,
      { withCredentials: true }
    );
  }

  cancelarPermiso(id: number) {
    return this.http.delete(
      `${this.apiUrl}/permisos/${id}/cancelar`,
      { withCredentials: true }
    );
  }

  horasDisponibles() {
    return this.http.get<{ disponibles: number }>(
      `${this.apiUrl}/permisos/horas-disponibles`,
      { withCredentials: true }
    );
  }
}