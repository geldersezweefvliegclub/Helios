import { Test, TestingModule } from '@nestjs/testing';
import { VliegtuigenService } from './vliegtuigen.service';

describe('VliegtuigenService', () => {
  let service: VliegtuigenService;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [VliegtuigenService],
    }).compile();

    service = module.get<VliegtuigenService>(VliegtuigenService);
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});
