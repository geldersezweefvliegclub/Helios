import { Test, TestingModule } from '@nestjs/testing';
import { TypesGroepenService } from './types-groepen.service';
import { getRepositoryToken } from '@nestjs/typeorm';
import { mockRepository } from '../../../core/helpers/testing/TypeORMTestingModule';
import { TypeGroepEntity } from '../entities/TypeGroep.entity';
import { AuditEntity } from '../../../core/entities/Audit.entity';

describe('TypesGroepenService', () => {
  let service: TypesGroepenService;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        TypesGroepenService,
        { provide: getRepositoryToken(TypeGroepEntity), useClass: mockRepository,},
        { provide: getRepositoryToken(AuditEntity), useClass: mockRepository },
      ],
    }).compile();

    service = module.get<TypesGroepenService>(TypesGroepenService);
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});
