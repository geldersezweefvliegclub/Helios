import { Test, TestingModule } from '@nestjs/testing';
import { TypesService } from './types.service';
import { getRepositoryToken } from '@nestjs/typeorm';
import { mockRepository } from '../../../core/helpers/testing/TypeORMTestingModule';
import { TypeEntity } from '../entities/Type.entity';

describe('TypesService', () => {
  let service: TypesService;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        TypesService,
        { provide: getRepositoryToken(TypeEntity), useClass: mockRepository },],
    }).compile();

    service = module.get<TypesService>(TypesService);
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});
