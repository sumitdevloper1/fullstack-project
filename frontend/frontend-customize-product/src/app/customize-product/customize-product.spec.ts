import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CustomizeProduct } from './customize-product';

describe('CustomizeProduct', () => {
  let component: CustomizeProduct;
  let fixture: ComponentFixture<CustomizeProduct>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CustomizeProduct]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CustomizeProduct);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
