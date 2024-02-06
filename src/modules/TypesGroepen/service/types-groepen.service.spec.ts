import { Test, TestingModule } from '@nestjs/testing';
import { TypesGroepenService } from './types-groepen.service';

describe('TypesGroepenService', () => {
  let service: TypesGroepenService;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [TypesGroepenService],
    }).compile();

    service = module.get<TypesGroepenService>(TypesGroepenService);
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});
