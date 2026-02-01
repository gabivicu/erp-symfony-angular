import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ButtonComponent } from '../button/button.component';

/**
 * Empty State Component
 * 
 * Beautiful empty states with illustrations and CTAs
 * Never show empty white screens
 */
@Component({
  selector: 'app-empty-state',
  standalone: true,
  imports: [CommonModule, RouterModule, ButtonComponent],
  templateUrl: './empty-state.component.html',
  styleUrls: ['./empty-state.component.css'],
})
export class EmptyStateComponent {
  @Input() icon?: string; // SVG path or icon name
  @Input() title = 'No items yet';
  @Input() description = 'Get started by creating your first item.';
  @Input() actionLabel = 'Create';
  @Input() actionRoute?: string;
  @Input() showAction = true;
}
