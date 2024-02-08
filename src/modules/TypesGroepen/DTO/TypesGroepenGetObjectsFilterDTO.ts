import { GetObjectsFilterDTO } from '../../../core/base/GetObjectsFilterDTO';
import { IsNumber, IsOptional, IsString } from 'class-validator';
import { TypeGroepEntity } from '../entities/TypeGroep.entity';
import { FindManyOptions } from 'typeorm';
import { isFindOptionsWhereAnObject } from '../../../core/helpers/functions';

export class TypesGroepenGetObjectsFilterDTO extends GetObjectsFilterDTO<TypeGroepEntity> {
  @IsString()
  @IsOptional()
  CODE?: string | null;

  @IsString()
  @IsOptional()
  EXT_REF?: string | null;

  @IsString()
  @IsOptional()
  OMSCHRIJVING?: string;

  @IsString()
  @IsOptional()
  SORTEER_VOLGORDE?: number | null;

  @IsString()
  @IsOptional()
  READ_ONLY?: number;

  @IsNumber()
  @IsOptional()
  BEDRAG_EENHEDEN: number;

  bouwGetObjectsFindOptions(): FindManyOptions<TypeGroepEntity> {
    const findOptions = super.bouwGetObjectsFindOptions();

    if (this.ID && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.ID = this.ID;
    }

    if (this.CODE && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.CODE = this.CODE;
    }

    if (this.EXT_REF && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.EXT_REF = this.EXT_REF;
    }

    if (this.OMSCHRIJVING && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.OMSCHRIJVING = this.OMSCHRIJVING;
    }

    if (this.SORTEER_VOLGORDE && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.SORTEER_VOLGORDE = this.SORTEER_VOLGORDE;
    }

    if (this.READ_ONLY && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.READ_ONLY = this.READ_ONLY;
    }

    if (this.BEDRAG_EENHEDEN && isFindOptionsWhereAnObject(findOptions.where)) {
      findOptions.where.BEDRAG_EENHEDEN = this.BEDRAG_EENHEDEN;
    }


    return findOptions;
  }
}
