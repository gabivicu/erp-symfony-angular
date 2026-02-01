import { Component, OnInit, signal, computed, ChangeDetectionStrategy } from '@angular/core';
import { FormArray, FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommonModule } from '@angular/common';

/**
 * Estimate Form Component with Dynamic FormArray
 * 
 * Senior-level decision: FormArray for dynamic invoice/estimate line items
 * Signals for real-time total calculation
 */
@Component({
  selector: 'app-estimate-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './estimate-form.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class EstimateFormComponent implements OnInit {
  estimateForm!: FormGroup;

  // Signals for real-time calculation
  subtotalSignal = signal(0);
  vatSignal = signal(0);
  
  // Computed signals for totals
  totalSignal = computed(() => this.subtotalSignal() + this.vatSignal());

  constructor(private fb: FormBuilder) {}

  ngOnInit(): void {
    this.estimateForm = this.fb.group({
      leadId: ['', Validators.required],
      currency: ['USD', Validators.required],
      lines: this.fb.array([]),
    });

    // Add initial line
    this.addLine();
  }

  get lines(): FormArray {
    return this.estimateForm.get('lines') as FormArray;
  }

  /**
   * Add a new line item dynamically
   */
  addLine(): void {
    const lineGroup = this.fb.group({
      description: ['', Validators.required],
      quantity: [1, [Validators.required, Validators.min(1)]],
      unitPrice: [0, [Validators.required, Validators.min(0)]],
      vatRate: [0, [Validators.required, Validators.min(0), Validators.max(100)]],
    });

    // Subscribe to line changes for real-time calculation
    lineGroup.valueChanges.subscribe(() => {
      this.calculateTotals();
    });

    this.lines.push(lineGroup);
    this.calculateTotals();
  }

  /**
   * Remove a line item
   */
  removeLine(index: number): void {
    if (this.lines.length > 1) {
      this.lines.removeAt(index);
      this.calculateTotals();
    }
  }

  /**
   * Calculate totals using Signals
   * Senior-level decision: Real-time calculation with Signals
   */
  private calculateTotals(): void {
    let subtotal = 0;
    let totalVat = 0;

    this.lines.controls.forEach((lineGroup) => {
      const value = lineGroup.value;
      const lineSubtotal = value.quantity * value.unitPrice;
      subtotal += lineSubtotal;
      totalVat += lineSubtotal * (value.vatRate / 100);
    });

    // Update signals (triggers computed signal update)
    this.subtotalSignal.set(subtotal);
    this.vatSignal.set(totalVat);
  }

  /**
   * Get line subtotal (for display)
   */
  getLineSubtotal(index: number): number {
    const line = this.lines.at(index).value;
    return line.quantity * line.unitPrice;
  }

  /**
   * Submit form
   */
  onSubmit(): void {
    if (this.estimateForm.valid) {
      const formValue = this.estimateForm.value;
      console.log('Estimate data:', formValue);
      // Emit event or call service
    }
  }
}
