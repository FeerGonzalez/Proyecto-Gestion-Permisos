import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map, Observable } from 'rxjs';
import { User } from '../models/user.model';
import { ApiResponse } from '../models/api-response.model';
import { PaginatedResponse } from '../models/paginated-response.model';

@Injectable({ providedIn: 'root' })
export class UserService {

  private apiUrl = 'http://localhost:8000/api/usuarios';

  constructor(private http: HttpClient) {}

  getAll(page: number = 1) {
    return this.http.get<PaginatedResponse<User>>(
      `${this.apiUrl}?page=${page}`
    );
  }

  getById(id: number): Observable<User> {
    return this.http.get<User>(`${this.apiUrl}/${id}`);
  }

  create(data: Partial<User> & { password: string }) {
    return this.http.post<User>(this.apiUrl, data);
  }

  update(id: number, data: Partial<User>) {
    return this.http.put(`${this.apiUrl}/${id}`, data);
  }

  delete(id: number) {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }

  desactivar(id: number) {
    return this.http
      .patch<ApiResponse<User>>(`${this.apiUrl}/${id}/desactivar`, {})
  }

  activar(id: number) {
    return this.http.patch(`${this.apiUrl}/${id}/activar`, {});
  }
}
