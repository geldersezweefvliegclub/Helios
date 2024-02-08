import { Test, TestingModule } from '@nestjs/testing';
import { FindOptionsBuilder } from './find-options-builder.service';


describe('FindOptionsBuilder', () => {
  let service: FindOptionsBuilder<NonNullable<unknown>>;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [FindOptionsBuilder],
    }).compile();

    service = module.get<FindOptionsBuilder<NonNullable<unknown>>>(FindOptionsBuilder);
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });
});
