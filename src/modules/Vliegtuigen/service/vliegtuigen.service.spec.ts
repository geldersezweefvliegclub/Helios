import { Test, TestingModule } from '@nestjs/testing';
import { VliegtuigenService } from './vliegtuigen.service';
import { VliegtuigenEntity } from '../entities/Vliegtuigen.entity';
import { getRepositoryToken } from '@nestjs/typeorm';
import { mockRepository } from '../../../core/helpers/testing/TypeORMTestingModule';

describe('VliegtuigenService', () => {
  let service: VliegtuigenService;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        VliegtuigenService,
        { provide: getRepositoryToken(VliegtuigenEntity), useClass: mockRepository },
      ],
    }).compile();

    service = module.get<VliegtuigenService>(VliegtuigenService);
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});
