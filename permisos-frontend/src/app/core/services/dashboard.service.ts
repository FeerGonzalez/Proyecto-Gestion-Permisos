import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({ providedIn: 'root' })
export class DashboardService {
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  getDashboard() {
    return this.http.get<any>(
      `${this.apiUrl}/dashboard`,
      { withCredentials: true }
    );
  }
}