
import { Test, TestingModule } from '@nestjs/testing';
import { LedenService } from './Leden.service';
import { getRepositoryToken } from '@nestjs/typeorm';
import { LedenEntity } from '../entities/Leden.entity';
import { Repository } from 'typeorm';

describe('LedenService', () => {
  let service: LedenService;
  let mockRepository: Repository<LedenEntity>;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        LedenService,
        {
          provide: getRepositoryToken(LedenEntity),
          useValue: jest.fn()
        },
      ],
    }).compile();

    service = module.get<LedenService>(LedenService);
    mockRepository = module.get<Repository<LedenEntity>>(getRepositoryToken(LedenEntity));
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});