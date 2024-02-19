
import { Test, TestingModule } from '@nestjs/testing';
import { RoosterService } from './Rooster.service';
import { getRepositoryToken } from '@nestjs/typeorm';
import { RoosterEntity } from '../entities/Rooster.entity';
import { RoosterViewEntity } from '../entities/RoosterView.entity';
import { Repository } from 'typeorm';
import { AuditEntity } from '../../../core/entities/Audit.entity';

describe('RoosterService', () => {
  let service: RoosterService;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  let mockRepository: Repository<RoosterEntity>;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        RoosterService,
        { provide: getRepositoryToken(RoosterEntity), useValue: jest.fn()},
        { provide: getRepositoryToken(RoosterViewEntity), useValue: jest.fn()},
        { provide: getRepositoryToken(AuditEntity), useClass: jest.fn() },
      ],
    }).compile();

    service = module.get<RoosterService>(RoosterService);
    mockRepository = module.get<Repository<RoosterEntity>>(getRepositoryToken(RoosterEntity));
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});