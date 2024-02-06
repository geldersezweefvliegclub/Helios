import { GetObjectsResponse } from '../types/GetObjectsResponse';
import { DeepPartial, Repository } from 'typeorm';
import { BadRequestException, NotFoundException } from '@nestjs/common';
import { createHash } from 'crypto';
import { IHeliosEntity } from '../DTO/IHeliosEntity';
import { IHeliosFilterDTO } from '../DTO/IHeliosFilterDTO';

export abstract class IHeliosService<Entity extends IHeliosEntity> {
  protected constructor(protected readonly repository: Repository<Entity>) {
  }

  /**
   * Haal een enkel object op uit de database met gegeven ID.
   * @param id
   * @throws BadRequestException ID moet ingevuld zijn.
   * @throws NotFoundException Object niet gevonden.
   */
  async getObject(id?: number): Promise<Entity>{
    if (!id) throw new BadRequestException('ID moet ingevuld zijn.');

    const result = await this.repository.findOne({ where: { ID: id } as never });
    if (!result) throw new NotFoundException('Object niet gevonden.');

    return result;
  };

  /**
   * Haal een lijst van objecten op uit de database die voldoen aan het gegeven filter.
   * @param filter Filter om de objecten op te halen.
   */
  async getObjects(filter: IHeliosFilterDTO<Entity>): Promise<GetObjectsResponse<Entity>> {
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

  /**
   * Update een object in de database met gegeven data.
   * @throws BadRequestException ID moet ingevuld zijn.
   * @throws BadRequestException Object om te updaten niet gevonden.
   * @param typeData
   */
  async updateObject(typeData: DeepPartial<Entity>): Promise<Entity> {
    if (!typeData.ID) {
      throw new BadRequestException('ID moet ingevuld zijn.');
    }

    const existingType = await this.repository.findOne({where: {ID: typeData.ID} as never});

    if (!existingType) {
      throw new BadRequestException('Object om te updaten niet gevonden.');
    }

    const updatedType = this.repository.merge(existingType, typeData);
    return this.repository.save(updatedType);
  }

  /**
   * Voeg een nieuw object toe aan de database.
   * @param data
   * @throws BadRequestException Object data moet zijn ingevuld.
   */
  async addObject(data: Entity): Promise<Entity> {
    if (!data) {
      throw new BadRequestException('Object data moet zijn ingevuld.');
    }

    const newType = this.repository.create(data);
    return this.repository.save(newType);
  }

  /**
   * Herstel een soft-deleted object in de database, zodat deze niet meer als verwijderd wordt gezien.
   * @param id ID van het object om te herstellen.
   * @throws BadRequestException ID moet ingevuld zijn.
   * @throws BadRequestException Object om te herstellen niet gevonden.
   */
  async restoreObject(id?: number): Promise<Entity> {
    if (!id) throw new BadRequestException('ID moet ingevuld zijn.');
    const existingType = await this.repository.findOne({ where: { ID: id } as never});

    if (!existingType) {
      throw new BadRequestException('Object om te herstellen niet gevonden.');
    }

    existingType.VERWIJDERD = false;
    return this.repository.save(existingType);
  }

  /**
   * Soft-delete (VERWIJDERD=true) het object met gegeven ID uit de database.
   * @throws BadRequestException ID moet ingevuld zijn.
   * @throws BadRequestException Type om te verwijderen niet gevonden.
   * @param id
   */
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
