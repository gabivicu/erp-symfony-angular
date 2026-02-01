import { Component, OnInit, inject, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { CardComponent } from '../../../../shared/components/card/card.component';
import { ButtonComponent } from '../../../../shared/components/button/button.component';
import { EmptyStateComponent } from '../../../../shared/components/empty-state/empty-state.component';
import { SkeletonLoaderComponent } from '../../../../shared/components/skeleton-loader/skeleton-loader.component';
import { Lead } from '../../models/lead.model';

/**
 * Lead List Component (Smart Component)
 * 
 * Displays list of leads with filtering and actions
 */
@Component({
  selector: 'app-lead-list',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    CardComponent,
    ButtonComponent,
    EmptyStateComponent,
    SkeletonLoaderComponent,
  ],
  templateUrl: './lead-list.component.html',
  styleUrls: ['./lead-list.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LeadListComponent implements OnInit {
  // In production, would inject a LeadService and LeadStore
  leads: Lead[] = [];
  isLoading = false;
  filterStatus: string = 'all';

  ngOnInit(): void {
    // Load leads from service/store
    this.loadLeads();
  }

  loadLeads(): void {
    this.isLoading = true;
    // In production: this.leadStore.loadLeads();
    // Mock data for now
    setTimeout(() => {
      this.leads = [];
      this.isLoading = false;
    }, 1000);
  }

  getStatusBadgeClass(status: string): string {
    return {
      new: 'bg-neutral-100 text-neutral-800',
      contacted: 'bg-blue-100 text-blue-800',
      qualified: 'bg-purple-100 text-purple-800',
      proposal_sent: 'bg-yellow-100 text-yellow-800',
      negotiation: 'bg-orange-100 text-orange-800',
      won: 'bg-green-100 text-green-800',
      lost: 'bg-red-100 text-red-800',
    }[status] || 'bg-neutral-100 text-neutral-800';
  }
}
