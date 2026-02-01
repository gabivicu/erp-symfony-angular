import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Estimate, CreateInvoiceRequest } from '../models/lead.model';

/**
 * Estimate Service
 */
@Injectable({
  providedIn: 'root'
})
export class EstimateService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = '/api/estimates';

  getAll(): Observable<Estimate[]> {
    return this.http.get<Estimate[]>(this.apiUrl);
  }

  getById(id: string): Observable<Estimate> {
    return this.http.get<Estimate>(`${this.apiUrl}/${id}`);
  }

  create(estimate: CreateInvoiceRequest): Observable<Estimate> {
    return this.http.post<Estimate>(this.apiUrl, estimate);
  }

  convertToProject(id: string, depositPercentage?: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/${id}/convert`, { depositPercentage });
  }
}
