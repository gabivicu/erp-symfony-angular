/**
 * User Model - Global State
 */
export interface User {
  id: string;
  email: string;
  name: string;
  roles: string[];
  company: Company;
}

export interface Company {
  id: string;
  name: string;
  subdomain: string;
  subscriptionPlan: 'starter' | 'pro';
}
