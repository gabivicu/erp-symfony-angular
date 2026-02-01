import { Component, Input, Output, EventEmitter, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

/**
 * Modern Button Component
 * 
 * Design Quality Checklist:
 * ✅ Proper contrast ratios (WCAG AA)
 * ✅ Consistent spacing (multiples of 4px)
 * ✅ Micro-interactions (hover lift, active scale)
 * ✅ Loading state with spinner
 * ✅ Variants (Primary, Secondary, Ghost, Danger)
 */
@Component({
  selector: 'app-button',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './button.component.html',
  styleUrls: ['./button.component.css'],
})
export class ButtonComponent {
  @Input() variant: 'primary' | 'secondary' | 'ghost' | 'danger' = 'primary';
  @Input() size: 'sm' | 'md' | 'lg' = 'md';
  @Input() isLoading = signal(false);
  @Input() disabled = false;
  @Input() fullWidth: boolean | string = false;
  @Input() icon?: string; // Heroicon name
  @Input() iconPosition: 'left' | 'right' = 'left';
  @Input() routerLink?: string | string[]; // For navigation
  @Output() clicked = new EventEmitter<void>();

  // Computed classes based on variant and state
  buttonClasses = computed(() => {
    const base = 'inline-flex items-center justify-center font-medium rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    const sizeClasses = {
      sm: 'px-3 py-1.5 text-sm',
      md: 'px-4 py-2 text-base',
      lg: 'px-6 py-3 text-lg',
    };

    const variantClasses = {
      primary: 'bg-primary-600 text-white hover:bg-primary-700 active:scale-95 focus:ring-primary-500 shadow-soft hover:shadow-soft-md hover:-translate-y-0.5',
      secondary: 'bg-accent-600 text-white hover:bg-accent-700 active:scale-95 focus:ring-accent-500 shadow-soft hover:shadow-soft-md hover:-translate-y-0.5',
      ghost: 'bg-transparent text-neutral-700 hover:bg-neutral-100 active:scale-95 focus:ring-neutral-500',
      danger: 'bg-destructive-500 text-white hover:bg-destructive-600 active:scale-95 focus:ring-destructive-500 shadow-soft hover:shadow-soft-md hover:-translate-y-0.5',
    };

    const widthClass = (this.fullWidth === true || this.fullWidth === 'true') ? 'w-full' : '';

    return `${base} ${sizeClasses[this.size]} ${variantClasses[this.variant]} ${widthClass}`;
  });

  onClick(): void {
    if (!this.disabled && !this.isLoading()) {
      this.clicked.emit();
    }
  }
}
