import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { TimeLog } from '../models/time-log.model';

/**
 * Time Log Service - API communication for time logs
 */
@Injectable({
  providedIn: 'root'
})
export class TimeLogService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = '/api/time-logs';

  getAll(limit = 20, offset = 0): Observable<TimeLog[]> {
    const params = new HttpParams().set('limit', String(limit)).set('offset', String(offset));
    return this.http.get<TimeLog[]>(this.apiUrl, { params });
  }
}
