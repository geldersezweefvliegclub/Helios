import { GetObjectsResponse } from '../types/GetObjectsResponse';
import { FindManyOptions, FindOptionsOrder, ObjectLiteral, Repository } from 'typeorm';

export abstract class IHeliosService<T extends ObjectLiteral, F> {
  protected constructor(protected readonly repository: Repository<T>) {
  }

  abstract getObject(id: number): Promise<T>;

  abstract getObjects(filter: F): Promise<GetObjectsResponse<T>>;

  abstract updateObject(typeData: Partial<T>): Promise<T>;

  abstract addObject(typeData: T): Promise<T>;

  abstract restoreObject(id?: number): Promise<T>;

  abstract deleteObject(id?: number): Promise<T>;

  protected buildFindOptions(filter: F): FindManyOptions<T> {
    console.log(filter);
    return {};
  }

  protected bouwSorteringOp(commaSeparatedString: string): FindOptionsOrder<T> {
    console.log(commaSeparatedString);
    return {};
  }
}
