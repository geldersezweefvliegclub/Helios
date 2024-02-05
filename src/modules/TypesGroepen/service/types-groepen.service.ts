import { Injectable } from '@nestjs/common';
import { FindManyOptions, FindOptionsOrder, Repository } from 'typeorm';
import { TypeGroepEntity } from '../entities/TypeGroep.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { FindOptionsWhere } from 'typeorm/find-options/FindOptionsWhere';
import { TypesGroepenGetObjectsFilterDTO } from '../DTO/TypesGroepenGetObjectsFilterDTO';
import { IHeliosService } from '../../../core/base/IHelios.service';

@Injectable()
export class TypesGroepenService extends IHeliosService<TypeGroepEntity, TypesGroepenGetObjectsFilterDTO> {
  constructor(@InjectRepository(TypeGroepEntity) private readonly typesGroepRepository: Repository<TypeGroepEntity>) {
    super(typesGroepRepository);
  }

  protected buildFindOptions(filter: TypesGroepenGetObjectsFilterDTO): FindManyOptions<TypeGroepEntity> {
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
}
