import { GetObjectsResponse } from '../types/GetObjectsResponse';
import { DeepPartial, Repository } from 'typeorm';
import { BadRequestException, NotFoundException } from '@nestjs/common';
import { createHash } from 'crypto';
import { IHeliosEntity } from '../DTO/IHeliosEntity';
import { IHeliosFilterDTO } from '../DTO/IHeliosFilterDTO';

export abstract class IHeliosService<Entity extends IHeliosEntity, FilterDTO extends IHeliosFilterDTO<Entity>> {
  protected constructor(protected readonly repository: Repository<Entity>) {
  }

  async getObject(id: number): Promise<Entity>{
    if (!id) throw new BadRequestException('ID moet ingevuld zijn.');

    const result = await this.repository.findOne({ where: { ID: id } as never });
    if (!result) throw new NotFoundException('Object niet gevonden.');

    return result;
  };
  async getObjects(filter: FilterDTO): Promise<GetObjectsResponse<Entity>> {
    const findOptions = filter.buildTypeORMFindManyObject();
    const dataset = await this.repository.find(findOptions);
    const hash = createHash('md5').update(JSON.stringify(dataset)).digest('hex');

    return {
      totaal: dataset.length,
      laatste_aanpassing: new Date(),
      dataset: dataset,
      hash: hash,
    };
  }

  async updateObject(typeData: DeepPartial<Entity>): Promise<Entity> {
    if (!typeData.ID) {
      throw new BadRequestException('ID moet ingevuld zijn.');
    }

    const existingType = await this.repository.findOne({where: {ID: typeData.ID} as never});

    if (!existingType) {
      throw new BadRequestException('Type om te updaten niet gevonden.');
    }

    const updatedType = this.repository.merge(existingType, typeData);
    return this.repository.save(updatedType);
  }

  async addObject(data: Entity): Promise<Entity> {
    if (!data) {
      throw new BadRequestException('Object data moet zijn ingevuld.');
    }

    const newType = this.repository.create(data);
    return this.repository.save(newType);
  }

  async restoreObject(id?: number): Promise<Entity> {
    if (!id) throw new BadRequestException('ID moet ingevuld zijn.');
    const existingType = await this.repository.findOne({ where: { ID: id } as never});

    if (!existingType) {
      throw new BadRequestException('Type om te herstellen niet gevonden.');
    }

    existingType.VERWIJDERD = false;
    return this.repository.save(existingType);
  }

  async deleteObject(id?: number) {
    if (!id) throw new BadRequestException('ID moet ingevuld zijn.');
    const existingType = await this.repository.findOne({ where: { ID: id } as never });

    if (!existingType) {
      throw new BadRequestException('Type om te verwijderen niet gevonden.');
    }

    existingType.VERWIJDERD = true;
    return this.repository.save(existingType);
  }
}
