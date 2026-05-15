import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EsqueceuSenha } from './esqueceu-senha';

describe('EsqueceuSenha', () => {
  let component: EsqueceuSenha;
  let fixture: ComponentFixture<EsqueceuSenha>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [EsqueceuSenha],
    }).compileComponents();

    fixture = TestBed.createComponent(EsqueceuSenha);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
