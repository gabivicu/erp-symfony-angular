import {
  AfterViewInit,
  Directive,
  ElementRef,
  EventEmitter,
  inject,
  NgZone,
  OnDestroy,
  Output,
} from '@angular/core';

const THRESHOLD_PX = 200;

/**
 * Infinite Scroll Directive
 * Emits when the user scrolls near the host element (bottom of list).
 * Uses scroll listener so it works with any scroll container (window or overflow div).
 */
@Directive({
  selector: '[appInfiniteScroll]',
  standalone: true,
})
export class InfiniteScrollDirective implements AfterViewInit, OnDestroy {
  @Output() appInfiniteScrollLoad = new EventEmitter<void>();

  private readonly el = inject(ElementRef<HTMLElement>);
  private readonly ngZone = inject(NgZone);
  private scrollRoot: Window | Element | null = null;
  private scrollListener: (() => void) | null = null;
  private lastEmit = 0;
  private rafId: number | null = null;

  private getScrollRoot(): Window | Element | null {
    let node: HTMLElement | null = this.el.nativeElement.parentElement;
    while (node) {
      const { overflowY, overflow } = getComputedStyle(node);
      if (overflowY === 'auto' || overflowY === 'scroll' || overflow === 'auto' || overflow === 'scroll') {
        return node;
      }
      node = node.parentElement;
    }
    return typeof window !== 'undefined' ? window : null;
  }

  private checkAndEmit(): void {
    const sentinel = this.el.nativeElement;
    const root = this.scrollRoot;
    if (!root || !sentinel.isConnected) return;

    const rect = sentinel.getBoundingClientRect();
    const threshold = THRESHOLD_PX;
    let shouldLoad: boolean;

    if (root instanceof Window) {
      shouldLoad = rect.top <= window.innerHeight + threshold;
    } else {
      const rootRect = root.getBoundingClientRect();
      shouldLoad = rect.top <= rootRect.bottom + threshold;
    }

    if (shouldLoad && Date.now() - this.lastEmit > 500) {
      this.lastEmit = Date.now();
      this.ngZone.run(() => this.appInfiniteScrollLoad.emit());
    }
  }

  private onScroll = (): void => {
    if (this.rafId !== null) return;
    this.rafId = requestAnimationFrame(() => {
      this.rafId = null;
      this.checkAndEmit();
    });
  };

  ngAfterViewInit(): void {
    this.ngZone.runOutsideAngular(() => {
      this.scrollRoot = this.getScrollRoot();
      if (!this.scrollRoot) return;

      this.scrollListener = this.onScroll;
      this.scrollRoot.addEventListener('scroll', this.scrollListener, { passive: true });

      // Check after layout (sentinel might already be in view)
      setTimeout(() => this.checkAndEmit(), 200);
    });
  }

  ngOnDestroy(): void {
    if (this.scrollRoot && this.scrollListener) {
      this.scrollRoot.removeEventListener('scroll', this.scrollListener);
    }
    if (this.rafId !== null) {
      cancelAnimationFrame(this.rafId);
    }
    this.scrollRoot = null;
    this.scrollListener = null;
  }
}
