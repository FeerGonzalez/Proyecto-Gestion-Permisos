import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, tap } from 'rxjs';
import { User } from '../models/user.model';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private apiUrl = 'http://localhost:8000/api';

  private userSubject = new BehaviorSubject<User | null>(null);
  user$ = this.userSubject.asObservable();

  constructor(private http: HttpClient) {}

  login(email: string, password: string) {
    return this.http.post<{ user: User }>(
      `${this.apiUrl}/login`,
      { email, password },
      { withCredentials: true }
    ).pipe(
      tap(response => this.userSubject.next(response.user))
    );
  }

  logout() {
    return this.http.post(`${this.apiUrl}/logout`, {}, { withCredentials: true })
      .pipe(tap(() => this.userSubject.next(null)));
  }

  me() {
    return this.http.get<User>(`${this.apiUrl}/me`, { withCredentials: true })
      .pipe(tap(user => this.userSubject.next(user)));
  }

  isLoggedIn(): boolean {
    return !!this.userSubject.value;
  }

  hasRole(...roles: string[]): boolean {
    return roles.includes(this.userSubject.value?.role || '');
  }
}