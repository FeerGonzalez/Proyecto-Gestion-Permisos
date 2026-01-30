import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MisPermisosComponent } from './mis-permisos.component';

describe('MisPermisosComponent', () => {
  let component: MisPermisosComponent;
  let fixture: ComponentFixture<MisPermisosComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [MisPermisosComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MisPermisosComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
