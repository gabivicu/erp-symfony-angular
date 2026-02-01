import { Routes } from '@angular/router';

export const crmRoutes: Routes = [
  {
    path: 'leads',
    loadComponent: () => import('./components/lead-list/lead-list.component').then(m => m.LeadListComponent),
  },
  {
    path: 'estimates',
    loadComponent: () => import('./components/estimate-list/estimate-list.component').then(m => m.EstimateListComponent),
  },
  {
    path: 'estimates/new',
    loadComponent: () => import('./components/estimate-form/estimate-form.component').then(m => m.EstimateFormComponent),
  },
];
