import { Component, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, RouterLink, RouterLinkActive } from '@angular/router';
import { CardComponent } from '../../shared/components/card/card.component';

/**
 * Modern Dashboard Layout
 * 
 * Features:
 * - Collapsible sidebar with clear active states
 * - Top bar with search (CMD+K style) and user profile
 * - Bento Grid for dashboard widgets
 * - Linear/Stripe aesthetic
 */
@Component({
  selector: 'app-dashboard-layout',
  standalone: true,
  imports: [CommonModule, RouterOutlet, RouterLink, RouterLinkActive, CardComponent],
  templateUrl: './dashboard-layout.component.html',
  styleUrls: ['./dashboard-layout.component.css'],
})
export class DashboardLayoutComponent {
  sidebarCollapsed = signal(false);
  showUserMenu = signal(false);
  showSearchModal = signal(false);

  menuItems = [
    {
      label: 'Dashboard',
      route: '/dashboard',
      icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    },
    {
      label: 'CRM',
      route: '/crm',
      icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
      children: [
        { label: 'Leads', route: '/crm/leads' },
        { label: 'Estimates', route: '/crm/estimates' },
      ],
    },
    {
      label: 'Projects',
      route: '/projects',
      icon: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
      children: [
        { label: 'All Projects', route: '/projects' },
        { label: 'Tasks', route: '/projects/tasks' },
        { label: 'Time Logs', route: '/projects/time-logs' },
      ],
    },
    {
      label: 'Finance',
      route: '/finance',
      icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
      children: [
        { label: 'Invoices', route: '/finance/invoices' },
        { label: 'Expenses', route: '/finance/expenses' },
        { label: 'Recurring', route: '/finance/recurring' },
      ],
    },
  ];

  toggleSidebar(): void {
    this.sidebarCollapsed.update(collapsed => !collapsed);
  }

  toggleUserMenu(): void {
    this.showUserMenu.update(show => !show);
  }

  openSearch(): void {
    this.showSearchModal.set(true);
  }

  closeSearch(): void {
    this.showSearchModal.set(false);
  }

  // Handle CMD+K / CTRL+K for search
  onKeyDown(event: KeyboardEvent): void {
    if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
      event.preventDefault();
      this.openSearch();
    }
  }
}
