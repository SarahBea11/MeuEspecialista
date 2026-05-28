import { Component, HostListener } from '@angular/core';
import { ToastService } from './services/toast';

@Component({
  selector: 'app-root',
  templateUrl: './app.html',
  standalone: false,
  styleUrls: ['./app.css'],
})
export class App {
  showScroll: boolean = false;

  constructor(public toastService: ToastService) {}

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

