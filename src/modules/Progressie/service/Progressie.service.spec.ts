import {Test, TestingModule} from '@nestjs/testing';
import {ProgressieService} from './Progressie.service';
import {getRepositoryToken} from '@nestjs/typeorm';
import {ProgressieEntity} from '../entities/Progressie.entity';
import {Repository} from 'typeorm';
import {AuditEntity} from '../../../core/entities/Audit.entity';
import {ProgressieViewEntity} from "../entities/ProgressieView.entity";
import {CompetentiesEntity} from "../../Competenties/entities/Competenties.entity";
import { CompetentiesViewEntity } from '../../Competenties/entities/CompetentiesView.entity';

describe('ProgressieService', () => {
  let service: ProgressieService;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  let mockRepository: Repository<ProgressieEntity>;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        ProgressieService,
        { provide: getRepositoryToken(ProgressieEntity), useValue: jest.fn()},
        { provide: getRepositoryToken(AuditEntity), useClass: jest.fn() },
        { provide: getRepositoryToken(CompetentiesViewEntity), useClass: jest.fn()},
        { provide: getRepositoryToken(ProgressieViewEntity), useClass: jest.fn()}
      ],
    }).compile();

    service = module.get<ProgressieService>(ProgressieService);
    mockRepository = module.get<Repository<ProgressieEntity>>(getRepositoryToken(ProgressieEntity));
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});
