import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Permiso } from '../models/permiso.model';

@Injectable({ providedIn: 'root' })
export class PermisoService {

  private apiUrl = 'http://localhost:8000/api/permisos';

  constructor(private http: HttpClient) {}

  misPermisos() {
    return this.http.get<Permiso[]>(`${this.apiUrl}/mis-permisos`);
  }

  crear(data: any) {
    return this.http.post(this.apiUrl, data);
  }

  horasDisponibles() {
    return this.http.get<{ horas_disponibles: number }>(
      `${this.apiUrl}/horas-disponibles`
    );
  }

  pendientes() {
    return this.http.get<Permiso[]>(`${this.apiUrl}/pendientes`);
  }

  aprobar(id: number) {
    return this.http.post(`${this.apiUrl}/${id}/aprobar`, {});
  }

  rechazar(id: number) {
    return this.http.post(`${this.apiUrl}/${id}/rechazar`, {});
  }

  cancelar(id: number) {
    return this.http.post(`${this.apiUrl}/${id}/cancelar`, {});
  }
}
