import {Test, TestingModule} from '@nestjs/testing';
import {LedenService} from './Leden.service';
import {getRepositoryToken} from '@nestjs/typeorm';
import {LedenEntity} from '../entities/Leden.entity';
import {Repository} from 'typeorm';
import {AuditEntity} from '../../../core/entities/Audit.entity';
import {LedenViewEntity} from "../entities/LedenView.entity";

describe('LedenService', () => {
  let service: LedenService;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  let mockRepository: Repository<LedenEntity>;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        LedenService,
        {
          provide: getRepositoryToken(LedenEntity),
          useValue: jest.fn()
        },
        { provide: getRepositoryToken(AuditEntity), useClass: jest.fn() },
        { provide: getRepositoryToken(LedenViewEntity), useClass: jest.fn()}
      ],
    }).compile();

    service = module.get<LedenService>(LedenService);
    mockRepository = module.get<Repository<LedenEntity>>(getRepositoryToken(LedenEntity));
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});
