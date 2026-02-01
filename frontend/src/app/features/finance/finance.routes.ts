import { Routes } from '@angular/router';

export const financeRoutes: Routes = [
  {
    path: 'invoices',
    loadComponent: () => import('./components/invoice-list/invoice-list.component').then(m => m.InvoiceListComponent),
  },
  {
    path: 'expenses',
    loadComponent: () => import('./components/expense-list/expense-list.component').then(m => m.ExpenseListComponent),
  },
];
