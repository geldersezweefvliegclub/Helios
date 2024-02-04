import { Injectable } from '@nestjs/common';
import { FindManyOptions, FindOptionsOrder, Repository } from 'typeorm';
import { TypeGroepEntity } from '../entities/TypeGroep.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { createHash } from 'crypto';
import { GetObjectsResponse } from 'src/helpers/GetObjectsResponse';
import { GetObjectsFilterCriteria } from '../../../helpers/FilterCriteria';
import { FindOptionsWhere } from 'typeorm/find-options/FindOptionsWhere';

@Injectable()
export class TypesGroepenService {
  constructor(@InjectRepository(TypeGroepEntity) private readonly typesRepository: Repository<TypeGroepEntity>) {
  }


  async getObject(id: number) {
    return this.typesRepository.findOne({ where: { ID: id } });
  }

  async getObjects(filter: GetObjectsFilterCriteria<TypeGroepEntity>): Promise<GetObjectsResponse<TypeGroepEntity>> {
    const findOptions = this.buildFindOptions(filter);
    const dataset = await this.typesRepository.find(findOptions);
    const hash = createHash('md5').update(JSON.stringify(dataset)).digest('hex');

    return {
      totaal: dataset.length,
      laatste_aanpassing: new Date(),
      dataset: dataset,
      hash: hash,
    };
  }

  private buildFindOptions(filter: GetObjectsFilterCriteria<TypeGroepEntity>): FindManyOptions<TypeGroepEntity> {
    const findOptions: FindManyOptions<TypeGroepEntity> = {};
    const where: FindOptionsWhere<TypeGroepEntity> = {};
    let order: FindOptionsOrder<TypeGroepEntity> = {
      // todo: CLUBKIST bestaat niet in de entity, maar staat zo gedocumenteerd in de oude swagger file.
      // CLUBKIST: 'DESC',
      // VOLGORDE: 'ASC',
      // REGISTRATIE: 'ASC',
    };


    if (filter.ID) {
      where.ID = filter.ID;
    }

    if (filter.VERWIJDERD === undefined) {
      where.VERWIJDERD = false;
    } else {
      where.VERWIJDERD = filter.VERWIJDERD;
    }

    if (filter.LAATSTE_AANPASSING) {
      where.LAATSTE_AANPASSING = filter.LAATSTE_AANPASSING;
    }

    if (filter.SORT) {
      order = this.bouwSorteringOp(filter.SORT);
    }

    if (filter.MAX) {
      findOptions.take = filter.MAX;
    }

    if (filter.START) {
      findOptions.skip = filter.START;
    }

    if (filter.VELDEN) {
      const select: Record<string, boolean> = {};
      // VELDEN is een comma separated string met de velden die je wilt selecteren.
      // TypeORM wil graag een object met de velden die je wilt selecteren, waarbij de waarde true is.
      // Bijvoorbeeld: { ID: true, OMSCHRIJVING: true }

      const velden = filter.VELDEN.split(',');
      velden.forEach((veld) => {
        select[veld.trim()] = true;
      });
      findOptions.select = select;
    }

    findOptions.where = where;
    findOptions.order = order;
    return findOptions;
  }

  /**
   * Zet de sortering om naar een FindOptionsOrder object
   * Input: SORT=CLUBKIST DESC, VOLGORDE, REGISTRATIE
   * Output: { CLUBKIST: 'DESC', VOLGORDE: 'ASC', REGISTRATIE: 'ASC' }
   * @param commaSeparatedString
   * @private
   */
  private bouwSorteringOp(commaSeparatedString: string): FindOptionsOrder<TypeGroepEntity> {
    const order: Record<string, string> = {};

    const sortFields = commaSeparatedString.split(',');

    sortFields.forEach((sortField) => {
      const parts = sortField.trim().split(' ');
      const field = parts[0];
      // Pak de de waarde van de sortering, als die er niet is, dan default naar ASC
      order[field] = parts.length > 1 ? parts[1] : 'ASC';
    });

    return order;
  }
}
