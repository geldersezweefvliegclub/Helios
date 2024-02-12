
import { Test, TestingModule } from '@nestjs/testing';
import { CompetentiesService } from './Competenties.service';
import { getRepositoryToken } from '@nestjs/typeorm';
import { CompetentiesEntity } from '../entities/Competenties.entity';
import { Repository } from 'typeorm';
import { AuditEntity } from '../../../core/entities/Audit.entity';

describe('CompetentiesService', () => {
  let service: CompetentiesService;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  let mockRepository: Repository<CompetentiesEntity>;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        CompetentiesService,
        { provide: getRepositoryToken(CompetentiesEntity), useValue: jest.fn()},
        { provide: getRepositoryToken(AuditEntity), useClass: jest.fn() },
      ],
    }).compile();

    service = module.get<CompetentiesService>(CompetentiesService);
    mockRepository = module.get<Repository<CompetentiesEntity>>(getRepositoryToken(CompetentiesEntity));
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});