import { GetObjectsResponse } from '../types/GetObjectsResponse';
import { FindManyOptions, FindOptionsOrder, ObjectLiteral, Repository } from 'typeorm';
import { BadRequestException, NotFoundException } from '@nestjs/common';

export abstract class IHeliosService<T extends ObjectLiteral, F> {
  protected constructor(protected readonly repository: Repository<T>) {
  }

  async getObject(id: number): Promise<T>{
    if (!id) throw new BadRequestException('ID moet ingevuld zijn.');

    const result = await this.repository.findOne({ where: { ID: id } as never });
    if (!result) throw new NotFoundException('Object niet gevonden.');

    return result;
  };

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
