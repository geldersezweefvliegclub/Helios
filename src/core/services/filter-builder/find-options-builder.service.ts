import { Injectable } from '@nestjs/common';
import { FindManyOptions, ObjectLiteral } from 'typeorm';
import { FindOptionsWhere } from 'typeorm/find-options/FindOptionsWhere';
import { IHeliosEntity } from '../../DTO/IHeliosEntity';

@Injectable()
export class FindOptionsBuilder<Entity extends ObjectLiteral> {
  private findOptions: FindManyOptions<Entity> = {
    where: [],
  };

  public and(condition: FindOptionsWhere<Entity>, index: number = 0) {
    const currentWhere = this.findOptions.where as FindOptionsWhere<Entity>[];
    const currentCondition = currentWhere[index] as FindOptionsWhere<Entity>;

    currentWhere[index] = {
      ...currentCondition,
      ...condition,
    }

    return this;
  }

  public or(condition: FindOptionsWhere<Entity>) {
    (this.findOptions.where as FindOptionsWhere<Entity>[]).push(condition);
    return this;
  }
}
