import { Injectable, inject, signal, computed } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable, tap, catchError, of } from 'rxjs';

const TOKEN_KEY = 'auth_token';
const API_LOGIN = '/api/login';

export interface LoginRequest {
  username: string;
  password: string;
}

export interface LoginResponse {
  token: string;
}

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private readonly http = inject(HttpClient);
  private readonly router = inject(Router);

  private readonly tokenSignal = signal<string | null>(this.getStoredToken());

  readonly isLoggedIn = computed(() => this.tokenSignal() !== null);
  readonly token = this.tokenSignal.asReadonly();

  login(username: string, password: string): Observable<{ token: string } | null> {
    return this.http
      .post<LoginResponse>(API_LOGIN, { username, password } as LoginRequest, {
        headers: { 'Content-Type': 'application/json' },
      })
      .pipe(
        tap((res) => {
          if (res?.token) {
            this.setToken(res.token);
          }
        }),
        catchError((error) => {
          console.error('Login error:', error);
          // Return null to indicate login failure
          return of(null);
        }),
      );
  }

  logout(): void {
    this.clearToken();
    this.router.navigate(['/login']);
  }

  getToken(): string | null {
    return this.tokenSignal();
  }

  private setToken(token: string): void {
    try {
      localStorage.setItem(TOKEN_KEY, token);
      this.tokenSignal.set(token);
    } catch {
      this.tokenSignal.set(token);
    }
  }

  private clearToken(): void {
    try {
      localStorage.removeItem(TOKEN_KEY);
    } finally {
      this.tokenSignal.set(null);
    }
  }

  private getStoredToken(): string | null {
    try {
      return localStorage.getItem(TOKEN_KEY);
    } catch {
      return null;
    }
  }
}
