import { FindOptionsBuilder } from './find-options-builder.service';


describe('FindOptionsBuilder', () => {
  let service: FindOptionsBuilder<NonNullable<{
    ID: number;
    VERWIJDERD: boolean;
    LAATSTE_AANPASSING: Date;
  }>>;

  beforeEach(async () => {
    service = new FindOptionsBuilder();
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
    service.and({ ID: 1 }).and({ VERWIJDERD: true });
    expect(service.findOptions.where).toEqual([{ ID: 1, VERWIJDERD: true }]);
  });

  it('should throw when adding an AND to a non-existing OR condition', () => {
    expect(() => service.and({ ID: 1 }, 1)).toThrow();
  });

  it('should chain multiple OR conditions', () => {
    service.or({ ID: 1 }).or({ VERWIJDERD: true });
    expect(service.findOptions.where).toEqual([{ ID: 1 }, { VERWIJDERD: true }]);
  });
});
