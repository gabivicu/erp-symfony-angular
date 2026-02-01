import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CardComponent } from '../../shared/components/card/card.component';
import { ButtonComponent } from '../../shared/components/button/button.component';

/**
 * Dashboard Component with Bento Grid Layout
 * 
 * Modern grid layout inspired by Linear/Stripe
 * Cards arranged in a visually appealing grid
 */
@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [CommonModule, CardComponent, ButtonComponent],
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css'],
})
export class DashboardComponent {
  // Example data - would come from services/stores
  stats = {
    totalRevenue: 125000,
    activeProjects: 12,
    pendingInvoices: 5,
    teamMembers: 8,
  };
}
