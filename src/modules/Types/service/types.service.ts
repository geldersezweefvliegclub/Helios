import { Injectable } from '@nestjs/common';
import { FindManyOptions, FindOptionsOrder, Repository } from 'typeorm';
import { TypeEntity } from '../entities/Type.entity';
import { InjectRepository } from '@nestjs/typeorm';
import { FindOptionsWhere } from 'typeorm/find-options/FindOptionsWhere';
import { TypesGetObjectsFilterDTO } from '../DTO/TypesGetObjectsFilterDTO';
import { IHeliosService } from '../../../core/base/IHelios.service';

@Injectable()
export class TypesService extends IHeliosService<TypeEntity, TypesGetObjectsFilterDTO> {
  constructor(@InjectRepository(TypeEntity) protected readonly repository: Repository<TypeEntity>) {
    super(repository);
  }

  protected buildFindOptions(filter: TypesGetObjectsFilterDTO): FindManyOptions<TypeEntity> {
    const findOptions: FindManyOptions<TypeEntity> = {};
    const where: FindOptionsWhere<TypeEntity> = {};
    let order: FindOptionsOrder<TypeEntity> = {
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

    if (filter.GROEP) {
      where.GROEP = filter.GROEP;
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
