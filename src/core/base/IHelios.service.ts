import { GetObjectsResponse } from './GetObjectsResponse';
import { DeepPartial, FindOptionsSelectByString, Repository } from 'typeorm';
import { BadRequestException, Logger, NotFoundException } from '@nestjs/common';
import { createHash } from 'crypto';
import { IHeliosObject } from './IHeliosObject';
import { IHeliosFilterDTO } from './IHeliosFilterDTO';
import { FindOptionsSelect } from 'typeorm/find-options/FindOptionsSelect';
import { AuditEntity } from '../entities/Audit.entity';
import { InjectRepository } from '@nestjs/typeorm';

/**
 * Base-service die basisfunctionaliteiten biedt voor het ophalen, updaten, toevoegen, herstellen en verwijderen van een TypeORM Entity in de database.
 * Deze service weet niet hoe de Entity eruit ziet, behalve dat het een IHeliosEntity is.
 *  @param Entity TypeORM Entity die de service behandelt.
 */
export abstract class IHeliosService<Entity extends IHeliosObject> {
  private logger: Logger = new Logger('IHeliosService');

  protected constructor(protected readonly repository: Repository<Entity>, @InjectRepository(AuditEntity) protected readonly auditRepository: Repository<AuditEntity>) {
  }

  /**
   * Haal een enkel object op uit de database met gegeven ID.
   * @param id
   * @throws BadRequestException ID moet ingevuld zijn.
   * @throws NotFoundException Object niet gevonden.
   */
  async getObject(id?: number): Promise<Entity> {
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
    filter.bouwGetObjectsFindOptions();
    const findOptions = filter.findOptionsBuilder.findOptions;
    const datasetRaw = await this.repository.find(findOptions);
    const dataset = this.applySelectionFilterToCalculatedColumns(datasetRaw, findOptions.select);
    const hash = createHash('md5').update(JSON.stringify(dataset)).digest('hex');

    // For LAATSTE_AANPASSING, search through the audit table for the latest change for this table
    const entityTable = this.repository.metadata.tableName;
    const auditTrail = await this.auditRepository.findOne({
      where: { TABEL: entityTable },
      order: { LAATSTE_AANPASSING: 'DESC' },
    });

    let laatsteAanpassing = auditTrail?.LAATSTE_AANPASSING;
    // If we don't have an audit trail, we should fall back to the last change recorded on a record
    // todo when VELDEN excludes LAATSTE_AANPASSING this also fails -> returns undefined
    if (!auditTrail) {
      const sorted = [...dataset].sort((a, b) => {
        if (a.LAATSTE_AANPASSING < b.LAATSTE_AANPASSING) return 1;
        if (a.LAATSTE_AANPASSING > b.LAATSTE_AANPASSING) return -1;
        return 0;
      });
      laatsteAanpassing = sorted[0]?.LAATSTE_AANPASSING;
    }


    return {
      totaal: dataset.length,
      laatste_aanpassing: laatsteAanpassing,
      dataset: dataset,
      hash: hash,
    };
  }

  /**
   * Update een object in de database met gegeven data.
   * @throws BadRequestException ID moet ingevuld zijn.
   * @throws BadRequestException Object om te updaten niet gevonden.
   * @param objectData
   */
  async updateObject(objectData: DeepPartial<Entity>): Promise<Entity> {
    if (!objectData.ID) {
      throw new BadRequestException('ID moet ingevuld zijn.');
    }

    const existingType = await this.getObject(objectData.ID);

    if (!existingType) {
      throw new BadRequestException('Object om te updaten niet gevonden.');
    }

    const updatedType = this.repository.merge(existingType, objectData);
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

    // TODO: Check for Conflict (bestaat al) wat oude PHP backend ook doet
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
    const existingType = await this.getObject(id);

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
    const existingType = await this.getObject(id);

    if (!existingType) {
      throw new BadRequestException('Type om te verwijderen niet gevonden.');
    }

    existingType.VERWIJDERD = true;
    return this.repository.save(existingType);
  }

  /**
   * A find object filters the dataset on database level, but does not filter on fields that are calculated in the Entity class.
   * This method applies the selection filter on a list of objects that are mostly already filtered by TypeORM, but where calculated fields are still remaining.
   * @param dataset
   * @param selection
   * @private
   */
  private applySelectionFilterToCalculatedColumns(dataset: Entity[], selection: FindOptionsSelect<Entity> | FindOptionsSelectByString<Entity>) {
    if (!selection) return dataset;
    if (Array.isArray(selection)) {
      this.logger.warn('applySelectionFilterToCalculatedColumns is not implemented for FindOptionsSelectByString filter. Returning the dataset unfiltered.');
      return dataset;
    }
    // For each object in the dataset
    return dataset.map((dbObject) => {
      // Create a new object to hold the properties included in the selection object
      const filteredObj: Partial<Entity> = {};

      Object.keys(dbObject).forEach((key) => {
        // If the property is in the selection object, add it to the filtered object
        if (selection[key as keyof Entity]) {
          filteredObj[key as keyof Entity] = dbObject[key as keyof Entity];
        }
      });

      // Return the filtered object
      return filteredObj as Entity;
    });
  }
}
