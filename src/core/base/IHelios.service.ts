import { GetObjectsResponse } from '../types/GetObjectsResponse';
import { DeepPartial, FindManyOptions, FindOptionsOrder, ObjectLiteral, Repository } from 'typeorm';
import { BadRequestException, NotFoundException } from '@nestjs/common';
import { createHash } from 'crypto';
import { TypeGroepEntity } from '../../modules/TypesGroepen/entities/TypeGroep.entity';

export interface IHeliosEntity extends ObjectLiteral {
  ID: number;
  VERWIJDERD: boolean;
  LAATSTE_AANPASSING: Date;
}


export abstract class IHeliosService<Entity extends IHeliosEntity, FilterDTO> {
  protected constructor(protected readonly repository: Repository<Entity>) {
  }

  async getObject(id: number): Promise<Entity>{
    if (!id) throw new BadRequestException('ID moet ingevuld zijn.');

    const result = await this.repository.findOne({ where: { ID: id } as never });
    if (!result) throw new NotFoundException('Object niet gevonden.');

    return result;
  };

  async getObjects(filter: FilterDTO): Promise<GetObjectsResponse<Entity>> {
    const findOptions = this.buildFindOptions(filter);
    const dataset = await this.repository.find(findOptions);
    const hash = createHash('md5').update(JSON.stringify(dataset)).digest('hex');

    return {
      totaal: dataset.length,
      laatste_aanpassing: new Date(),
      dataset: dataset,
      hash: hash,
    };
  }

  abstract updateObject(typeData: DeepPartial<Entity>): Promise<Entity>;

  async addObject(data: Entity): Promise<Entity> {
    if (!data) {
      throw new BadRequestException('Object data moet zijn ingevuld.');
    }

    const newType = this.repository.create(data);
    return this.repository.save(newType);
  }

  abstract restoreObject(id?: number): Promise<Entity>;

  abstract deleteObject(id?: number): Promise<Entity>;

  protected abstract buildFindOptions(filter: FilterDTO): FindManyOptions<Entity>;

  protected bouwSorteringOp(commaSeparatedString: string): FindOptionsOrder<Entity> {
    console.log(commaSeparatedString);
    return {};
  }
}
