import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { RecurringInvoice } from '../models/recurring-invoice.model';

/**
 * Recurring Invoice Service - API communication for recurring invoices
 */
@Injectable({
  providedIn: 'root'
})
export class RecurringInvoiceService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = '/api/recurring-invoices';

  getAll(limit = 20, offset = 0): Observable<RecurringInvoice[]> {
    const params = new HttpParams().set('limit', String(limit)).set('offset', String(offset));
    return this.http.get<RecurringInvoice[]>(this.apiUrl, { params });
  }
}
