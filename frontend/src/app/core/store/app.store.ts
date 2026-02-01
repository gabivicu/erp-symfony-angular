import { inject, Injectable } from '@angular/core';
import { signalStore, withState, withComputed, withMethods } from '@ngrx/signals';
import { User, Company } from '../models/user.model';

/**
 * Global App Store
 * 
 * Senior-level decision: Global state for User Profile and Current Company
 * Component state for form data and module-specific data
 */
interface AppState {
  user: User | null;
  currentCompany: Company | null;
  isLoading: boolean;
}

const initialState: AppState = {
  user: null,
  currentCompany: null,
  isLoading: false,
};

@Injectable({ providedIn: 'root' })
export class AppStore extends signalStore(
  withState(initialState),
  withComputed((store) => ({
    isAuthenticated: () => store.user() !== null,
    isProPlan: () => store.currentCompany()?.subscriptionPlan === 'pro',
    userRoles: () => store.user()?.roles || [],
  })),
  withMethods((store) => ({
    setUser(user: User) {
      // Update state - would use patchState in real implementation
      // patchState(store, { user });
    },
    setCurrentCompany(company: Company) {
      // Update state - would use patchState in real implementation
      // patchState(store, { currentCompany: company });
    },
    logout() {
      // Clear state - would use patchState in real implementation
      // patchState(store, { user: null, currentCompany: null });
    },
  }))
) {}
