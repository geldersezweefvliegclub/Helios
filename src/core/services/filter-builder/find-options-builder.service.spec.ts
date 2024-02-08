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

  it('should chain multiple AND conditions', () => {
    service.and({ id: 1 }).and({ name: 'John' });
    expect(service.findOptions.where).toEqual([{ id: 1, name: 'John' }]);
  });

  it('should throw when adding an AND to a non-existing OR condition', () => {
    expect(() => service.and({ id: 1 }, 1)).toThrow();
  })

  it('should chain multiple OR conditions', () => {
    service.or({ id: 1 }).or({ name: 'John' });
    expect(service.findOptions.where).toEqual([{ id: 1 }, { name: 'John' }]);
  });
});
