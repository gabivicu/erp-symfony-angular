import { Component, OnInit, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { CardComponent } from '../../../../shared/components/card/card.component';
import { ButtonComponent } from '../../../../shared/components/button/button.component';
import { EmptyStateComponent } from '../../../../shared/components/empty-state/empty-state.component';

/**
 * Invoice List Component
 */
@Component({
  selector: 'app-invoice-list',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    CardComponent,
    ButtonComponent,
    EmptyStateComponent,
  ],
  templateUrl: './invoice-list.component.html',
  styleUrls: ['./invoice-list.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class InvoiceListComponent implements OnInit {
  invoices: any[] = [];
  isLoading = false;

  ngOnInit(): void {
    this.loadInvoices();
  }

  loadInvoices(): void {
    this.isLoading = true;
    // In production: this.invoiceStore.loadInvoices();
    setTimeout(() => {
      this.invoices = [];
      this.isLoading = false;
    }, 1000);
  }
}
