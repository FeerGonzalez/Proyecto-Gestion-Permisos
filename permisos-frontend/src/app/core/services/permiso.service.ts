import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Permiso } from '../models/permiso.model';
import { ApiResponse } from '../models/api-response.model';
import { PaginatedResponse } from '../models/paginated-response.model';

@Injectable({ providedIn: 'root' })
export class PermisoService {

  private apiUrl = 'http://localhost:8000/api/permisos';

  constructor(private http: HttpClient) {}

  misPermisos(page: number = 1) {
    return this.http.get<PaginatedResponse<Permiso>>(
      `${this.apiUrl}/mis-permisos?page=${page}`
    );
  }

  crear(data: any) {
    return this.http.post(this.apiUrl, data);
  }

  horasDisponibles() {
    return this.http.get<{ horas_disponibles: number }>(
      `${this.apiUrl}/horas-disponibles`
    );
  }

  pendientes(page: number = 1) {
    return this.http.get<PaginatedResponse<Permiso>>(
      `${this.apiUrl}/pendientes?page=${page}`
    );
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

  gestionadosPorMi() {
    return this.http.get<ApiResponse<Permiso[]>>(
      `${this.apiUrl}/gestionados`
    );
  }
}
