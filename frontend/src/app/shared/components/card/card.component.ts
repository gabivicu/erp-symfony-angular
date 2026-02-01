import { Component, Input, signal, computed, Signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { SkeletonLoaderComponent } from '../skeleton-loader/skeleton-loader.component';

/**
 * Modern Card Component
 * 
 * Design Quality Checklist:
 * ✅ Hover effect (lift + shadow increase)
 * ✅ Loading state with skeleton
 * ✅ Consistent spacing (multiples of 4px)
 * ✅ Subtle borders
 * ✅ Proper contrast
 */
@Component({
  selector: 'app-card',
  standalone: true,
  imports: [CommonModule, SkeletonLoaderComponent],
  templateUrl: './card.component.html',
  styleUrls: ['./card.component.css'],
})
export class CardComponent {
  @Input() title?: string;
  @Input() subtitle?: string;
  @Input() isLoading: boolean | Signal<boolean> = false;
  @Input() hoverable: boolean | string = true;
  @Input() padding: 'sm' | 'md' | 'lg' = 'md';
  @Input() showBorder = true;

  // Getter to handle both boolean and Signal<boolean>
  get isLoadingValue(): boolean {
    return typeof this.isLoading === 'function' ? this.isLoading() : this.isLoading;
  }

  // Computed classes
  cardClasses = computed(() => {
    const base = 'bg-surface-50 rounded-2xl transition-all duration-300';
    
    const paddingClasses = {
      sm: 'p-4',
      md: 'p-6',
      lg: 'p-8',
    };

    const borderClass = this.showBorder ? 'border border-neutral-200' : '';
    
    const hoverClass = (this.hoverable === true || this.hoverable === 'true')
      ? 'hover:shadow-soft-xl hover:-translate-y-1 cursor-pointer' 
      : '';

    return `${base} ${paddingClasses[this.padding]} ${borderClass} ${hoverClass}`;
  });
}
