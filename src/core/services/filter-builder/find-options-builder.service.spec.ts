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

  it('should chain multiple complex AND conditions', () => {
    const date = new Date();
    service.and({ ID: 1, VERWIJDERD: true }).and({ LAATSTE_AANPASSING: date });
    expect(service.findOptions.where).toEqual([{ ID: 1, VERWIJDERD: true, LAATSTE_AANPASSING: date }]);
  });

  it('should add an AND condition to an existing OR condition', () => {
    const date = new Date();
    service.or({ID: 1}).or({LAATSTE_AANPASSING: date}).and({VERWIJDERD: true}, 1);

    expect(service.findOptions.where).toEqual([{ID: 1}, {LAATSTE_AANPASSING: date, VERWIJDERD: true}]);
  });

  it('should throw when adding an AND to a non-existing OR condition', () => {
    expect(() => service.and({ ID: 1 }, 1)).toThrow();
    expect(() => service.and({ ID: 1 }, -1)).toThrow();
  });

  it('should chain multiple OR conditions', () => {
    service.or({ ID: 1 }).or({ VERWIJDERD: true });
    expect(service.findOptions.where).toEqual([{ ID: 1 }, { VERWIJDERD: true }]);
  });

  it('should set the ordering', () => {
    service.order('ID DESC, VERWIJDERD');
    expect(service.findOptions.order).toEqual({ ID: 'DESC', VERWIJDERD: 'ASC' });
  });

  it('should set the ordering as an object', () => {
    service.order({ ID: 'DESC', VERWIJDERD: 'ASC' });
    expect(service.findOptions.order).toEqual({ ID: 'DESC', VERWIJDERD: 'ASC' });
  });

  it('should select fields', () => {
    service.select('ID, VERWIJDERD');
    expect(service.findOptions.select).toEqual({ ID: true, VERWIJDERD: true });
  });

  it('should select fields as an object', () => {
    service.select({ ID: true, VERWIJDERD: true });
    expect(service.findOptions.select).toEqual({ ID: true, VERWIJDERD: true });
  });

  it('should set the max', () => {
    service.max(10);
    expect(service.findOptions.take).toEqual(10);
  });

  it('should set the skip', () => {
    service.skip(10);
    expect(service.findOptions.skip).toEqual(10);
  });
});
