export interface Expense {
  id: string;
  description: string;
  amount: number;
  currency: string;
  status: string;
  expenseDate: string;
  projectName: string | null;
}
