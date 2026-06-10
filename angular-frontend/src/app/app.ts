import { Component, HostListener, ChangeDetectorRef, OnInit, OnDestroy } from '@angular/core';
import { ToastService, ToastMessage } from './services/toast';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-root',
  templateUrl: './app.html',
  standalone: false,
  styleUrls: ['./app.css'],
})
export class App implements OnInit, OnDestroy {
  showScroll: boolean = false;
  toasts: ToastMessage[] = [];
  private toastSubscription!: Subscription;

  constructor(
    public toastService: ToastService,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit() {
    this.toastSubscription = this.toastService.toasts$.subscribe((toasts) => {
      this.toasts = toasts;
      this.cdr.detectChanges();
    });
  }

  ngOnDestroy() {
    if (this.toastSubscription) {
      this.toastSubscription.unsubscribe();
    }
  }

  @HostListener('window:scroll', [])
  onWindowScroll() {
    // Mostra o botão se a rolagem passar de 300px
    this.showScroll = window.scrollY > 300;
  }

  scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }
}
