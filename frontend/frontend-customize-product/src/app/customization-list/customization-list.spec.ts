import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CustomizationList } from './customization-list';

describe('CustomizationList', () => {
  let component: CustomizationList;
  let fixture: ComponentFixture<CustomizationList>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CustomizationList]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CustomizationList);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
