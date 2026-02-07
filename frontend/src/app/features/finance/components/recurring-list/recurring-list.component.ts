import { Component, ChangeDetectionStrategy, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CardComponent } from '../../../../shared/components/card/card.component';
import { ButtonComponent } from '../../../../shared/components/button/button.component';
import { EmptyStateComponent } from '../../../../shared/components/empty-state/empty-state.component';
import { InfiniteScrollDirective } from '../../../../shared/directives/infinite-scroll.directive';
import { RecurringInvoiceService } from '../../services/recurring-invoice.service';
import { RecurringInvoice } from '../../models/recurring-invoice.model';
import { BehaviorSubject, of, catchError } from 'rxjs';

const PAGE_SIZE = 20;

export type RecurringListState = {
  items: RecurringInvoice[];
  loading: boolean;
  loadingMore: boolean;
  hasMore: boolean;
};

/**
 * Recurring Invoices List - loads recurring invoices with infinite scroll
 */
@Component({
  selector: 'app-recurring-list',
  standalone: true,
  imports: [
    CommonModule,
    CardComponent,
    ButtonComponent,
    EmptyStateComponent,
    InfiniteScrollDirective,
  ],
  templateUrl: './recurring-list.component.html',
  styleUrls: ['./recurring-list.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class RecurringListComponent implements OnInit {
  private readonly recurringService = inject(RecurringInvoiceService);

  private readonly state = new BehaviorSubject<RecurringListState>({
    items: [],
    loading: true,
    loadingMore: false,
    hasMore: true,
  });
  recurringState$ = this.state.asObservable();

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

    this.recurringService.getAll(PAGE_SIZE, offset).pipe(
      catchError(() => of([]))
    ).subscribe((response) => {
      const raw = Array.isArray(response) ? response : (response as { data?: RecurringInvoice[] })?.data;
      const page = (Array.isArray(raw) ? raw : []) as RecurringInvoice[];
      const current = this.state.value;
      const items = isInitial ? page : [...current.items, ...page];
      this.state.next({
        items,
        loading: false,
        loadingMore: false,
        hasMore: page.length === PAGE_SIZE,
      });
    });
  }

  loadMore(): void {
    const current = this.state.value;
    if (current.loadingMore || !current.hasMore || current.loading) return;
    this.loadPage(current.items.length);
  }

  formatDate(iso: string): string {
    try {
      return new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' });
    } catch {
      return iso;
    }
  }
}
