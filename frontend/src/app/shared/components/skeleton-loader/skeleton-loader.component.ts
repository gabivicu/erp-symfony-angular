import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

/**
 * Skeleton Loader Component
 * 
 * Shimmer effect for loading states
 * Never show empty white screens - always use skeleton loaders
 */
@Component({
  selector: 'app-skeleton-loader',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './skeleton-loader.component.html',
  styleUrls: ['./skeleton-loader.component.css'],
})
export class SkeletonLoaderComponent {
  @Input() width: string | number = '100%';
  @Input() height: string | number = '1rem';
  @Input() rounded: 'none' | 'sm' | 'md' | 'lg' | 'full' = 'md';

  getWidth(): string {
    return typeof this.width === 'number' ? `${this.width}px` : this.width;
  }

  getHeight(): string {
    return typeof this.height === 'number' ? `${this.height}px` : this.height;
  }

  getRoundedClass(): string {
    const roundedClasses = {
      none: 'rounded-none',
      sm: 'rounded-sm',
      md: 'rounded-md',
      lg: 'rounded-lg',
      full: 'rounded-full',
    };
    return roundedClasses[this.rounded];
  }
}
