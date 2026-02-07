export interface RecurringInvoice {
  id: string;
  frequency: string;
  amount: number;
  currency: string;
  description: string;
  isActive: boolean;
  nextGenerationDate: string;
}
