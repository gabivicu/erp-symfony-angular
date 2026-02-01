import { Component, OnInit, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CardComponent } from '../../../../shared/components/card/card.component';
import { ButtonComponent } from '../../../../shared/components/button/button.component';
import { EmptyStateComponent } from '../../../../shared/components/empty-state/empty-state.component';

/**
 * Expense List Component
 */
@Component({
  selector: 'app-expense-list',
  standalone: true,
  imports: [CommonModule, CardComponent, ButtonComponent, EmptyStateComponent],
  templateUrl: './expense-list.component.html',
  styleUrls: ['./expense-list.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ExpenseListComponent implements OnInit {
  expenses: any[] = [];
  isLoading = false;

  ngOnInit(): void {
    this.loadExpenses();
  }

  loadExpenses(): void {
    this.isLoading = true;
    // In production: this.expenseStore.loadExpenses();
    setTimeout(() => {
      this.expenses = [];
      this.isLoading = false;
    }, 1000);
  }
}
