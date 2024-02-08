import { FindOptionsBuilder } from './find-options-builder.service';


describe('FindOptionsBuilder', () => {
  let service: FindOptionsBuilder<NonNullable<unknown>>;

  beforeEach(async () => {
    service = new FindOptionsBuilder<NonNullable<unknown>>();
  });

  it('should be defined', () => {
    expect(service).toBeDefined();
  });

  it('should apply defaults to the findOptions', () => {
    const sutWithDefaultWhereObject = new FindOptionsBuilder({
      where: { id: 1 },
      select: ['id'],
      order: { id: 'ASC' },
    });
    const sutWithDefaultWhereArray = new FindOptionsBuilder({
      where: [{ id: 1 }],
      select: ['id'],
      order: { id: 'ASC' },
    });

    const expected = {
      where: [{ id: 1 }],
      select: ['id'],
      order: { id: 'ASC' },
    }

    expect(sutWithDefaultWhereObject.findOptions).toEqual(expected);
    expect(sutWithDefaultWhereArray.findOptions).toEqual(expected);
  });

  it('should chain multiple AND conditions', () => {
    service.and({ id: 1 }).and({ name: 'John' });
    expect(service.findOptions.where).toEqual([{ id: 1, name: 'John' }]);
  });

  it('should throw when adding an AND to a non-existing OR condition', () => {
    expect(() => service.and({ id: 1 }, 1)).toThrow();
  });

  it('should chain multiple OR conditions', () => {
    service.or({ id: 1 }).or({ name: 'John' });
    expect(service.findOptions.where).toEqual([{ id: 1 }, { name: 'John' }]);
  });
});
