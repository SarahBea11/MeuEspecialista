import { Component, HostListener } from '@angular/core';

@Component({
  selector: 'app-root',
  templateUrl: './app.html',
  standalone: false,
  styleUrl: './app.css',
})
export class App {
  showScroll: boolean = false;

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

