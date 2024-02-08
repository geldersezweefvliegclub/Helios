import { Injectable } from '@nestjs/common';
import { FindManyOptions, ObjectLiteral } from 'typeorm';
import { FindOptionsWhere } from 'typeorm/find-options/FindOptionsWhere';
import { InvalidArgumentException } from '../../helpers/exceptions/InvalidArgumentException';

@Injectable()
export class FindOptionsBuilder<Entity extends ObjectLiteral> {
  get findOptions(): FindManyOptions<Entity> {
    return this._findOptions;
  }
  private _findOptions: FindManyOptions<Entity> = {
    where: [],
  };

  public and(condition: FindOptionsWhere<Entity>, index: number = 0) {
    const currentWhere = this._findOptions.where as FindOptionsWhere<Entity>[];
    const currentCondition = currentWhere[index] as FindOptionsWhere<Entity>;

    if (index < 0 || (index >= currentWhere.length && index != 0)) throw new InvalidArgumentException('Invalid index to build AND filter')

    currentWhere[index] = {
      ...currentCondition,
      ...condition,
    }

    return this;
  }

  public or(condition: FindOptionsWhere<Entity>) {
    (this._findOptions.where as FindOptionsWhere<Entity>[]).push(condition);
    return this;
  }
}
