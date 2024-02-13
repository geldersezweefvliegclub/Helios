import { Injectable } from '@nestjs/common';
import { FindManyOptions, FindOptionsOrder, FindOptionsRelations, FindOptionsSelect } from 'typeorm';
import { FindOptionsWhere } from 'typeorm/find-options/FindOptionsWhere';
import { InvalidArgumentException } from '../../helpers/exceptions/InvalidArgumentException';
import { IHeliosObject } from '../../base/IHeliosObject';

@Injectable()
export class FindOptionsBuilder<Entity extends IHeliosObject> {
  get findOptions(): FindManyOptions<Entity> {
    return this._findOptions;
  }

  private readonly _findOptions: FindManyOptions<Entity> = {
    where: [],
  };

  constructor(defaults?: Partial<FindManyOptions<Entity>>) {
    if (defaults?.where && !Array.isArray(defaults.where)) {
      defaults.where = [defaults.where];
    }

    this._findOptions = {
      ...this._findOptions,
      ...defaults,
    };
  }

  public and(condition: FindOptionsWhere<Entity>, index: number = 0) {
    const currentWhere = this._findOptions.where as FindOptionsWhere<Entity>[];
    const currentCondition = currentWhere[index] as FindOptionsWhere<Entity>;

    if (index < 0 || (index >= currentWhere.length && index != 0)) throw new InvalidArgumentException('Invalid index to build AND filter');

    currentWhere[index] = {
      ...currentCondition,
      ...condition,
    };

    return this;
  }

  public or(condition: FindOptionsWhere<Entity>) {
    (this._findOptions.where as FindOptionsWhere<Entity>[]).push(condition);
    return this;
  }

  /**
   * Set the sorting (ordering) for the query
   * @param sorting Either a comma seperated string or an object
   * @example
   * // Comma seperated string
   * builder.order('CLUBKIST DESC, VOLGORDE, REGISTRATIE'); // { CLUBKIST: 'DESC', VOLGORDE: 'ASC', REGISTRATIE: 'ASC' }
   *
   * // Object
   * builder.order({ CLUBKIST: 'DESC', VOLGORDE: 'ASC', REGISTRATIE: 'ASC' }); // { CLUBKIST: 'DESC', VOLGORDE: 'ASC', REGISTRATIE: 'ASC' }
   */
  public order(sorting: string | FindOptionsOrder<Entity>) {
    if (typeof sorting === 'string') {
      this._findOptions.order = this.buildOrdering(sorting);
    } else {
      this._findOptions.order = sorting;
    }
    return this;
  }


  /**
   * Only select particular fields from the database
   * @param fields Either a comma seperated string, or a FindOptionsSelect type
   * @param additionalFields Additional fields to select regardless of the fields parameter
   */
  select(fields: string | FindOptionsSelect<Entity>, additionalFields: FindOptionsSelect<Entity> = {}) {
    if (typeof fields === 'object') {
      this._findOptions.select = { ...fields, ...additionalFields };
      return this;
    }

    const select: Record<string, boolean> = {};
    const fieldNames = fields.split(',');
    fieldNames.forEach((fieldName) => {
      select[fieldName.trim()] = true;
    });
    this._findOptions.select = { ...(select as FindOptionsSelect<Entity>), ...additionalFields };
  }

  /**
   * Converts a comma seperated string to a FindOptionsOrder object
   * @param commaSeparatedString
   * @private
   * @example
   * Input: "CLUBKIST DESC, VOLGORDE, REGISTRATIE"
   * Output: { CLUBKIST: 'DESC', VOLGORDE: 'ASC', REGISTRATIE: 'ASC' }
   */
  private buildOrdering(commaSeparatedString: string): FindOptionsOrder<Entity> {
    const order: Record<string, string> = {};

    const sortFields = commaSeparatedString.split(',');

    for (const sortField of sortFields) {
      const parts = sortField.trim().split(' ');
      const field = parts[0];
      // Take the value from the second part of the string, or default to 'ASC' sorting if the second part is not present
      order[field as keyof typeof order] = parts.length > 1 ? parts[1] : 'ASC';
    }

    return order as FindOptionsOrder<Entity>;
  }

  /**
   * Skip the first n results
   * @param count
   */
  skip(count: number) {
    this._findOptions.skip = count;
    return this;
  }

  /**
   * Take the first n results
   * @param count
   */
  max(count: number) {
    this._findOptions.take = count;
    return this;
  }

  public takeWhereCondition(index: number): FindOptionsWhere<Entity> {
    return (this._findOptions.where as FindOptionsWhere<Entity>[])[index];
  }

  clearWhere() {
    this._findOptions.where = [];
  }

  public relations(relations: FindOptionsRelations<Entity>) {
    this._findOptions.relations = relations
    return this;
  }
}
