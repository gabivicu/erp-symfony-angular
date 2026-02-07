import { Component, ChangeDetectionStrategy, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CardComponent } from '../../../../shared/components/card/card.component';
import { ButtonComponent } from '../../../../shared/components/button/button.component';
import { EmptyStateComponent } from '../../../../shared/components/empty-state/empty-state.component';
import { InfiniteScrollDirective } from '../../../../shared/directives/infinite-scroll.directive';
import { TimeLogService } from '../../services/time-log.service';
import { TimeLog } from '../../models/time-log.model';
import { BehaviorSubject, of, catchError } from 'rxjs';

const PAGE_SIZE = 20;

export type TimeLogListState = {
  timeLogs: TimeLog[];
  loading: boolean;
  loadingMore: boolean;
  hasMore: boolean;
};

/**
 * Time Log List - loads time logs with infinite scroll
 */
@Component({
  selector: 'app-time-log-list',
  standalone: true,
  imports: [
    CommonModule,
    CardComponent,
    ButtonComponent,
    EmptyStateComponent,
    InfiniteScrollDirective,
  ],
  templateUrl: './time-log-list.component.html',
  styleUrls: ['./time-log-list.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TimeLogListComponent implements OnInit {
  private readonly timeLogService = inject(TimeLogService);

  private readonly state = new BehaviorSubject<TimeLogListState>({
    timeLogs: [],
    loading: true,
    loadingMore: false,
    hasMore: true,
  });
  timeLogState$ = this.state.asObservable();

  ngOnInit(): void {
    this.loadPage(0);
  }

  loadPage(offset: number): void {
    const isInitial = offset === 0;
    this.state.next({
      ...this.state.value,
      loading: isInitial,
      loadingMore: !isInitial,
    });

    this.timeLogService.getAll(PAGE_SIZE, offset).pipe(
      catchError(() => of([]))
    ).subscribe((response) => {
      const raw = Array.isArray(response) ? response : (response as { data?: TimeLog[] })?.data;
      const page = (Array.isArray(raw) ? raw : []) as TimeLog[];
      const current = this.state.value;
      const timeLogs = isInitial ? page : [...current.timeLogs, ...page];
      this.state.next({
        timeLogs,
        loading: false,
        loadingMore: false,
        hasMore: page.length === PAGE_SIZE,
      });
    });
  }

  loadMore(): void {
    const current = this.state.value;
    if (current.loadingMore || !current.hasMore || current.loading) return;
    this.loadPage(current.timeLogs.length);
  }

  formatDate(iso: string): string {
    try {
      const d = new Date(iso);
      return d.toLocaleDateString(undefined, { dateStyle: 'medium' });
    } catch {
      return iso;
    }
  }
}
