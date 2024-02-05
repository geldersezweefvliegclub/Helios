import { GetObjectsResponse } from '../types/GetObjectsResponse';
import { DeepPartial, FindManyOptions, FindOptionsOrder, Repository } from 'typeorm';
import { BadRequestException, NotFoundException } from '@nestjs/common';
import { createHash } from 'crypto';
import { IHeliosEntity } from '../DTO/IHeliosEntity';

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
  async deleteObject(id?: number) {
    if (!id) throw new BadRequestException('ID moet ingevuld zijn.');
    const existingType = await this.repository.findOne({ where: { ID: id } as never });

    if (!existingType) {
      throw new BadRequestException('Type om te verwijderen niet gevonden.');
    }

    existingType.VERWIJDERD = true;
    return this.repository.save(existingType);
  }

  protected abstract buildFindOptions(filter: FilterDTO): FindManyOptions<Entity>;

  protected abstract bouwSorteringOp(commaSeparatedString: string): FindOptionsOrder<Entity>;
}
