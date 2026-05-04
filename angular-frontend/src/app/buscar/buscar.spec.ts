import { Buscar } from './buscar';
import { ComponentFixture, TestBed } from '@angular/core/testing';

describe('Buscar', () => {
  let component: Buscar;
  let fixture: ComponentFixture<Buscar>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [Buscar],
    }).compileComponents();

    fixture = TestBed.createComponent(Buscar);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });
  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
