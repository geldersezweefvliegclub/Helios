
import { Test, TestingModule } from '@nestjs/testing';
import { DienstenService } from './Diensten.service';
import { getRepositoryToken } from '@nestjs/typeorm';
import { DienstenEntity } from '../entities/Diensten.entity';
import { DienstenViewEntity } from '../entities/DienstenView.entity';
import { Repository } from 'typeorm';
import { AuditEntity } from '../../../core/entities/Audit.entity';

describe('DienstenService', () => {
  let service: DienstenService;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  let mockRepository: Repository<DienstenEntity>;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        DienstenService,
        { provide: getRepositoryToken(DienstenEntity), useValue: jest.fn()},
        { provide: getRepositoryToken(DienstenViewEntity), useValue: jest.fn()},
        { provide: getRepositoryToken(AuditEntity), useClass: jest.fn() },
      ],
    }).compile();

    service = module.get<DienstenService>(DienstenService);
    mockRepository = module.get<Repository<DienstenEntity>>(getRepositoryToken(DienstenEntity));
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});