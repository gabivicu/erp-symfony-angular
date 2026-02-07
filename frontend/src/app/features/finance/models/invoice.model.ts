export interface Invoice {
  id: string;
  invoiceNumber: string;
  status: string;
  total: number;
  currency: string;
  clientName: string;
  createdAt: string;
}
