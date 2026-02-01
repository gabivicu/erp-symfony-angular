import { Component, OnInit, ChangeDetectionStrategy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CardComponent } from '../../../../shared/components/card/card.component';
import { ButtonComponent } from '../../../../shared/components/button/button.component';
import { EmptyStateComponent } from '../../../../shared/components/empty-state/empty-state.component';

/**
 * Task List Component
 */
@Component({
  selector: 'app-task-list',
  standalone: true,
  imports: [CommonModule, CardComponent, ButtonComponent, EmptyStateComponent],
  templateUrl: './task-list.component.html',
  styleUrls: ['./task-list.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TaskListComponent implements OnInit {
  tasks: any[] = [];
  isLoading = false;

  ngOnInit(): void {
    this.loadTasks();
  }

  loadTasks(): void {
    this.isLoading = true;
    // In production: this.taskStore.loadTasks();
    setTimeout(() => {
      this.tasks = [];
      this.isLoading = false;
    }, 1000);
  }
}
