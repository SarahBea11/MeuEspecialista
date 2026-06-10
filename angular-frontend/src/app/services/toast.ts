import { Injectable, NgZone } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

export interface ToastMessage {
  id: number;
  type: 'success' | 'error' | 'info' | 'warning';
  title: string;
  message: string;
  duration?: number;
}

@Injectable({
  providedIn: 'root',
})
export class ToastService {
  private toastsSubject = new BehaviorSubject<ToastMessage[]>([]);
  toasts$ = this.toastsSubject.asObservable();

  toasts: ToastMessage[] = [];
  private nextId = 0;

  constructor(private zone: NgZone) {}

  show(type: 'success' | 'error' | 'info' | 'warning', title: string, message: string, duration: number = 4000) {
    const id = this.nextId++;
    const toast: ToastMessage = { id, type, title, message, duration };
    
    this.zone.run(() => {
      this.toasts = [...this.toasts, toast];
      this.toastsSubject.next(this.toasts);
      console.log('[ToastService] Adicionado:', toast);
    });

    if (duration > 0) {
      console.log('[ToastService] Agendando remoção em:', duration, 'ms');
      this.zone.run(() => {
        setTimeout(() => {
          console.log('[ToastService] Timer disparado para ID:', id);
          this.remove(id);
        }, duration);
      });
    }
  }

  success(title: string, message: string, duration?: number) {
    this.show('success', title, message, duration !== undefined ? duration : 4000);
  }

  error(title: string, message: string, duration?: number) {
    this.show('error', title, message, duration !== undefined ? duration : 4000);
  }

  errorFriendly(title: string, err: any, defaultMsg: string = 'Verifique a conexão com o servidor.') {
    let msg = defaultMsg;
    if (err) {
      if (err.error) {
        if (typeof err.error === 'string') {
          if (err.error.includes('<br') || err.error.includes('<b>') || err.error.trim().startsWith('<')) {
            msg = 'Erro interno no servidor (formato inválido).';
          } else {
            msg = err.error;
          }
        } else if (err.error.message) {
          msg = err.error.message;
        }
      } else if (err.message) {
        msg = err.message;
      }
    }
    this.error(title, msg);
  }

  info(title: string, message: string, duration?: number) {
    this.show('info', title, message, duration !== undefined ? duration : 4000);
  }

  warning(title: string, message: string, duration?: number) {
    this.show('warning', title, message, duration !== undefined ? duration : 4000);
  }

  remove(id: number) {
    this.zone.run(() => {
      console.log('[ToastService] Removendo ID:', id);
      this.toasts = this.toasts.filter(t => t.id !== id);
      this.toastsSubject.next(this.toasts);
      console.log('[ToastService] Lista após remoção:', this.toasts);
    });
  }
}
