import { TestBed } from '@angular/core/testing';

import { StandardCodeService } from './standard-code.service';

describe('StandardCodeService', () => {
  let service: StandardCodeService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(StandardCodeService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
