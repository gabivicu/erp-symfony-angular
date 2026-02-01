import { Component, OnInit, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { CardComponent } from '../../../../shared/components/card/card.component';
import { ButtonComponent } from '../../../../shared/components/button/button.component';
import { EmptyStateComponent } from '../../../../shared/components/empty-state/empty-state.component';
import { Estimate } from '../../models/lead.model';

/**
 * Estimate List Component
 */
@Component({
  selector: 'app-estimate-list',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    CardComponent,
    ButtonComponent,
    EmptyStateComponent,
  ],
  templateUrl: './estimate-list.component.html',
  styleUrls: ['./estimate-list.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class EstimateListComponent implements OnInit {
  estimates: Estimate[] = [];
  isLoading = false;

  ngOnInit(): void {
    this.loadEstimates();
  }

  loadEstimates(): void {
    this.isLoading = true;
    // In production: this.estimateStore.loadEstimates();
    setTimeout(() => {
      this.estimates = [];
      this.isLoading = false;
    }, 1000);
  }

  getStatusBadgeClass(status: string): string {
    return {
      draft: 'bg-neutral-100 text-neutral-800',
      sent: 'bg-blue-100 text-blue-800',
      accepted: 'bg-green-100 text-green-800',
      rejected: 'bg-red-100 text-red-800',
      expired: 'bg-gray-100 text-gray-800',
    }[status] || 'bg-neutral-100 text-neutral-800';
  }
}
