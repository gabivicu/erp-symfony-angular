/**
 * CRM Module Models
 */
export interface Lead {
  id: string;
  name: string;
  email: string;
  companyName: string;
  status: LeadStatus;
  createdAt: string;
}

export type LeadStatus = 'new' | 'contacted' | 'qualified' | 'proposal_sent' | 'negotiation' | 'won' | 'lost';

export interface Estimate {
  id: string;
  leadId: string;
  estimateNumber: string;
  status: EstimateStatus;
  total: number;
  currency: string;
  lines: EstimateLine[];
}

export type EstimateStatus = 'draft' | 'sent' | 'accepted' | 'rejected' | 'expired';

export interface EstimateLine {
  description: string;
  quantity: number;
  unitPrice: number;
  vatRate: number;
}

export interface CreateInvoiceRequest {
  clientId: string;
  currency: string;
  lines: CreateInvoiceLineRequest[];
}

export interface CreateInvoiceLineRequest {
  description: string;
  quantity: number;
  unitPrice: number;
  vatRate: number;
}
