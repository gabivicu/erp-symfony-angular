import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

/**
 * Project Service
 */
@Injectable({
  providedIn: 'root'
})
export class ProjectService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = '/api/projects';

  getAll(): Observable<any[]> {
    return this.http.get<any[]>(this.apiUrl);
  }

  getById(id: string): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/${id}`);
  }

  getProfitability(id: string): Observable<any> {
    return this.http.get<any>(`${this.apiUrl}/${id}/profitability`);
  }
}
